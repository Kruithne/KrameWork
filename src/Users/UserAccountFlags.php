<?php
	namespace KrameWork\Users;

	/**
	 * Class UserAccountFlags
	 * Bit flags for the flags column of a user account.
	 * Bits 0 to 4 are used by KrameWork. Applications can use bits 5 to 30.
	 *
	 * @package KrameWork\Users
	 */
	class UserAccountFlags
	{
		const NONE = 0;
		const ACCOUNT_DISABLED = 1 << 0;
		const REQUIRES_VERIFICATION = 1 << 1;
		const FORCE_PASSWORD_RESET = 1 << 2;
		const ALL_PERMISSIONS = 1 << 3;
		const OAUTH_ACCOUNT = 1 << 4;
	}
