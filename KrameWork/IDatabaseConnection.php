<?php
	interface IDatabaseConnection
	{
		/**
		 * Return the type of DBMS
		 */
		public function getType();

		/**
		 * Returns a database statement.
		 *
		 * @param string $sql An SQL query for this statement.
		 * @return IDatabaseStatement A database statement.
		 */
		public function prepare($sql);

		/**
		 * Execute an SQL query.
		 *
		 * @param string $sql An SQL query string.
		 * @return int Amount of rows effected by the execution of this query.
		 */
		public function execute($sql);

		/**
		 * @param string $table The name of the table to check.
		 * @return string ID of the last inserted row.
		 */
		public function getLastInsertID($table);
	}
?>
