## Getting Started with the User System

***Table of Contents***
* **Overview** - Information about the user system.
* **Setup** - Connection, managers and mailer.
* **Tables** - Create and update the tables.
* **Endpoints** - Examples for each part of the user system.
* **Application tasks** - Tasks that the application must do.

___
### Overview
The user system gives accounts, logins, sessions, account verification, password reset, permissions and OAuth logins. It has these parts:

class | purpose
--- | ---
[UserManager](UserManager.md) | Register accounts, check logins, verify accounts, reset passwords, set flags and permissions.
[UserSessionManager](UserSessionManager.md) | Start, find and end sessions. A cookie identifies the session.
[OAuthManager](OAuthManager.md) | Log in with Google, Microsoft or another OpenID Connect provider.
[IUserMailer](IUserMailer.md) | The application implements this to send verification codes and password reset tokens.
[UserTables](UserTables.md) | The managed tables of the user system.

KrameWork has no router. The examples use one PHP file for each endpoint. Each endpoint includes `bootstrap.php`.

The user tables hold only the columns that the user system uses. Keep application data, such as names, in an application table with a `user_id` column. The examples use a `profiles` table.

___
### Setup
`bootstrap.php` creates the connection and the managers:
```php
<?php
	use KrameWork\Caching\APCu;
	use KrameWork\Database\ConnectionString;
	use KrameWork\Database\Database;
	use KrameWork\Mailing\SMTPTransport;
	use KrameWork\Users\OAuth\OAuthManager;
	use KrameWork\Users\OAuth\OAuthProvider;
	use KrameWork\Users\UserManager;
	use KrameWork\Users\UserSessionManager;

	require_once(__DIR__ . '/vendor/autoload.php');
	require_once(__DIR__ . '/MyMailer.php');

	const PERMISSION_EDIT_POSTS = 1;

	$db = new Database(new ConnectionString('mysql:host=localhost;dbname=app', 'user', 'password'));

	$users = new UserManager($db, new MyMailer(new SMTPTransport('smtp.bar.net', 465, 'user@bar.net', 'password')));
	$sessions = new UserSessionManager($db);
	$oauth = new OAuthManager($db, 'https://bar.net/auth/callback', [
		OAuthProvider::google('google-client-id', 'google-secret'),
		OAuthProvider::microsoft('microsoft-client-id', 'microsoft-secret')
	], new APCu());

	function respond(array $data): never {
		\header('Content-Type: application/json');
		echo \json_encode($data);
		exit;
	}
```

`MyMailer.php` sends the account mails. The application writes the content:
```php
<?php
	use KrameWork\Mailing\IMailTransport;
	use KrameWork\Mailing\Mail;
	use KrameWork\Users\IUserMailer;

	class MyMailer implements IUserMailer
	{
		public function __construct(private IMailTransport $transport) {}

		public function sendVerificationCode(int $userID, string $email, string $token, string $code): void {
			$this->send($email, 'Your verification code', 'Your code is ' . $code . '. Enter it at https://bar.net/verify?token=' . $token);
		}

		public function sendPasswordReset(int $userID, string $email, string $token): void {
			$this->send($email, 'Reset your password', 'Reset your password at https://bar.net/reset?token=' . $token);
		}

		private function send(string $email, string $subject, string $body): void {
			$mail = new Mail();
			$mail->setSender('no-reply@bar.net');
			$mail->to->add($email);
			$mail->setSubject($subject);
			$mail->plainContent->setContent($body);
			$mail->send($this->transport);
		}
	}
```

