<?php
	namespace KrameWork\Users;

	/**
	 * Enum LoginStatus
	 * Status of a password login attempt.
	 *
	 * @package KrameWork\Users
	 */
	enum LoginStatus
	{
		case SUCCESS;
		case NO_ACCOUNT;
		case INVALID_PASSWORD;
		case REQUIRES_VERIFICATION;
		case ACCOUNT_DISABLED;
		case REQUIRES_PASSWORD_RESET;
	}
