<?php
	class KW_ClassLoader
	{
		public function loadClass($className)
		{
			foreach ($this->classPaths as $classPath)
			{
				foreach (scandir($classPath) as $node)
				{
					if ($node === '.' || $node === '..')
						continue;

					foreach ($this->allowedExtensions as $extension)
					{
						$path = $classPath . DIRECTORY_SEPARATOR . $className . $extension;
						if (file_exists($path))
						{
							require_once($path);
							return;
						}
					}
				}
			}
		}

		public function setAllowedExtensions($extensionString)
		{
			$this->allowedExtensions = explode(',', $extensionString);
		}

		public function addClassPath($classPath)
		{
			$this->classPaths[] = $classPath;
		}

		private $allowedExtensions = Array();
		private $classPaths = Array();
	}
?>