___
### Tables
`migrate.php` creates and updates the tables. Run it one time for each deploy. `ProfilesTable` is an application table.
```php
<?php
	use KrameWork\Database\Schema\DatabaseManager;
	use KrameWork\Database\Schema\MySQLManagedTable;
	use KrameWork\Database\Schema\MySQLMetaTable;
	use KrameWork\Database\Schema\TableVersion;
	use KrameWork\Users\Schema\DatabaseType;
	use KrameWork\Users\Schema\OAuthAccountsTable;
	use KrameWork\Users\Schema\OAuthStatesTable;
	use KrameWork\Users\Schema\UserAccountsTable;
	use KrameWork\Users\Schema\UserPermissionsTable;
	use KrameWork\Users\Schema\UserResetTokensTable;
	use KrameWork\Users\Schema\UserSessionsTable;
	use KrameWork\Users\Schema\UserVerificationCodesTable;

	require_once(__DIR__ . '/bootstrap.php');

	class ProfilesTable extends MySQLManagedTable
	{
		public function getName(): string {
			return 'profiles';
		}

		public function versionLog(): array {
			return [new TableVersion(1, ['CREATE TABLE profiles (user_id BIGINT NOT NULL PRIMARY KEY, first_name VARCHAR(50) NOT NULL, last_name VARCHAR(50) NOT NULL)'])];
		}
	}

	$meta = new MySQLMetaTable($db);
	$manager = new DatabaseManager();
	$manager->userAccounts = new UserAccountsTable($db, $meta, DatabaseType::MYSQL);
	$manager->userSessions = new UserSessionsTable($db, $meta);
	$manager->userVerificationCodes = new UserVerificationCodesTable($db, $meta);
	$manager->userResetTokens = new UserResetTokensTable($db, $meta);
	$manager->userPermissions = new UserPermissionsTable($db, $meta);
	$manager->oauthAccounts = new OAuthAccountsTable($db, $meta);
	$manager->oauthStates = new OAuthStatesTable($db, $meta);
	$manager->profiles = new ProfilesTable($db, $meta);

	print_r($manager->updateSchema());
```

___
### Endpoints
#### Register
`register()` creates an account that requires verification, and sends a code. Write the application data after `register()`.
```php
<?php
	use KrameWork\HTTP\HTTPContext;
	use KrameWork\Security\Password;

	require_once(__DIR__ . '/bootstrap.php');

	[$email, $password, $firstName, $lastName] = HTTPContext::getFormDataValues('email', 'password', 'first_name', 'last_name');
	if (!\is_string($email) || !\filter_var($email, \FILTER_VALIDATE_EMAIL) || !\is_string($password) || \strlen($password) < 12)
		respond(['error' => 'Invalid e-mail address or password']);

	$userID = $users->register($email, new Password($password));
	if ($userID === null)
		respond(['error' => 'E-mail address in use']);

	$db->execute('INSERT INTO profiles (user_id, first_name, last_name) VALUES (?, ?, ?)', [$userID, (string) $firstName, (string) $lastName]);

	respond(['verify' => $users->getVerificationToken($userID)]);
```

#### Verify
A correct code verifies the account and logs the user in.
```php
<?php
	use KrameWork\HTTP\HTTPContext;
	use KrameWork\Users\VerificationStatus;

	require_once(__DIR__ . '/bootstrap.php');

	[$token, $code] = HTTPContext::getFormDataValues('token', 'code');
	$result = $users->checkVerificationCode((string) $token, (string) $code);

	if ($result->status === VerificationStatus::TOO_MANY_ATTEMPTS)
		respond(['error' => 'Too many attempts. Log in to get a new code.']);

	if ($result->status !== VerificationStatus::SUCCESS)
		respond(['error' => 'Invalid code']);

	$sessions->start($result->userID);
	respond(['success' => true]);
```

#### Log in
`verifyLogin()` does not start a session. Start the session for `SUCCESS` only. An account that is not verified gets a new code if it has no open verification.
```php
<?php
	use KrameWork\HTTP\HTTPContext;
	use KrameWork\Security\Password;
	use KrameWork\Users\LoginStatus;

	require_once(__DIR__ . '/bootstrap.php');

	[$email, $password] = HTTPContext::getFormDataValues('email', 'password');
	$result = $users->verifyLogin((string) $email, new Password((string) $password));

	switch ($result->status) {
		case LoginStatus::SUCCESS:
			$sessions->start($result->userID);
			respond(['success' => true]);

		case LoginStatus::REQUIRES_VERIFICATION:
			$token = $users->getVerificationToken($result->userID) ?? $users->createVerification($result->userID);
			respond(['verify' => $token]);

		case LoginStatus::REQUIRES_PASSWORD_RESET:
			$users->requestPasswordResetByID($result->userID);
			respond(['error' => 'Reset your password. We sent you a link.']);

		case LoginStatus::ACCOUNT_DISABLED:
			respond(['error' => 'This account is disabled']);

		default:
			respond(['error' => 'Incorrect e-mail address or password']);
	}
```

#### Log out
```php
<?php
	require_once(__DIR__ . '/bootstrap.php');

	$sessions->endCurrent();
	respond(['success' => true]);
```

