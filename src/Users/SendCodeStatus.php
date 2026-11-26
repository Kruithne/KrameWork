<?php
	namespace KrameWork\Users;

	/**
	 * Enum SendCodeStatus
	 * Status of a request to send a verification code again.
	 *
	 * @package KrameWork\Users
	 */
	enum SendCodeStatus
	{
		case SUCCESS;
		case INVALID_TOKEN;
		case THROTTLED;
	}
