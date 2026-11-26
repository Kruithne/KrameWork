## KrameWork\Users\UserManager

***Table of Contents***
* **Overview** - Information about the class.
* **Examples** - Usage examples.
* **Functions** - Comprehensive list of all functions in the class.

___
### Overview
`UserManager` registers and deletes user accounts, checks logins, verifies accounts, resets passwords and checks permissions.

`UserManager` uses a `Database` connection that contains the user tables. See [UserTables](UserTables.md). The SQL works on MySQL, PostgreSQL, SQLite and SQL Server.

`UserManager` does not send mail itself. It gives verification codes and password reset tokens to an [IUserMailer](IUserMailer.md) that the application supplies.

`UserManager` does not start sessions and does not limit the rate of requests. Use [UserSessionManager](UserSessionManager.md) to start a session after a successful login. Limit the rate of login requests in the application.

User IDs are auto-increment integers. E-mail addresses are trimmed and changed to lower case.

The application owns the HTTP endpoints. It must check the input (for example the length of the e-mail address and password) before it calls `UserManager`.
___
### Examples
Register an account:
```php
$users = new UserManager($db, new MyMailer());

$userID = $users->register($email, new Password($password));
if ($userID === null)
	return ['error' => 'E-mail address in use'];

$token = $users->getVerificationToken($userID);
```

Log in:
```php
$result = $users->verifyLogin($email, new Password($password));

if ($result->status === LoginStatus::SUCCESS)
	$sessions->start($result->userID);

if ($result->status === LoginStatus::REQUIRES_VERIFICATION)
	$token = $users->getVerificationToken($result->userID) ?? $users->createVerification($result->userID);
```

Verify an account and log in:
```php
$result = $users->checkVerificationCode($token, $code);

if ($result->status === VerificationStatus::SUCCESS)
	$sessions->start($result->userID);
```

Reset a password:
```php
$users->requestPasswordReset($email);

// later, with the token from the mail
$status = $users->applyPasswordReset($token, new Password($newPassword));
```

Check a permission:
```php
const PERMISSION_EDIT_POSTS = 1;

$users->setPermission($userID, PERMISSION_EDIT_POSTS, true);
$users->hasPermission($userID, PERMISSION_EDIT_POSTS); // true
```

Delete an account:
```php
deleteProfile($userID);
$users->deleteAccount($userID);
```

### Application data
The user tables hold only the columns that the user system uses. Keep application data, such as names, in an application table with a `user_id` column. Make a `ManagedTable` for that table.

`deleteAccount()` deletes the rows of the account in all user tables, including `user_sessions` and `oauth_accounts`. Create all the user tables to use it. It does not delete application data. Delete that data first.

### Verification
A new account has the `REQUIRES_VERIFICATION` flag. `register()` sends a five-digit code and a verification token to the mailer. A correct code removes the flag.

After `VERIFY_MAX_ATTEMPTS` (5) incorrect codes, the token is deleted. Call `createVerification()` to send a new code. Only call it for a user that gave the correct password, for example after `verifyLogin()` returns `REQUIRES_VERIFICATION`.

### Login status
status | description
--- | ---
`SUCCESS` | The password is correct. `userID` is set.
`NO_ACCOUNT` | No account has the e-mail address.
`INVALID_PASSWORD` | The password is incorrect, or the account is an OAuth account.
`ACCOUNT_DISABLED` | The account has the `ACCOUNT_DISABLED` flag.
`REQUIRES_PASSWORD_RESET` | The password is correct, but the account has the `FORCE_PASSWORD_RESET` flag. `userID` is set.
`REQUIRES_VERIFICATION` | The password is correct, but the account is not verified. `userID` is set.

### Password reset status
status | description
--- | ---
`SUCCESS` | The token was sent, or the password was changed.
`NO_ACCOUNT` | No account has the e-mail address or ID.
`THROTTLED` | A token was sent less than `RESET_SEND_INTERVAL` (300) seconds ago.
`OAUTH_ACCOUNT` | The account is an OAuth account and has no password. Do not show this to users that are not logged in.
`INVALID_TOKEN` | The token does not exist or was used.
`TOKEN_EXPIRED` | The token is older than `RESET_LIFETIME` (86400) seconds.

