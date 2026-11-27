<?php
	use KrameWork\Database\ConnectionString;
	use KrameWork\Database\Database;
	use KrameWork\Database\Schema\SQLiteMetaTable;
	use KrameWork\Security\Password;
	use KrameWork\Users\IUserMailer;
	use KrameWork\Users\LoginStatus;
	use KrameWork\Users\PasswordResetStatus;
	use KrameWork\Users\Schema\DatabaseType;
	use KrameWork\Users\Schema\OAuthAccountsTable;
	use KrameWork\Users\Schema\UserAccountsTable;
	use KrameWork\Users\Schema\UserPermissionsTable;
	use KrameWork\Users\Schema\UserResetTokensTable;
	use KrameWork\Users\Schema\UserSessionsTable;
	use KrameWork\Users\Schema\UserVerificationCodesTable;
	use KrameWork\Users\SendCodeStatus;
	use KrameWork\Users\UserAccountFlags;
	use KrameWork\Users\UserManager;
	use KrameWork\Users\VerificationStatus;

	require_once(__DIR__ . '/../src/Database/Database.php');
	require_once(__DIR__ . '/../src/Database/Schema/SQLiteMetaTable.php');
	require_once(__DIR__ . '/../src/Users/UserManager.php');
	require_once(__DIR__ . '/../src/Users/Schema/OAuthAccountsTable.php');
	require_once(__DIR__ . '/../src/Users/Schema/UserAccountsTable.php');
	require_once(__DIR__ . '/../src/Users/Schema/UserPermissionsTable.php');
	require_once(__DIR__ . '/../src/Users/Schema/UserResetTokensTable.php');
	require_once(__DIR__ . '/../src/Users/Schema/UserSessionsTable.php');
	require_once(__DIR__ . '/../src/Users/Schema/UserVerificationCodesTable.php');

	class UserManagerTest extends PHPUnit\Framework\TestCase
	{
		/**
		 * Test that a new account cannot log in until it is verified.
		 */
		public function testNewAccountRequiresVerification() {
			$users = $this->createManager();
			$userID = $users->register('foo@bar.net', new Password('hunter2'));

			$result = $users->verifyLogin('foo@bar.net', new Password('hunter2'));
			$this->assertSame(LoginStatus::REQUIRES_VERIFICATION, $result->status);
			$this->assertSame($userID, $result->userID);
		}

		/**
		 * Test that the mailed verification code verifies the account, so the account can log in.
		 */
		public function testVerificationCodeVerifiesAccount() {
			$users = $this->createManager();
			$userID = $users->register('foo@bar.net', new Password('hunter2'));

			$result = $users->checkVerificationCode($this->mailer->token, $this->mailer->code);
			$this->assertSame(VerificationStatus::SUCCESS, $result->status);
			$this->assertSame($userID, $result->userID);
			$this->assertSame(LoginStatus::SUCCESS, $users->verifyLogin('foo@bar.net', new Password('hunter2'))->status);
		}

		/**
		 * Test that the e-mail address is matched without case and surrounding spaces.
		 */
		public function testEmailIsNormalized() {
			$users = $this->createManager();
			$users->register(' Foo@Bar.net ', new Password('hunter2'));

			$this->assertTrue($users->emailInUse('foo@bar.net'));
			$this->assertSame(LoginStatus::REQUIRES_VERIFICATION, $users->verifyLogin('FOO@BAR.NET', new Password('hunter2'))->status);
		}

		/**
		 * Test that registering an e-mail address that an account uses returns null and sends no code.
		 */
		public function testRegisterEmailInUseReturnsNull() {
			$users = $this->createManager();
			$users->register('foo@bar.net', new Password('hunter2'));
			$token = $this->mailer->token;

			$this->assertNull($users->register('Foo@Bar.net', new Password('hunter3')));
			$this->assertSame($token, $this->mailer->token);
		}

		/**
		 * Test that an incorrect password is rejected and gives no user ID.
		 */
		public function testIncorrectPasswordIsRejected() {
			$users = $this->createManager();
			$users->register('foo@bar.net', new Password('hunter2'));

			$result = $users->verifyLogin('foo@bar.net', new Password('wrong'));
			$this->assertSame(LoginStatus::INVALID_PASSWORD, $result->status);
			$this->assertNull($result->userID);
		}

		/**
		 * Test that a login for an e-mail address with no account takes as long as a login with an incorrect password.
		 */
		public function testLoginWithNoAccountTakesPasswordCheckTime() {
			$users = $this->createManager();
			$users->register('foo@bar.net', new Password('hunter2'));

			$this->assertGreaterThan($this->timeLogin($users, 'foo@bar.net') / 2, $this->timeLogin($users, 'none@bar.net'));
		}

		/**
		 * Test that a login for an OAuth account takes as long as a login with an incorrect password.
		 */
		public function testLoginWithOAuthAccountTakesPasswordCheckTime() {
			$users = $this->createManager();
			$users->register('foo@bar.net', new Password('hunter2'));
			$this->db->execute('INSERT INTO user_accounts (email, password, flags, registered) VALUES (?, ?, ?, ?)', ['oauth@bar.net', '', UserAccountFlags::OAUTH_ACCOUNT, \time()]);

			$this->assertGreaterThan($this->timeLogin($users, 'foo@bar.net') / 2, $this->timeLogin($users, 'oauth@bar.net'));
		}

		/**
		 * Test that the verification is locked after the maximum number of incorrect codes, so the correct code no longer works.
		 */
		public function testVerificationIsLockedAfterMaxAttempts() {
			$users = $this->createManager();
			$users->register('foo@bar.net', new Password('hunter2'));
			$this->useAllAttempts($users);

			$this->assertSame(VerificationStatus::TOO_MANY_ATTEMPTS, $users->checkVerificationCode($this->mailer->token, $this->mailer->code)->status);
		}

		/**
		 * Test that a login after the maximum number of incorrect codes gives no new code, so the attempt count does not reset.
		 */
		public function testCreateVerificationAfterMaxAttemptsSendsNoCode() {
			$users = $this->createManager();
			$userID = $users->register('foo@bar.net', new Password('hunter2'));
			$this->useAllAttempts($users);
			$code = $this->mailer->code;

			$token = $users->createVerification($userID);
			$this->assertSame($code, $this->mailer->code);
			$this->assertSame(VerificationStatus::TOO_MANY_ATTEMPTS, $users->checkVerificationCode($token, $code)->status);
		}

		/**
		 * Test that createVerification() for an open verification gives a new token for the same code, and sends no code.
		 */
		public function testCreateVerificationKeepsOpenVerification() {
			$users = $this->createManager();
			$userID = $users->register('foo@bar.net', new Password('hunter2'));
			$oldToken = $this->mailer->token;

			$token = $users->createVerification($userID);
			$this->assertSame($oldToken, $this->mailer->token);
			$this->assertSame(VerificationStatus::INVALID_CODE, $users->checkVerificationCode($oldToken, $this->mailer->code)->status);
			$this->assertSame(VerificationStatus::SUCCESS, $users->checkVerificationCode($token, $this->mailer->code)->status);
		}

		/**
		 * Test that a verification code older than the verification lifetime is rejected.
		 */
		public function testExpiredVerificationCodeIsRejected() {
			$users = $this->createManager();
			$users->register('foo@bar.net', new Password('hunter2'));
			$this->expireVerifications();

			$this->assertSame(VerificationStatus::CODE_EXPIRED, $users->checkVerificationCode($this->mailer->token, $this->mailer->code)->status);
		}

		/**
		 * Test that createVerification() for an expired verification sends a new code that verifies the account.
		 */
		public function testCreateVerificationReplacesExpiredVerification() {
			$users = $this->createManager();
			$userID = $users->register('foo@bar.net', new Password('hunter2'));
			$oldToken = $this->mailer->token;
			$this->expireVerifications();

			$token = $users->createVerification($userID);
			$this->assertSame($token, $this->mailer->token);
			$this->assertNotSame($oldToken, $token);
			$this->assertSame(VerificationStatus::SUCCESS, $users->checkVerificationCode($token, $this->mailer->code)->status);
		}

		/**
		 * Test that a verification code has 8 digits.
		 */
		public function testVerificationCodeHasEightDigits() {
			$users = $this->createManager();
			$users->register('foo@bar.net', new Password('hunter2'));

			$this->assertMatchesRegularExpression('/^\d{8}$/', $this->mailer->code);
		}

		/**
		 * Test that a new token for an open verification works with the same code, and the old token does not.
		 */
		public function testGetVerificationTokenReplacesToken() {
			$users = $this->createManager();
			$userID = $users->register('foo@bar.net', new Password('hunter2'));
			$oldToken = $this->mailer->token;

			$token = $users->getVerificationToken($userID);
			$this->assertSame(VerificationStatus::INVALID_CODE, $users->checkVerificationCode($oldToken, $this->mailer->code)->status);
			$this->assertSame(VerificationStatus::SUCCESS, $users->checkVerificationCode((string) $token, $this->mailer->code)->status);
		}

		/**
		 * Test that a verification code is not sent again within the send interval.
		 */
		public function testVerificationCodeResendIsThrottled() {
			$users = $this->createManager();
			$users->register('foo@bar.net', new Password('hunter2'));

			$this->assertSame(SendCodeStatus::THROTTLED, $users->sendVerificationCode($this->mailer->token));
			$this->assertSame(SendCodeStatus::SUCCESS, $users->sendVerificationCode($this->mailer->token, true));
		}

		/**
		 * Test that a resent verification code verifies the account and the old code does not.
		 */
		public function testResentVerificationCodeReplacesCode() {
			$users = $this->createManager();
			$users->register('foo@bar.net', new Password('hunter2'));
			$oldCode = $this->mailer->code;

			$users->sendVerificationCode($this->mailer->token, true);
			$wrong = $oldCode === $this->mailer->code ? '00000' : $oldCode;
			$this->assertSame(VerificationStatus::INVALID_CODE, $users->checkVerificationCode($this->mailer->token, $wrong)->status);
			$this->assertSame(VerificationStatus::SUCCESS, $users->checkVerificationCode($this->mailer->token, $this->mailer->code)->status);
		}

		/**
		 * Test that the database does not contain the verification token or code that is sent to the user.
		 */
		public function testVerificationSecretsAreNotStored() {
			$users = $this->createManager();
			$users->register('foo@bar.net', new Password('hunter2'));

			$this->assertNull($this->db->getValue('SELECT 1 FROM user_verification_codes WHERE token = ? OR code = ?', [$this->mailer->token, $this->mailer->code]));
		}

		/**
		 * Test that the database does not contain the password reset token that is sent to the user.
		 */
		public function testPasswordResetTokenIsNotStored() {
			$users = $this->createManager();
			$this->createVerifiedAccount($users);
			$users->requestPasswordReset('foo@bar.net');

			$this->assertNull($this->db->getValue('SELECT 1 FROM user_reset_tokens WHERE reset_token = ?', [$this->mailer->resetToken]));
		}

		/**
		 * Test that a password reset sets the new password and removes the forced reset flag.
		 */
		public function testPasswordResetSetsNewPassword() {
			$users = $this->createManager();
			$userID = $this->createVerifiedAccount($users);
			$users->setFlag($userID, UserAccountFlags::FORCE_PASSWORD_RESET, true);

			$this->assertSame(PasswordResetStatus::SUCCESS, $users->requestPasswordReset('foo@bar.net'));
			$this->assertSame(PasswordResetStatus::SUCCESS, $users->applyPasswordReset($this->mailer->resetToken, new Password('newpass'))->status);

			$this->assertSame(LoginStatus::SUCCESS, $users->verifyLogin('foo@bar.net', new Password('newpass'))->status);
			$this->assertSame(LoginStatus::INVALID_PASSWORD, $users->verifyLogin('foo@bar.net', new Password('hunter2'))->status);
		}

		/**
		 * Test that a successful password reset returns the ID of the account.
		 */
		public function testPasswordResetReturnsUserID() {
			$users = $this->createManager();
			$userID = $this->createVerifiedAccount($users);
			$users->requestPasswordReset('foo@bar.net');

			$this->assertSame($userID, $users->applyPasswordReset($this->mailer->resetToken, new Password('newpass'))->userID);
		}

		/**
		 * Test that a password reset verifies an account that is not verified, so the account can log in.
		 */
		public function testPasswordResetVerifiesAccount() {
			$users = $this->createManager();
			$users->register('foo@bar.net', new Password('hunter2'));
			$users->requestPasswordReset('foo@bar.net');

			$users->applyPasswordReset($this->mailer->resetToken, new Password('newpass'));
			$this->assertSame(LoginStatus::SUCCESS, $users->verifyLogin('foo@bar.net', new Password('newpass'))->status);
		}

		/**
		 * Test that a password reset token can be used one time only.
		 */
		public function testPasswordResetTokenIsSingleUse() {
			$users = $this->createManager();
			$this->createVerifiedAccount($users);
			$users->requestPasswordReset('foo@bar.net');

			$users->applyPasswordReset($this->mailer->resetToken, new Password('newpass'));
			$this->assertSame(PasswordResetStatus::INVALID_TOKEN, $users->applyPasswordReset($this->mailer->resetToken, new Password('other'))->status);
		}

		/**
		 * Test that a second password reset request within the send interval is throttled.
		 */
		public function testPasswordResetRequestIsThrottled() {
			$users = $this->createManager();
			$this->createVerifiedAccount($users);

			$this->assertSame(PasswordResetStatus::SUCCESS, $users->requestPasswordReset('foo@bar.net'));
			$this->assertSame(PasswordResetStatus::THROTTLED, $users->requestPasswordReset('foo@bar.net'));
		}

		/**
		 * Test that a permission is given only to the account it is set on, and that ALL_PERMISSIONS gives every permission.
		 */
		public function testPermissions() {
			$users = $this->createManager();
			$userID = $users->register('foo@bar.net', new Password('hunter2'));
			$adminID = $users->register('admin@bar.net', new Password('hunter2'));

			$users->setPermission($userID, 1, true);
			$users->setFlag($adminID, UserAccountFlags::ALL_PERMISSIONS, true);

			$this->assertTrue($users->hasPermission($userID, 1));
			$this->assertFalse($users->hasPermission($userID, 2));
			$this->assertTrue($users->hasPermission($adminID, 2));

			$users->setPermission($userID, 1, false);
			$this->assertFalse($users->hasPermission($userID, 1));
		}

		/**
		 * Test that a disabled account has no permissions, also with ALL_PERMISSIONS.
		 */
		public function testDisabledAccountHasNoPermissions() {
			$users = $this->createManager();
			$userID = $users->register('foo@bar.net', new Password('hunter2'));

			$users->setPermission($userID, 1, true);
			$users->setFlag($userID, UserAccountFlags::ALL_PERMISSIONS, true);
			$users->setFlag($userID, UserAccountFlags::ACCOUNT_DISABLED, true);

			$this->assertFalse($users->hasPermission($userID, 1));
		}

		/**
		 * Test that deleting an account removes its rows from all user tables, and keeps the rows of other accounts.
		 */
		public function testDeleteAccountRemovesUserData() {
			$users = $this->createManager();
			$userID = $users->register('foo@bar.net', new Password('hunter2'));
			$otherID = $users->register('other@bar.net', new Password('hunter2'));

			foreach ([$userID, $otherID] as $id) {
				$users->setPermission($id, 1, true);
				$users->requestPasswordResetByID($id);
				$this->db->execute('INSERT INTO user_sessions (session_id, user_id, created, updated) VALUES (?, ?, ?, ?)', ['session' . $id, $id, \time(), \time()]);
				$this->db->execute('INSERT INTO oauth_accounts (provider, provider_user_id, user_id, linked) VALUES (?, ?, ?, ?)', ['google', 'subject' . $id, $id, \time()]);
			}

			$this->assertTrue($users->deleteAccount($userID));

			$tables = ['user_accounts' => 'id', 'user_sessions' => 'user_id', 'user_permissions' => 'user_id', 'user_verification_codes' => 'user_id', 'user_reset_tokens' => 'user_id', 'oauth_accounts' => 'user_id'];
			foreach ($tables as $table => $column) {
				$this->assertNull($this->db->getValue('SELECT 1 FROM ' . $table . ' WHERE ' . $column . ' = ?', [$userID]), $table);
				$this->assertNotNull($this->db->getValue('SELECT 1 FROM ' . $table . ' WHERE ' . $column . ' = ?', [$otherID]), $table);
			}
		}

		/**
		 * Give incorrect codes for the mailed verification token until the verification is locked.
		 * @param UserManager $users User manager.
		 */
		private function useAllAttempts(UserManager $users): void {
			for ($i = 1; $i < UserManager::VERIFY_MAX_ATTEMPTS; $i++)
				$this->assertSame(VerificationStatus::INVALID_CODE, $users->checkVerificationCode($this->mailer->token, '10000')->status);

			$this->assertSame(VerificationStatus::TOO_MANY_ATTEMPTS, $users->checkVerificationCode($this->mailer->token, '10000')->status);
		}

		/**
		 * Move the creation time of all verifications back by the verification lifetime.
		 */
		private function expireVerifications(): void {
			$this->db->execute('UPDATE user_verification_codes SET created = created - ?', [UserManager::VERIFY_LIFETIME]);
		}

		/**
		 * Get the time of a login attempt with an incorrect password.
		 * @param UserManager $users User manager.
		 * @param string $email E-mail address of the login attempt.
		 * @return int Time in nanoseconds.
		 */
		private function timeLogin(UserManager $users, string $email): int {
			$start = \hrtime(true);
			$users->verifyLogin($email, new Password('wrong'));
			return \hrtime(true) - $start;
		}

		/**
		 * Register an account with the address foo@bar.net and verify it.
		 * @param UserManager $users User manager.
		 * @return int ID of the account.
		 */
		private function createVerifiedAccount(UserManager $users): int {
			$userID = $users->register('foo@bar.net', new Password('hunter2'));
			$users->checkVerificationCode($this->mailer->token, $this->mailer->code);
			return $userID;
		}

		/**
		 * Create a user manager on a new in-memory database.
		 * @return UserManager
		 */
		private function createManager(): UserManager {
			$db = $this->db = new Database(new ConnectionString('sqlite::memory:'), Database::DB_DRIVER_PDO);
			$meta = new SQLiteMetaTable($db);
			(new UserAccountsTable($db, $meta, DatabaseType::SQLITE))->create();
			(new UserVerificationCodesTable($db, $meta))->create();
			(new UserResetTokensTable($db, $meta))->create();
			(new UserPermissionsTable($db, $meta))->create();
			(new UserSessionsTable($db, $meta))->create();
			(new OAuthAccountsTable($db, $meta))->create();

			$this->mailer = new class implements IUserMailer {
				public string $token = '';
				public string $code = '';
				public string $resetToken = '';

				public function sendVerificationCode(int $userID, string $email, string $token, string $code): void {
					$this->token = $token;
					$this->code = $code;
				}

				public function sendPasswordReset(int $userID, string $email, string $token): void {
					$this->resetToken = $token;
				}
			};

			return new UserManager($db, $this->mailer);
		}

		private IUserMailer $mailer;
		private Database $db;
	}
