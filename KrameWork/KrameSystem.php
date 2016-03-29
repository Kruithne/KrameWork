<?php
	require_once('KW_Constants.php');
	require_once('IDependencyInjector.php');
	require_once('IManyInject.php');
	require_once('KW_DependencyInjector.php');
	require_once('KW_ClassLoader.php');

	class KrameSystem extends KW_DependencyInjector
	{
		/**
		 * Initialize a new KrameWork system.
		 *
		 * @param int $flags Flags to control the behavior of the system.
		 * @param string[] $paths A list of auto load paths to add.
		 * @param array $components Pre-load classes with this collection
		 * @param array $bindings Pre-load type bindings with this collection
		 * @param array $decorators Pre-load decorators with this collection
		 */
		public function __construct($flags = KW_DEFAULT_FLAGS, $paths = null, $components = null, $bindings = null, $decorators = null)
		{
			parent::__construct($components, $bindings, $decorators);
			$this->flags = $flags;

			// Set-up auto loading.
			if ($flags & KW_AUTOBIND_INTERFACES)
				$this->bindInterfaces = true;

			if ($flags & KW_PRELOAD_CLASSES)
				$this->preload = true;

			if ($flags & KW_AUTO_ADD_DEPENDS)
				$this->autoAddDepends = true;

			if ($flags & KW_AUTOLOAD_RECURSIVE)
				KW_ClassLoader::enableRecursion();

			KW_ClassLoader::setAllowedExtensions('.php');
			KW_ClassLoader::addClassPath(dirname(__FILE__));

			if($paths)
				foreach($paths as $path)
					KW_ClassLoader::addClassPath($path);

			$loadClassFunction = 'KW_ClassLoader::loadClass';
			spl_autoload_register($loadClassFunction);
			ini_set('unserialize_callback_func', $loadClassFunction);

			if ($flags & KW_ERROR_HANDLER)
			{
				$handler = new KW_ErrorHandler(
					!($flags & KW_LEAVE_ERROR_LEVEL),
					($flags & KW_ANY_ERROR) ? 0 : 10,
					!($flags & KW_ANY_ERROR)
				);
				if ($this->bindInterfaces)
					$this->addComponent($handler);
				else
					$this->addBinding('IErrorHandler', $handler);
			}
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
					if (isset($_SERVER['REMOTE_ADDR']))
						$remote = $_SERVER['REMOTE_ADDR'];

					if (!isset($_SESSION['__client__']))
						$_SESSION['__client__'] = $remote;

					if (isset($_SESSION['__client__']) && $_SESSION['__client__'] != $remote)
					{
						if (function_exists('session_abort'))
						{
							session_regenerate_id(false);
							session_destroy();
							session_start();
						}
						else
						{
							throw new Exception('Stolen session');
						}
					}
				}
			}

			// Invoke classes that implement IStartup
			$startup = $this->getComponents("IStartup");
			if ($startup)
			{
				/**
				 * @var IStartup $component
				 */
				foreach ($startup as $component)
					$component->start();
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
		 * @return null|KW_ErrorHandler The error handler, will be null if error handling is disabled in this instance.
		 */
		public function getErrorHandler()
		{
			try
			{
				return $this->getComponent('KW_ErrorHandler');
			}
			catch(KW_ClassDependencyException $e)
			{
				return null;
			}
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
		 * @var int
		 */
		private $flags;
	}
?>
