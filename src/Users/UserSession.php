<?php
	namespace KrameWork\Users;

	require_once(__DIR__ . '/UserAccountFlags.php');

	/**
	 * Class UserSession
	 * A logged-in session of a user account.
	 *
	 * @package KrameWork\Users
	 */
	class UserSession
	{
		/**
		 * UserSession constructor.
		 *
		 * @api __construct
		 * @param string $sessionID ID of the session.
		 * @param int $userID ID of the user account.
		 * @param int $flags UserAccountFlags of the user account when the session was read.
		 * @param int $updated Unix time of the last refresh of the session.
		 */
		public function __construct(public readonly string $sessionID, public readonly int $userID, public readonly int $flags, public readonly int $updated) {}

		/**
		 * Check if the user account has a flag set.
		 *
		 * @api hasFlag
		 * @param int $flag One of the UserAccountFlags constants.
		 * @return bool
		 */
		public function hasFlag(int $flag): bool {
			return ($this->flags & $flag) === $flag;
		}
	}
