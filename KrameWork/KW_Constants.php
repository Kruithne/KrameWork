<?php
	// This file is called from the KrameSystem.php file.
	define('KW_ENABLE_SESSIONS', 0x1);
	define('KW_ERROR_HANDLER', 0x2);
	define('KW_LEAVE_ERROR_LEVEL', 0x4);
	define('KW_SECURE_SESSIONS', 0x8);
	define('KW_AUTOLOAD_RECURSIVE', 0x10);
	define('KW_PRELOAD_CLASSES', 0x20);
	define('KW_DEFAULT_FLAGS', KW_ENABLE_SESSIONS | KW_ERROR_HANDLER | KW_SECURE_SESSIONS);

	define('HTML_EOL', '<br/>');
?>