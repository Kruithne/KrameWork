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
			$queue = array_values(self::$classPaths);
			while (count($queue))
			{
				$classPath = array_pop($queue);
				foreach (self::$allowedExtensions as $extension)
				{
					$path = $classPath . DIRECTORY_SEPARATOR . $className . $extension;
					if (file_exists($path))
					{
						error_log('Autoloading '.$className.'..');
						require_once($path);
						error_log('Autoloading '.$className.' succeeded..');
						return;
					}
				}
				if(self::$recursive)
					foreach (scandir($classPath) as $node)
					{
						if ($node === '.' || $node === '..')
							continue;

						$subClassPath = $classPath . DIRECTORY_SEPARATOR . $node;
						if (is_dir($subClassPath))
							array_unshift($subClassPath, $subClassPath);
					}
			}
		}

		/**
		 * Sets which file extensions can be automatically loaded by the class loader.
		 *
		 * @param String $extensionString A comma-separated list of extensions with period included.
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
		 *
		 * @param String $classPath The directory to add to the loader.
		 */
		public static function addClassPath($classPath)
		{
			self::$classPaths[] = rtrim($classPath, "\x2F\x5C");
		}

		private static $allowedExtensions = Array();
		private static $classPaths = Array();
		private static $recursive = false;
	}
?>