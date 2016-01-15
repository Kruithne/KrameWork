<?php
	interface IDataContainer extends Serializable, JsonSerializable
	{
		/**
		 * run when writing data to inaccessible members.
		 *
		 * @param $key string
		 * @param $value mixed
		 * @return void
		 * @link http://php.net/manual/en/language.oop5.overloading.php#language.oop5.overloading.members
		 */
		public function __set($key, $value);

		/**
		 * The __invoke method is called when a script tries to call an object as a function.
		 *
		 * @return mixed
		 * @link http://php.net/manual/en/language.oop5.magic.php#language.oop5.magic.invoke
		 */
		public function __invoke($arr);

		/**
		 * Get a value set in this object.
		 *
		 * @param string $key The key the value is stored at.
		 * @return mixed|null The value for the key. Will be NULL if nothing exists at the key.
		 */
		public function __get($key);

		/**
		 * Returns the underlying data as an associative array.
		 *
		 * @return array
		 */
		public function getAsArray();
	}
?>
