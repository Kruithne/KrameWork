<?php
	namespace KrameWork\Users;

	/**
	 * Enum VerificationStatus
	 * Status of a verification code check.
	 *
	 * @package KrameWork\Users
	 */
	enum VerificationStatus
	{
		case SUCCESS;
		case INVALID_CODE;
		case TOO_MANY_ATTEMPTS;
		case CODE_EXPIRED;
	}
