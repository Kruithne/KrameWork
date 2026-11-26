<?php
	namespace KrameWork\Users;

	use KrameWork\Database\Driver\Generic;
	use KrameWork\Security\Password;

	require_once(__DIR__ . '/../Database/Driver/Generic.php');
	require_once(__DIR__ . '/../Security/Password.php');
	require_once(__DIR__ . '/IUserMailer.php');
	require_once(__DIR__ . '/UserAccountFlags.php');
	require_once(__DIR__ . '/LoginResult.php');
	require_once(__DIR__ . '/VerificationResult.php');
	require_once(__DIR__ . '/SendCodeStatus.php');
	require_once(__DIR__ . '/PasswordResetStatus.php');
	require_once(__DIR__ . '/UnknownUserException.php');

	/**
	 * Class UserManager
	 * Registers and deletes user accounts, checks logins, verifies accounts, resets passwords and checks permissions.
	 *
	 * @package KrameWork\Users
	 */
	class UserManager
	{
		const VERIFY_MAX_ATTEMPTS = 5;
		const VERIFY_SEND_INTERVAL = 300;
		const RESET_SEND_INTERVAL = 300;
		const RESET_LIFETIME = 86400;
		const USER_TABLES = ['user_sessions', 'user_permissions', 'user_verification_codes', 'user_reset_tokens', 'oauth_accounts'];

		/**
		 * UserManager constructor.
		 *
		 * @api __construct
		 * @param Generic $db Database that contains the user tables.
		 * @param IUserMailer $mailer Sends verification codes and password reset tokens.
		 */
		public function __construct(private Generic $db, private IUserMailer $mailer) {}

		/**
		 * Register a user account that requires verification, and send the verification code.
		 * The e-mail address is trimmed and changed to lower case.
		 *
		 * @api register
		 * @param string $email E-mail address of the account.
		 * @param Password $password Password of the account.
		 * @return int|null ID of the new user account. Null if a user account uses the e-mail address.
		 * @throws \Throwable
		 */
		public function register(string $email, Password $password): ?int {
			try {
				$this->db->execute(
					'INSERT INTO user_accounts (email, password, flags, registered) VALUES (?, ?, ?, ?)',
					[self::normalizeEmail($email), $password->asHash(), UserAccountFlags::REQUIRES_VERIFICATION, \time()]
				);
			} catch (\PDOException $e) {
				if (\str_starts_with((string) $e->getCode(), '23'))
					return null;

				throw $e;
			}

			$userID = (int) $this->db->getLastInsertID();
			$this->createVerification($userID);
			return $userID;
		}

		/**
		 * Create a new verification token and code for a user account, and send the code.
		 * This replaces the open verification of the account, if there is one.
		 *
		 * @api createVerification
		 * @param int $userID ID of the user account.
		 * @return string Verification token.
		 * @throws \Throwable
		 */
		public function createVerification(int $userID): string {
			$email = $this->db->getValue('SELECT email FROM user_accounts WHERE id = ?', [$userID]);
			if (!\is_string($email))
				throw new UnknownUserException('No user account with ID ' . $userID);

			$token = self::generateToken();
			$code = self::generateCode();

			$this->db->beginTransaction();
			try {
				$this->db->execute('DELETE FROM user_verification_codes WHERE user_id = ?', [$userID]);
				$this->db->execute(
					'INSERT INTO user_verification_codes (token, user_id, code, attempts, last_sent) VALUES (?, ?, ?, ?, ?)',
					[self::hashSecret($token), $userID, self::hashSecret($code), 0, \time()]
				);
				$this->db->commitTransaction();
			} catch (\Throwable $e) {
				$this->db->rollbackTransaction();
				throw $e;
			}

			$this->mailer->sendVerificationCode($userID, $email, $token, $code);
			return $token;
		}

		/**
		 * Check if a user account uses an e-mail address.
		 *
		 * @api emailInUse
		 * @param string $email E-mail address to check.
		 * @return bool
		 */
		public function emailInUse(string $email): bool {
			return $this->db->getValue('SELECT 1 FROM user_accounts WHERE email = ?', [self::normalizeEmail($email)]) !== null;
		}

		/**
		 * Check the e-mail address and password of a login attempt. This does not start a session.
		 *
		 * @api verifyLogin
		 * @param string $email E-mail address of the account.
		 * @param Password $password Password given for the account.
		 * @return LoginResult
		 */
		public function verifyLogin(string $email, Password $password): LoginResult {
			$row = $this->db->getRow('SELECT id, password, flags FROM user_accounts WHERE email = ?', [self::normalizeEmail($email)]);
			if ($row === null)
				return new LoginResult(LoginStatus::NO_ACCOUNT);

			$flags = self::toInt($row->flags);
			if (($flags & UserAccountFlags::OAUTH_ACCOUNT) !== 0 || !$password->verify(self::toString($row->password)))
				return new LoginResult(LoginStatus::INVALID_PASSWORD);

			if (($flags & UserAccountFlags::ACCOUNT_DISABLED) !== 0)
				return new LoginResult(LoginStatus::ACCOUNT_DISABLED);

			$userID = self::toInt($row->id);
			if (($flags & UserAccountFlags::FORCE_PASSWORD_RESET) !== 0)
				return new LoginResult(LoginStatus::REQUIRES_PASSWORD_RESET, $userID);

			if (($flags & UserAccountFlags::REQUIRES_VERIFICATION) !== 0)
				return new LoginResult(LoginStatus::REQUIRES_VERIFICATION, $userID);

			return new LoginResult(LoginStatus::SUCCESS, $userID);
		}

		/**
		 * Get a new verification token for the open verification of a user account.
		 * The old token stops working. The code and the attempt count do not change.
		 *
		 * @api getVerificationToken
		 * @param int $userID ID of the user account.
		 * @return string|null Null if the account has no open verification.
		 */
		public function getVerificationToken(int $userID): ?string {
			$token = self::generateToken();
			if ($this->db->execute('UPDATE user_verification_codes SET token = ? WHERE user_id = ?', [self::hashSecret($token), $userID]) === 0)
				return null;

			return $token;
		}

		/**
		 * Check a verification code. A correct code removes the REQUIRES_VERIFICATION flag.
		 * After VERIFY_MAX_ATTEMPTS incorrect codes, the verification token is deleted.
		 *
		 * @api checkVerificationCode
		 * @param string $token Verification token.
		 * @param string $code Verification code given by the user.
		 * @return VerificationResult
		 * @throws \Throwable
		 */
		public function checkVerificationCode(string $token, string $code): VerificationResult {
			$tokenHash = self::hashSecret($token);
			$counted = $this->db->execute(
				'UPDATE user_verification_codes SET attempts = attempts + 1 WHERE token = ? AND attempts < ?',
				[$tokenHash, self::VERIFY_MAX_ATTEMPTS]
			);

			if ($counted === 0)
				return new VerificationResult(VerificationStatus::INVALID_CODE);

			$row = $this->db->getRow('SELECT user_id, code, attempts FROM user_verification_codes WHERE token = ?', [$tokenHash]);
			if ($row === null)
				return new VerificationResult(VerificationStatus::INVALID_CODE);

			if (!\hash_equals(self::toString($row->code), self::hashSecret($code))) {
				if (self::toInt($row->attempts) < self::VERIFY_MAX_ATTEMPTS)
					return new VerificationResult(VerificationStatus::INVALID_CODE);

				$this->db->execute('DELETE FROM user_verification_codes WHERE token = ?', [$tokenHash]);
				return new VerificationResult(VerificationStatus::TOO_MANY_ATTEMPTS);
			}

			$userID = self::toInt($row->user_id);
			$this->db->beginTransaction();
			try {
				$this->db->execute('DELETE FROM user_verification_codes WHERE token = ?', [$tokenHash]);
				$this->db->execute('UPDATE user_accounts SET flags = flags & ? WHERE id = ?', [~UserAccountFlags::REQUIRES_VERIFICATION, $userID]);
				$this->db->commitTransaction();
			} catch (\Throwable $e) {
				$this->db->rollbackTransaction();
				throw $e;
			}

			return new VerificationResult(VerificationStatus::SUCCESS, $userID);
		}

		/**
		 * Send a new verification code for a verification token. The old code stops working.
		 * The code is sent at most one time in VERIFY_SEND_INTERVAL seconds, unless forced.
		 *
		 * @api sendVerificationCode
		 * @param string $token Verification token.
		 * @param bool $force Send the code even if it was sent recently.
		 * @return SendCodeStatus
		 */
		public function sendVerificationCode(string $token, bool $force = false): SendCodeStatus {
			$tokenHash = self::hashSecret($token);
			$row = $this->db->getRow(
				'SELECT v.user_id, a.email FROM user_verification_codes v INNER JOIN user_accounts a ON a.id = v.user_id WHERE v.token = ?',
				[$tokenHash]
			);

			if ($row === null)
				return SendCodeStatus::INVALID_TOKEN;

			$code = self::generateCode();
			$codeHash = self::hashSecret($code);
			$time = \time();

			if ($force)
				$this->db->execute('UPDATE user_verification_codes SET code = ?, last_sent = ? WHERE token = ?', [$codeHash, $time, $tokenHash]);
			else if ($this->db->execute('UPDATE user_verification_codes SET code = ?, last_sent = ? WHERE token = ? AND last_sent <= ?', [$codeHash, $time, $tokenHash, $time - self::VERIFY_SEND_INTERVAL]) === 0)
				return SendCodeStatus::THROTTLED;

			$this->mailer->sendVerificationCode(self::toInt($row->user_id), self::toString($row->email), $token, $code);
			return SendCodeStatus::SUCCESS;
		}

		/**
		 * Send a password reset token to the user account with an e-mail address.
		 * A token is sent at most one time in RESET_SEND_INTERVAL seconds for each account.
		 *
		 * @api requestPasswordReset
		 * @param string $email E-mail address of the account.
		 * @return PasswordResetStatus
		 * @throws \PDOException
		 */
		public function requestPasswordReset(string $email): PasswordResetStatus {
			$row = $this->db->getRow('SELECT id, email, flags FROM user_accounts WHERE email = ?', [self::normalizeEmail($email)]);
			return $this->issuePasswordReset($row);
		}

		/**
		 * Send a password reset token to the user account with an ID.
		 * A token is sent at most one time in RESET_SEND_INTERVAL seconds for each account.
		 *
		 * @api requestPasswordResetByID
		 * @param int $userID ID of the user account.
		 * @return PasswordResetStatus
		 * @throws \PDOException
		 */
		public function requestPasswordResetByID(int $userID): PasswordResetStatus {
			$row = $this->db->getRow('SELECT id, email, flags FROM user_accounts WHERE id = ?', [$userID]);
			return $this->issuePasswordReset($row);
		}

		/**
		 * Set a new password with a password reset token. This removes the FORCE_PASSWORD_RESET flag.
		 * A token is valid for RESET_LIFETIME seconds and can be used one time.
		 *
		 * @api applyPasswordReset
		 * @param string $token Password reset token.
		 * @param Password $password New password.
		 * @return PasswordResetStatus
		 * @throws \Throwable
		 */
		public function applyPasswordReset(string $token, Password $password): PasswordResetStatus {
			$tokenHash = self::hashSecret($token);
			$row = $this->db->getRow('SELECT user_id, reset_sent FROM user_reset_tokens WHERE reset_token = ?', [$tokenHash]);
			if ($row === null)
				return PasswordResetStatus::INVALID_TOKEN;

			$cutoff = \time() - self::RESET_LIFETIME;
			if (self::toInt($row->reset_sent) < $cutoff)
				return PasswordResetStatus::TOKEN_EXPIRED;

			$hash = $password->asHash();

			$this->db->beginTransaction();
			try {
				$deleted = $this->db->execute('DELETE FROM user_reset_tokens WHERE reset_token = ? AND reset_sent >= ?', [$tokenHash, $cutoff]);
				if ($deleted === 0) {
					$this->db->rollbackTransaction();
					return PasswordResetStatus::INVALID_TOKEN;
				}

				$this->db->execute(
					'UPDATE user_accounts SET password = ?, flags = flags & ? WHERE id = ?',
					[$hash, ~UserAccountFlags::FORCE_PASSWORD_RESET, self::toInt($row->user_id)]
				);
				$this->db->commitTransaction();
			} catch (\Throwable $e) {
				$this->db->rollbackTransaction();
				throw $e;
			}

			return PasswordResetStatus::SUCCESS;
		}

		/**
		 * Set or clear a flag on a user account.
		 *
		 * @api setFlag
		 * @param int $userID ID of the user account.
		 * @param int $flag One of the UserAccountFlags constants.
		 * @param bool $state True to set the flag, false to clear it.
		 */
		public function setFlag(int $userID, int $flag, bool $state): void {
			if ($state)
				$this->db->execute('UPDATE user_accounts SET flags = flags | ? WHERE id = ?', [$flag, $userID]);
			else
				$this->db->execute('UPDATE user_accounts SET flags = flags & ? WHERE id = ?', [~$flag, $userID]);
		}

		/**
		 * Check if a user account has a permission. The ALL_PERMISSIONS flag gives all permissions.
		 *
		 * @api hasPermission
		 * @param int $userID ID of the user account.
		 * @param int $permission Permission defined by the application.
		 * @return bool
		 */
		public function hasPermission(int $userID, int $permission): bool {
			$flags = $this->db->getValue('SELECT flags FROM user_accounts WHERE id = ?', [$userID]);
			if (!\is_numeric($flags))
				return false;

			if (((int) $flags & UserAccountFlags::ALL_PERMISSIONS) !== 0)
				return true;

			return $this->db->getValue('SELECT 1 FROM user_permissions WHERE user_id = ? AND permission = ?', [$userID, $permission]) !== null;
		}

		/**
		 * Give a permission to a user account, or take it away.
		 *
		 * @api setPermission
		 * @param int $userID ID of the user account.
		 * @param int $permission Permission defined by the application.
		 * @param bool $state True to give the permission, false to take it away.
		 */
		public function setPermission(int $userID, int $permission, bool $state): void {
			$this->db->execute('DELETE FROM user_permissions WHERE user_id = ? AND permission = ?', [$userID, $permission]);
			if ($state)
				$this->db->execute('INSERT INTO user_permissions (user_id, permission) VALUES (?, ?)', [$userID, $permission]);
		}

		/**
		 * Delete a user account with its sessions, permissions, OAuth identities, verification and password reset.
		 * Delete the application data of the account first.
		 *
		 * @api deleteAccount
		 * @param int $userID ID of the user account.
		 * @return bool False if no account has the ID.
		 * @throws \Throwable
		 */
		public function deleteAccount(int $userID): bool {
			$this->db->beginTransaction();
			try {
				foreach (self::USER_TABLES as $table)
					$this->db->execute('DELETE FROM ' . $table . ' WHERE user_id = ?', [$userID]);

				$deleted = $this->db->execute('DELETE FROM user_accounts WHERE id = ?', [$userID]);
				$this->db->commitTransaction();
			} catch (\Throwable $e) {
				$this->db->rollbackTransaction();
				throw $e;
			}

			return $deleted > 0;
		}

		/**
		 * Issue a password reset token for a user account row and send it.
		 *
		 * @param \stdClass|null $row Account row with id, email and flags, or null.
		 * @return PasswordResetStatus
		 * @throws \PDOException
		 */
		private function issuePasswordReset(?\stdClass $row): PasswordResetStatus {
			if ($row === null)
				return PasswordResetStatus::NO_ACCOUNT;

			if ((self::toInt($row->flags) & UserAccountFlags::OAUTH_ACCOUNT) !== 0)
				return PasswordResetStatus::OAUTH_ACCOUNT;

			$userID = self::toInt($row->id);
			$token = self::generateToken();
			$tokenHash = self::hashSecret($token);
			$time = \time();

			$updated = $this->db->execute(
				'UPDATE user_reset_tokens SET reset_token = ?, reset_sent = ? WHERE user_id = ? AND reset_sent <= ?',
				[$tokenHash, $time, $userID, $time - self::RESET_SEND_INTERVAL]
			);

			if ($updated === 0) {
				try {
					$this->db->execute('INSERT INTO user_reset_tokens (user_id, reset_token, reset_sent) VALUES (?, ?, ?)', [$userID, $tokenHash, $time]);
				} catch (\PDOException $e) {
					if (\str_starts_with((string) $e->getCode(), '23'))
						return PasswordResetStatus::THROTTLED;

					throw $e;
				}
			}

			$this->mailer->sendPasswordReset($userID, self::toString($row->email), $token);
			return PasswordResetStatus::SUCCESS;
		}

		/**
		 * Trim an e-mail address and change it to lower case.
		 *
		 * @param string $email E-mail address.
		 * @return string
		 */
		private static function normalizeEmail(string $email): string {
			return \strtolower(\trim($email));
		}

		/**
		 * Generate a random token of 32 hexadecimal characters.
		 *
		 * @return string
		 */
		private static function generateToken(): string {
			return \bin2hex(\random_bytes(16));
		}

		/**
		 * Generate a random verification code of 5 digits.
		 *
		 * @return string
		 */
		private static function generateCode(): string {
			return (string) \random_int(10000, 99999);
		}

		/**
		 * Get the SHA-256 hash of a token or code. The database stores only the hash.
		 *
		 * @param string $secret Token or code.
		 * @return string
		 */
		private static function hashSecret(string $secret): string {
			return \hash('sha256', $secret);
		}

		/**
		 * Convert a database value to an integer.
		 *
		 * @param mixed $value Database value.
		 * @return int Zero if the value is not numeric.
		 */
		private static function toInt(mixed $value): int {
			return \is_numeric($value) ? (int) $value : 0;
		}

		/**
		 * Convert a database value to a string.
		 *
		 * @param mixed $value Database value.
		 * @return string Empty string if the value is not scalar.
		 */
		private static function toString(mixed $value): string {
			return \is_scalar($value) ? (string) $value : '';
		}
	}
