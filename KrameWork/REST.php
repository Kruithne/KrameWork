<?php
	class REST
	{
		/**
		 * Runs a value through various sanitizing functions.
		 *
		 * @param $data mixed The data to be sanitized.
		 * @return string The clean and shiny result.
		 */
		private static function cleanData($data)
		{
			return htmlentities(utf8_decode(trim($data)), ENT_COMPAT | ENT_HTML401, self::$encoding);
		}

		/**
		 * Return a value from an array after sanitizing it.
		 *
		 * @param $array array Array of which to pull the value from.
		 * @param $key string Key of the value with the array.
		 * @return null|string
		 */
		private static function getData($array, $key)
		{
			if (array_key_exists($key, $array))
			{
				$data = self::cleanData($array[$key]);
				if (strlen($data) > 0)
					return $data;
			}

			return null;
		}

		/**
		 * Get a value from the POST array after sanitizing.
		 *
		 * @param mixed $key The key of the value to return.
		 * @return mixed|null The filtered value or null if the key does not exist or is empty.
		 */
		public static function Post($key)
		{
			return self::getData($_POST, $key);
		}

		/**
		 * Get a value from the GET array after sanitizing.
		 *
		 * @param mixed $key The key of the value to return.
		 * @return mixed|null The filtered value or null if the key does not exist or is empty.
		 */
		public static function Get($key)
		{
			return self::getData($_GET, $key);
		}

		/**
		 * Safely check (with spoof protection) for file(s).
		 * @param string $key
		 * @return string[]
		 */
		public static function File($key)
		{
			if (empty($_FILES))
				return array();

			if (!array_key_exists($key, $_FILES))
				return array();

			$data = array();
			$tmp = $_FILES[$key]['tmp_name'];
			if (is_array($tmp))
			{
				foreach ($tmp as $node)
					if (strlen($node) > 0 && file_exists($node) && is_uploaded_file($node))
						$data[] = $node;
			}
			else
			{
				if (strlen($tmp) > 0 && file_exists($tmp) && is_uploaded_file($tmp))
					$data[] = $tmp;
			}

			return $data;
		}

		/**
		 * Checks all arguments passed to it are not null.
		 * @return bool
		 */
		public static function Check()
		{
			foreach (func_get_args() as $arg)
				if ($arg === null)
					return false;

			return true;
		}

		/**
		 * Set the encoding used when cleaning data.
		 * @param string $encoding
		 */
		public static function setEncoding($encoding)
		{
			self::$encoding = $encoding;
		}

		/**
		 * @var string
		 */
		private static $encoding = 'UTF-8';
	}
?>