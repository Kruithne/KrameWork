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
			return preg_replace("@[//\\\\]+@", DIRECTORY_SEPARATOR, $path);
		}
	}
