<?php
	namespace KrameWork\Users;

	/**
	 * Interface IUserMailer
	 * Sends account mails for the user system. The application supplies the content and the transport.
	 *
	 * @package KrameWork\Users
	 */
	interface IUserMailer
	{
		/**
		 * Send a verification code to a user.
		 *
		 * @api sendVerificationCode
		 * @param int $userID ID of the user account.
		 * @param string $email E-mail address of the user account.
		 * @param string $token Verification token that identifies the verification.
		 * @param string $code Five-digit verification code.
		 */
		public function sendVerificationCode(int $userID, string $email, string $token, string $code): void;

		/**
		 * Send a password reset token to a user.
		 *
		 * @api sendPasswordReset
		 * @param int $userID ID of the user account.
		 * @param string $email E-mail address of the user account.
		 * @param string $token Password reset token.
		 */
		public function sendPasswordReset(int $userID, string $email, string $token): void;
	}
