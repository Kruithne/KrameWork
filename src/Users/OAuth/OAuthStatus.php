<?php
	namespace KrameWork\Users\OAuth;

	/**
	 * Enum OAuthStatus
	 * Status of an OAuth login or link.
	 *
	 * @package KrameWork\Users\OAuth
	 */
	enum OAuthStatus
	{
		case SUCCESS;
		case INVALID_STATE;
		case UNKNOWN_PROVIDER;
		case TOKEN_EXCHANGE_FAILED;
		case INVALID_ID_TOKEN;
		case MISSING_EMAIL;
		case EMAIL_NOT_VERIFIED;
		case EMAIL_IN_USE;
		case ACCOUNT_NOT_FOUND;
		case ACCOUNT_DISABLED;
		case IDENTITY_IN_USE;
	}
