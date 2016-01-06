<?php
	class Path
	{
		/**
		 * Join parts of a path together and validate (clean) it using Path::Clean.
		 * @return string
		 */
		public static function Join()
		{
			return self::Clean(implode(DIRECTORY_SEPARATOR, func_get_args()));
		}

		/**
		 * Clean a path, removing trailing/leading/duplicate slashes and converting all slashes to
		 * the system default.
		 * @param $path
		 * @return string
		 */
		public static function Clean($path)
		{
			$path = str_replace(array('/', '\\'), DIRECTORY_SEPARATOR, $path);
			$parts = array_filter(explode(DIRECTORY_SEPARATOR, $path), 'strlen');
			$absolutes = array();

			foreach ($parts as $part)
			{
				if ('.' == $part)
					continue;

				if ('..' == $part)
					array_pop($absolutes);
				else
					$absolutes[] = $part;
			}

			return implode(DIRECTORY_SEPARATOR, $absolutes);
		}
	}
?>