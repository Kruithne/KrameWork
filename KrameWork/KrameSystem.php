<?php
	require_once('KW_Constants.php');
	require_once('IDependencyInjector.php');
	require_once('KW_DependencyInjector.php');
	require_once('KW_ClassLoader.php');

	class KrameSystem extends KW_DependencyInjector
	{
		/**
		 * Initialize a new KrameWork system.
		 *
		 * @param int $flags Flags to control the behavior of the system.
		 */
		public function __construct($flags = KW_DEFAULT_FLAGS)
		{
			$this->flags = $flags;
			// Set-up auto loading.
			if($flags & KW_AUTOBIND_INTERFACES)
				$this->bindInterfaces = true;
			if($flags & KW_PRELOAD_CLASSES)
				$this->preload = true;
			if($flags & KW_AUTOLOAD_RECURSIVE)
				KW_ClassLoader::enableRecursion();
			KW_ClassLoader::setAllowedExtensions('.php');
			KW_ClassLoader::addClassPath(dirname(__FILE__));

			$loadClassFunction = 'KW_ClassLoader::loadClass';
			spl_autoload_register($loadClassFunction);
			ini_set('unserialize_callback_func', $loadClassFunction);

			if ($flags & KW_ERROR_HANDLER)
				$this->errorHandler = new KW_ErrorHandler(!($flags & KW_LEAVE_ERROR_LEVEL));
		}

		/**
		 * Start system after initialization has completed.
		 */
		public function start()
		{
			if ($this->flags & KW_ENABLE_SESSIONS)
			{
				if (!self::sessionIsStarted())
					session_start();

				if (($this->flags & KW_SECURE_SESSIONS) && self::sessionIsStarted())
				{
					$remote = '';
					if(isset($_SERVER['REMOTE_ADDR']))
						$remote = $_SERVER['REMOTE_ADDR'];
					if(!isset($_SESSION['__client__']))
						$_SESSION['__client__'] = $remote;
					if(isset($_SESSION['__client__']) && $_SESSION['__client__'] != $remote)
					{
						if(function_exists('session_abort'))
						{
							session_regenerate_id(false);
							session_destroy();
							session_start();
						}
						else
							throw new Exception('Stolen session');
					}
				}
			}
		}

		/**
		 * Adds a directory to the loader which will be checked for matching class files.
		 *
		 * @param String $classPath The directory to add to the loader.
		 */
		public function addAutoLoadPath($classPath)
		{
			KW_ClassLoader::addClassPath($classPath);
		}

		/**
		 * Sets which file extensions can be automatically loaded by the class loader.
		 *
		 * @param String $extensionString A comma-separated list of extensions with period included.
		 */
		public function setAutoLoadExtensions($extensionString)
		{
			KW_ClassLoader::setAllowedExtensions($extensionString);
		}

		/**
		 * Returns the error handler for this KrameWork system instance.
		 *
		 * @return null|KW_ErrorHandler The error handler, will be NULL if error handling is disabled in this instance.
		 */
		public function getErrorHandler()
		{
			return $this->errorHandler;
		}

		public static function sessionIsStarted()
		{
			if (php_sapi_name() == 'cli')
				return false;

			if (function_exists('session_status'))
				return session_status() == PHP_SESSION_ACTIVE;
			else
				return session_id() === '' ? false : true;
		}

		/**
		 * @var KW_ErrorHandler
		 */
		private $errorHandler;
		private $flags;
	}
?>
