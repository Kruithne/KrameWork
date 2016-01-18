<?php
	interface ICRUD
	{
		/**
		 * Override this to return a string or an array of string with the names of the primary key columns
		 * @return mixed
		 */
		public function getKey();

		/**
		 * Override this to define whether the table has an automatic primary key (id)
		 * @return bool
		 */
		public function hasAutoKey();

		/**
		 * Override this to return an array of column names in the table
		 * @return string[]
		 */
		public function getValues();

		/**
		 * Type hint for column types
		 * @param string $key The name of the column
		 * @return int One of the PDO::PARAM_* constants
		 */
		public function getKeyType($key);

		/**
		 * Override this method to return a custom type from the database layer
		 * @param IDataContainer $data
		 * @return mixed The data you want your row represented as
		 */
		public function getNewObject($data);

		/**
		 * Generates and prepares SQL statements for accessing your table, override this if you want to add some custom queries.
		 */
		public function prepare();

		/**
		 * Persist an object to the underlying table by calling INSERT on the database
		 * @param object $object An object with properties matching the values, and if not an autokey, keys, of the table spec.
		 * @return mixed An object as returned by getNewObject.
		 */
		public function create($object);

		/**
		 * Fetch an object, or a list of objects.
		 *
		 * Non-relational tables do not have keys, call without a key and always get the entire table.
		 *
		 * Composite key tables expect an array with properties matching the names return in the array from getKey().
		 * To do a partial key match, pass an asterix for a key component to fetch any value of that column.
		 *
		 * Simple key tables expect an int or string with the value of the key column.
		 * @param mixed $key Descriptor of what to fetch, see description
		 * @return mixed The result set or single object matching the key
		 */
		public function read($key = null);

		/**
		 * Persist an existing object to the table with an UPDATE query.
		 * @param object $object An object with properties matching all entries of the key and values specifications of the table
		 */
		public function update($object);

		/**
		 * Remove an existing object from the table with a DELETE query.
		 * @param object $object An object with properties matching all entries of the key specification of the table
		 */
		public function delete($object);
	}
?>
