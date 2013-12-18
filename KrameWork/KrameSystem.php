<?php
	namespace KrameWork;

	require_once('constants.php');

	class KrameSystem
	{
		public function __construct($flags = KW_ENABLE_SESSIONS)
		{
			// Set-up auto loading.
			spl_autoload_extensions('.php');
			spl_autoload_register();

			if ($flags & KW_ENABLE_SESSIONS)
				session_start();
		}
	}
?>