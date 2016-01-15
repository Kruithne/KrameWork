<?php
	interface IDatabaseStatement
	{
		/**
		 * Retrieve the SQL query string set in this statement.
		 *
		 * @return null|string Statement SQL, will be NULL if not yet set.
		 */
		public function getQueryString();

		/**
		 * Sets a value for this statement.
		 *
		 * @param string $key The key used in the statement.
		 * @param mixed $value The value to assign to this key.
		 * @return KW_DatabaseStatement $this Statement instance.
		 */
		public function setValue($key, $value);

		public function setType($key, $type);

		public function __set($key, $value);

		/**
		 * Copies the values already stored inside a row.
		 *
		 * @param KW_DatabaseRow $row A row to extract from.
		 * @param string $prependChar Character to prepend each key with.
		 * @return KW_DatabaseStatement Statement instance.
		 */
		public function copyValuesFromRow($row, $prependChar = ':');

		/**
		 * Executes the statement and collects retrieved rows.
		 *
		 * @return KW_DatabaseStatement $this Database statement instance.
		 */
		public function execute();

		/**
		 * Returns an array of database rows retrieved. Will be empty if the statement is not executed.
		 *
		 * @return KW_DatabaseRow[]
		 */
		public function getRows();

		/**
		 * Returns the first row of the data retrieved. Will by NULL if no results were returned.
		 * @return KW_DatabaseRow|null
		 */
		public function getFirstRow();

		/**
		 * Returns the amount of rows in this statement.
		 *
		 * @return int Amount of rows in this statement. Always zero until executed.
		 */
		public function getRowCount();

		/**
		 * @return string
		 */
		public function getErrorCode();
	}
?>
