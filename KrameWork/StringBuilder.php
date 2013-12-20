<?php
	class StringBuilder
	{
		/**
		 * Appends a string to the end of the string builder.
		 * @param string $string A string to append.
		 * @return StringBuilder $this The string builder instance.
		 */
		public function append($string)
		{
			$this->string .= $string;
			return $this;
		}

		/**
		 * Prepends a string to the start of the string builder.
		 * @param string $string The string to prepend.
		 * @return StringBuilder $this The string builder instance.
		 */
		public function prepend($string)
		{
			$this->string = $string . $this->string;
			return $this;
		}

		/**
		 * Called when this object is cast to a string.
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