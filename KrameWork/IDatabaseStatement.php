<?php
	interface IDatabaseStatement
	{
		/**
		 * Called when an unknown property is set.
		 *
		 * @param string $key
		 * @param object $value
		 */
		public function __set($key, $value);

		/**
		 * Retrieve the SQL query string set in this statement.
		 *
		 * @return null|string Statement SQL, will be null if not yet set.
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

		/**
		 * Set the parameter type.
		 * @param string $key
		 * @param int $type
		 * @see http://php.net/manual/en/pdo.constants.php
		 */
		public function setType($key, $type);

		/**
		 * Copies the values already stored inside a row.
		 *
		 * @param IDataContainer $row A row to extract from.
		 * @return IDatabaseStatement Statement instance.
		 */
		public function copyValuesFromRow(IDataContainer $row);

		/**
		 * Executes the statement and collects retrieved rows.
		 *
		 * @return IDatabaseStatement $this Database statement instance.
		 */
		public function execute();

		/**
		 * Returns an array of database rows retrieved. Will be empty if the statement is not executed.
		 *
		 * @return IDataContainer[]
		 */
		public function getRows();

		/**
		 * Returns the first row of the data retrieved. Will by null if no results were returned.
		 * @return IDataContainer|null
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
