<?php
	namespace KrameWork\Users\OAuth;

	require_once(__DIR__ . '/OAuthStatus.php');

	/**
	 * Class OAuthResult
	 * Result of an OAuth login or link.
	 *
	 * @package KrameWork\Users\OAuth
	 */
	class OAuthResult
	{
		/**
		 * OAuthResult constructor.
		 *
		 * @api __construct
		 * @param OAuthStatus $status Status of the login.
		 * @param int|null $userID ID of the user account. Null if the status is not SUCCESS.
		 * @param \stdClass|null $claims Claims of the ID token, for example given_name. Null if the status is not SUCCESS.
		 * @param bool $registered True if the login created a new user account.
		 * @param bool $linked True if the callback linked the identity to the account.
		 */
		public function __construct(public readonly OAuthStatus $status, public readonly ?int $userID = null, public readonly ?\stdClass $claims = null, public readonly bool $registered = false, public readonly bool $linked = false) {}
	}
