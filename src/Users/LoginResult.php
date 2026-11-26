<?php
	namespace KrameWork\Users;

	require_once(__DIR__ . '/LoginStatus.php');

	/**
	 * Class LoginResult
	 * Result of a password login attempt.
	 *
	 * @package KrameWork\Users
	 */
	class LoginResult
	{
		/**
		 * LoginResult constructor.
		 *
		 * @api __construct
		 * @param LoginStatus $status Status of the login attempt.
		 * @param int|null $userID ID of the user account. Null if the status is not SUCCESS, REQUIRES_VERIFICATION or REQUIRES_PASSWORD_RESET.
		 */
		public function __construct(public readonly LoginStatus $status, public readonly ?int $userID = null) {}
	}
