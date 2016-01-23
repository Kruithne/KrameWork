<?php
	class KW_Template
	{
		/**
		 * Construct the template object.
		 *
		 * @param string $file Path of the file to use for this template.
		 */
		public function __construct($file)
		{
			if (defined('KW_TEMPLATE_DIR'))
				$file = KW_TEMPLATE_DIR . $file;

			$this->data = array();
			if (file_exists($file))
				$this->__set('@@template@@', $file);
			else if (file_exists($file . '.php'))
				$this->__set('@@template@@', $file . '.php');
			else
				trigger_error('Missing template file "' . $file . '"!', E_USER_ERROR);
		}

		/**
		 * Get a value stored in this template.
		 *
		 * @param mixed $key The key of the value to get.
		 * @return mixed|null The value or null if nothing is set at the provided key.
		 */
		public function __get($key)
		{
			return array_key_exists($key, $this->data) ? $this->data[$key] : null;
		}

		/**
		 * Sets a value for this template which will be interjected into the underlying file.
		 *
		 * @param mixed $key The key to store the value under.
		 * @param mixed $value The value to store in the template.
		 */
		public function __set($key, $value)
		{
			$this->data[$key] = $value;
		}

		/**
		 * Compiles the template; interjecting the template values into the file and returning the result.
		 *
		 * @return string The file output with injected values.
		 */
		public function __toString()
		{
			ob_start();
			extract($this->data);

			// This is safe, as constructor throws an error.
			require($this->__get('@@template@@'));

			return ob_get_clean();
		}

		/**
		 * @var array
		 */
		protected $data;
	}
?>