#### Reset a password
The request endpoint gives the same response when no account exists. This does not show which e-mail addresses have accounts.
```php
<?php
	// reset-request.php
	use KrameWork\HTTP\HTTPContext;
	use KrameWork\Users\PasswordResetStatus;

	require_once(__DIR__ . '/bootstrap.php');

	$status = $users->requestPasswordReset((string) HTTPContext::getFormDataValue('email'));
	if ($status === PasswordResetStatus::THROTTLED)
		respond(['error' => 'Try again in 5 minutes']);

	respond(['success' => true]);
```

A password reset does not end the sessions of the account. The apply endpoint ends them with `endAll()`.
```php
<?php
	// reset-apply.php
	use KrameWork\HTTP\HTTPContext;
	use KrameWork\Security\Password;
	use KrameWork\Users\PasswordResetStatus;

	require_once(__DIR__ . '/bootstrap.php');

	[$token, $password] = HTTPContext::getFormDataValues('token', 'password');
	if (!\is_string($password) || \strlen($password) < 12)
		respond(['error' => 'Password must be at least 12 characters']);

	$userID = $db->getValue('SELECT user_id FROM user_reset_tokens WHERE reset_token = ?', [(string) $token]);
	if ($users->applyPasswordReset((string) $token, new Password($password)) !== PasswordResetStatus::SUCCESS)
		respond(['error' => 'Invalid or expired link']);

	$sessions->endAll((int) $userID);
	respond(['success' => true]);
```

#### Log in with OAuth
Send the user to the authorization URL of the provider:
```php
<?php
	// oauth-start.php?provider=google
	use KrameWork\HTTP\HTTPContext;

	require_once(__DIR__ . '/bootstrap.php');

	$url = $oauth->getAuthorizationURL((string) HTTPContext::getQueryDataValue('provider'));
	\header('Location: ' . ($url ?? '/login?error=unknown_provider'));
```

The provider sends the user back to the callback URL. For a new account, `registered` is true. The example fills the profile from the claims of the ID token.
```php
<?php
	// auth/callback.php
	use KrameWork\HTTP\HTTPContext;
	use KrameWork\Users\OAuth\OAuthStatus;

	require_once(__DIR__ . '/../bootstrap.php');

	[$state, $code] = HTTPContext::getQueryDataValues('state', 'code');
	if (!\is_string($state) || !\is_string($code)) {
		\header('Location: /login?error=oauth');
		exit;
	}

	$result = $oauth->callback($state, $code);
	if ($result->status !== OAuthStatus::SUCCESS) {
		\header('Location: /login?error=' . \strtolower($result->status->name));
		exit;
	}

	if ($result->registered)
		$db->execute('INSERT INTO profiles (user_id, first_name, last_name) VALUES (?, ?, ?)', [
			$result->userID,
			(string) ($result->claims->given_name ?? ''),
			(string) ($result->claims->family_name ?? '')
		]);

	$sessions->start($result->userID);
	\header('Location: /');
```

#### Require a session
`UserSessionManager` does not check the flags. Check `ACCOUNT_DISABLED` in the application.
```php
<?php
	// me.php
	use KrameWork\Users\UserAccountFlags;

	require_once(__DIR__ . '/bootstrap.php');

	$session = $sessions->getCurrent();
	if ($session === null || $session->hasFlag(UserAccountFlags::ACCOUNT_DISABLED)) {
		\http_response_code(401);
		respond(['error' => 'Not logged in']);
	}

	respond(['profile' => $db->getRow('SELECT first_name, last_name FROM profiles WHERE user_id = ?', [$session->userID])]);
```

#### Require a permission
The application defines the permission values.
```php
<?php
	// edit-post.php
	require_once(__DIR__ . '/bootstrap.php');

	$session = $sessions->getCurrent();
	if ($session === null || !$users->hasPermission($session->userID, PERMISSION_EDIT_POSTS)) {
		\http_response_code(403);
		respond(['error' => 'Not allowed']);
	}
```

#### Manage accounts
Use these calls in an admin tool:
```php
$users->setPermission($userID, PERMISSION_EDIT_POSTS, true);
$users->setFlag($adminID, UserAccountFlags::ALL_PERMISSIONS, true);

$users->setFlag($userID, UserAccountFlags::ACCOUNT_DISABLED, true);
$sessions->endAll($userID);
```

___
### Application tasks
The user system does not do these tasks. The application must do them:
* Limit the rate of requests to the login, register and verify endpoints. `UserManager` limits only the mails it sends.
* Check the input before it calls the managers, for example the format of the e-mail address and the length of the password.
* Call `UserSessionManager::start()` and `endCurrent()` before output starts, because they set a cookie.
