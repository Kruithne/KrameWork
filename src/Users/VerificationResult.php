<?php
	namespace KrameWork\Users;

	require_once(__DIR__ . '/VerificationStatus.php');

	/**
	 * Class VerificationResult
	 * Result of a verification code check.
	 *
	 * @package KrameWork\Users
	 */
	class VerificationResult
	{
		/**
		 * VerificationResult constructor.
		 *
		 * @api __construct
		 * @param VerificationStatus $status Status of the check.
		 * @param int|null $userID ID of the verified user account. Null if the status is not SUCCESS.
		 */
		public function __construct(public readonly VerificationStatus $status, public readonly ?int $userID = null) {}
	}
