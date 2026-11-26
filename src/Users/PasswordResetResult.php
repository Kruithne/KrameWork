<?php
	namespace KrameWork\Users;

	require_once(__DIR__ . '/PasswordResetStatus.php');

	/**
	 * Class PasswordResetResult
	 * Result of a password reset.
	 *
	 * @package KrameWork\Users
	 */
	class PasswordResetResult
	{
		/**
		 * PasswordResetResult constructor.
		 *
		 * @api __construct
		 * @param PasswordResetStatus $status Status of the password reset.
		 * @param int|null $userID ID of the user account. Null if the status is not SUCCESS.
		 */
		public function __construct(public readonly PasswordResetStatus $status, public readonly ?int $userID = null) {}
	}
