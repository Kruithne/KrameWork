<?php
	require_once('KW_Constants.php');
	require_once('KW_ClassLoader.php');

	class KrameSystem
	{
		public function __construct($flags = KW_DEFAULT_FLAGS)
		{
			// Set-up auto loading.
			$this->classLoader = new KW_ClassLoader();
			$this->classLoader->setAllowedExtensions('.php');
			$this->classLoader->addClassPath(dirname(__FILE__));
			spl_autoload_register(array($this->classLoader, 'loadClass'));

			if ($flags & KW_ENABLE_SESSIONS)
				session_start();
		}

		/**
		 * @var KW_ClassLoader
		 */
		private $classLoader;
	}
?>