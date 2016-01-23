<?php
	class KW_ClassLoader
	{
		/**
		 * Loads a file with matching class name (case-sensitive) from the linked class paths.
		 *
		 * @param string $className The name of the class.
		 */
		public static function loadClass($className)
		{
			$parts = explode('\\', $className);
			$className = $parts[count($parts) - 1];

			$queue = array_values(self::$classPaths);
			while (count($queue))
			{
				$classPath = array_pop($queue);
				foreach (self::$allowedExtensions as $extension)
				{
					$path = $classPath . DIRECTORY_SEPARATOR . $className . $extension;
					if (file_exists($path))
					{
						if (self::$debug)
							error_log('Autoloading ' . $className . '..');

						require_once($path);

						if (self::$debug)
							error_log('Autoloading ' . $className . ' succeeded..');

						return;
					}
				}

				if (self::$recursive)
				{
					foreach (scandir($classPath) as $node)
					{
						if ($node === '.' || $node === '..')
							continue;

						$subClassPath = $classPath . DIRECTORY_SEPARATOR . $node;
						if (is_dir($subClassPath))
							array_unshift($queue, $subClassPath);
					}
				}
			}
		}

		/**
		 * Sets which file extensions can be automatically loaded by the class loader.
		 *
		 * @param string $extensionString A comma-separated list of extensions with period included.
		 */
		public static function setAllowedExtensions($extensionString)
		{
			self::$allowedExtensions = explode(',', $extensionString);
		}

		/**
		 * Turns on recursive scanning for files in the class path
		 */
		public static function enableRecursion()
		{
			self::$recursive = true;
		}

		/**
		 * Adds a directory to the loader which will be checked for matching class files.
		 * Takes any and all arguments given.
		 */
		public static function addClassPath()
		{
			foreach (func_get_args() as $arg)
				self::$classPaths[] = rtrim($arg, "\x2F\x5C");
		}

		/**
		 * Enable debugging for the class loader.
		 */
		public static function enableDebug()
		{
			self::$debug = true;
		}

		/**
		 * @var string[]
		 */
		private static $allowedExtensions = array();

		/**
		 * @var string[]
		 */
		private static $classPaths = array();

		/**
		 * @var bool
		 */
		private static $recursive = false;

		/**
		 * @var bool
		 */
		private static $debug = false;
	}
?>