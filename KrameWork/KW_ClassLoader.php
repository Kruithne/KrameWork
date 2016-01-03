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
						require_once($path);
						return;
					}
				}
				if(self::$recursive)
					foreach (scandir($classPath) as $node)
					{
						if ($node === '.' || $node === '..')
							continue;

						if (is_dir($classPath . DIRECTORY_SEPARATOR . $node))
							array_unshift($classPath . DIRECTORY_SEPARATOR . $node);
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