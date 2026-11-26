<?php
	namespace KrameWork\Users;

	/**
	 * Enum PasswordResetStatus
	 * Status of a password reset request or a password reset.
	 *
	 * @package KrameWork\Users
	 */
	enum PasswordResetStatus
	{
		case SUCCESS;
		case NO_ACCOUNT;
		case THROTTLED;
		case INVALID_TOKEN;
		case TOKEN_EXPIRED;
		case OAUTH_ACCOUNT;
	}
