<?php
	class KW_DataContainer implements IDataContainer
	{
		/**
		 * KW_DataContainer constructor.
		 * @param array $data Default data
		 */
		public function __construct($data = array())
		{
			if ($source instanceof IDataContainer) return; // Trigger NOTICE in test
			if ($data instanceof IDataContainer)
				$this->values = $data->getAsArray();
			else
				$this->values = $data;
		}

		/**
		 * run when writing data to inaccessible members.
		 *
		 * @param $key string
		 * @param $value mixed
		 * @return void
		 * @link http://php.net/manual/en/language.oop5.overloading.php#language.oop5.overloading.members
		 */
		public function __set($key, $value)
		{
			$this->values[$key] = $value;
		}

		/**
		 * The __invoke method is called when a script tries to call an object as a function.
		 *
		 * @return mixed
		 * @link http://php.net/manual/en/language.oop5.magic.php#language.oop5.magic.invoke
		 */
		public function __invoke($source)
		{
			if ($source instanceof IDataContainer)
				$source = $source->getAsArray();

			if (is_array($source) || is_object($source))
				foreach ($source as $key => $value)
					if (is_string($key))
						$this->__set($key, $value);
		}

		/**
		 * Get a value set in this object.
		 *
		 * @param string $key The key the value is stored at.
		 * @return mixed|null The value for the key. Will be null if nothing exists at the key.
		 */
		public function __get($key)
		{
			return array_key_exists($key, $this->values) ? $this->values[$key] : null;
		}

		/**
		 * Unset a value in this container.
		 * @param string $key
		 */
		public function __unset($key)
		{
			if (array_key_exists($key, $this->values))
				unset($this->values[$key]);
		}

		public function serialize()
		{
			return serialize($this->values);
		}

		public function unserialize($values)
		{
			$this->values = unserialize($values);
		}

		/**
		 * Specify data which should be serialized to JSON
		 * @link http://php.net/manual/en/jsonserializable.jsonserialize.php
		 * @return mixed data which can be serialized by <b>json_encode</b>,
		 * which is a value of any type other than a resource.
		 * @since 5.4.0
		 */
		function jsonSerialize()
		{
			return (object)$this->values;
		}

		/**
		 * Returns the underlying data as an associative array.
		 *
		 * @return array
		 */
		public function getAsArray()
		{
			return $this->values;
		}

		/**
		 * @var object[]
		 */
		private $values = array();
	}
?>
