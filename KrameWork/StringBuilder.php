<?php
	class StringBuilder
	{
		/**
		 * Appends a string to the end of the string builder.
		 *
		 * @param string $string A string to append.
		 * @param int $times How many times the string should be appended.
		 * @return StringBuilder $this The string builder instance.
		 */
		public function append($string, $times = 1)
		{
			$this->string .= str_repeat($string, $times);
			return $this;
		}

		/**
		 * Prepends a string to the start of the string builder.
		 *
		 * @param string $string The string to prepend.
		 * * @param int $times How many times the string should be prepended.
		 * @return StringBuilder $this The string builder instance.
		 */
		public function prepend($string, $times = 1)
		{
			$this->string = $string . str_repeat($this->string, $times);
			return $this;
		}

		/**
		 * Called when this object is cast to a string.
		 *
		 * @return string The string contained by the builder.
		 */
		public function __toString()
		{
			return $this->string;
		}

		/**
		 * @var string The string manipulated by this builder.
		 */
		private $string;
	}
?>