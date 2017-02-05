<?php
	// This file is called from the KrameSystem.php file.
	define('KW_ENABLE_SESSIONS', 0x1);
	define('KW_ERROR_HANDLER', 0x2);
	define('KW_LEAVE_ERROR_LEVEL', 0x4);
	define('KW_SECURE_SESSIONS', 0x8);
	define('KW_AUTOLOAD_RECURSIVE', 0x10);
	define('KW_PRELOAD_CLASSES', 0x20);
	define('KW_AUTOBIND_INTERFACES', 0x40);
	define('KW_AUTO_ADD_DEPENDS', 0x80);
	define('KW_DEFAULT_FLAGS', KW_ENABLE_SESSIONS | KW_ERROR_HANDLER | KW_SECURE_SESSIONS);

	define('AUTH_ERR_UNKNOWN', -1);
	define('AUTH_ERR_NOSECRET', -2);
	define('AUTH_ERR_LOCKOUT', -3);
	define('AUTH_NONE', 0);
	define('AUTH_OK', 1);
	define('AUTH_OK_OLD', 2);
	define('AUTH_MULTIFACTOR', 3);

	define('HTML_EOL', '<br/>');