> **Note**: A password reset does not end the sessions of the account. To end them, call `UserSessionManager::endAll()`.
___
### Functions
##### > __construct() : `void`
UserManager constructor.

parameter | type | description
--- | --- | ---
`$db` | `Generic` | Database that contains the user tables.
`$mailer` | `IUserMailer` | Sends verification codes and password reset tokens.
##### > register() : `int|null`
Register a user account that requires verification, and send the verification code. Returns the ID of the new account, or `null` if an account uses the e-mail address.

parameter | type | description
--- | --- | ---
`$email` | `string` | E-mail address of the account.
`$password` | `Password` | Password of the account.
##### > createVerification() : `string`
Create a new verification token and code for an account, and send the code. This replaces the open verification of the account. Returns the verification token.

parameter | type | description
--- | --- | ---
`$userID` | `int` | ID of the user account.

exception | reason
--- | ---
`UnknownUserException` | No account has the ID.
##### > emailInUse() : `bool`
Check if an account uses an e-mail address.

parameter | type | description
--- | --- | ---
`$email` | `string` | E-mail address to check.
##### > verifyLogin() : `LoginResult`
Check the e-mail address and password of a login attempt. This does not start a session. The result has a `status` (`LoginStatus`) and a `userID`.

parameter | type | description
--- | --- | ---
`$email` | `string` | E-mail address of the account.
`$password` | `Password` | Password given for the account.
##### > getVerificationToken() : `string|null`
Get a new verification token for the open verification of an account. The old token stops working. The code and the attempt count do not change. Returns null if the account has no open verification.

parameter | type | description
--- | --- | ---
`$userID` | `int` | ID of the user account.
##### > checkVerificationCode() : `VerificationResult`
Check a verification code. The result has a `status` (`VerificationStatus`: `SUCCESS`, `INVALID_CODE` or `TOO_MANY_ATTEMPTS`) and a `userID`.

parameter | type | description
--- | --- | ---
`$token` | `string` | Verification token.
`$code` | `string` | Verification code given by the user.
##### > sendVerificationCode() : `SendCodeStatus`
Send a new verification code. The old code stops working. The code is sent at most one time in `VERIFY_SEND_INTERVAL` (300) seconds, unless forced. Returns `SUCCESS`, `INVALID_TOKEN` or `THROTTLED`.

parameter | type | description
--- | --- | ---
`$token` | `string` | Verification token.
`$force` | `bool` | Send the code even if it was sent recently. **Default: false**.
##### > requestPasswordReset() : `PasswordResetStatus`
Send a password reset token to the account with an e-mail address.

parameter | type | description
--- | --- | ---
`$email` | `string` | E-mail address of the account.
##### > requestPasswordResetByID() : `PasswordResetStatus`
Send a password reset token to the account with an ID.

parameter | type | description
--- | --- | ---
`$userID` | `int` | ID of the user account.
##### > applyPasswordReset() : `PasswordResetStatus`
Set a new password with a password reset token. This removes the `FORCE_PASSWORD_RESET` flag. A token can be used one time.

parameter | type | description
--- | --- | ---
`$token` | `string` | Password reset token.
`$password` | `Password` | New password.
##### > setFlag() : `void`
Set or clear a flag on an account. See [UserAccountFlags](UserAccountFlags.md).

parameter | type | description
--- | --- | ---
`$userID` | `int` | ID of the user account.
`$flag` | `int` | One of the `UserAccountFlags` constants.
`$state` | `bool` | True to set the flag, false to clear it.
##### > hasPermission() : `bool`
Check if an account has a permission. The `ALL_PERMISSIONS` flag gives all permissions. An account with the `ACCOUNT_DISABLED` flag has no permissions.

parameter | type | description
--- | --- | ---
`$userID` | `int` | ID of the user account.
`$permission` | `int` | Permission defined by the application.
##### > setPermission() : `void`
Give a permission to an account, or take it away.

parameter | type | description
--- | --- | ---
`$userID` | `int` | ID of the user account.
`$permission` | `int` | Permission defined by the application.
`$state` | `bool` | True to give the permission, false to take it away.
##### > deleteAccount() : `bool`
Delete an account with its sessions, permissions, OAuth identities, verification and password reset. Returns false if no account has the ID.

parameter | type | description
--- | --- | ---
`$userID` | `int` | ID of the user account.
