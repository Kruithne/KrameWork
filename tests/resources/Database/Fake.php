<?php
	namespace KrameWork\Database\Driver;

	use KrameWork\Database\ConnectionString;

	class Fake implements Generic
	{
		public function __construct(ConnectionString $connection) {
		}

		/**
		 * Execute a query and return an array of ArrayObjects
		 * @api getAll
		 * @param string $sql An SQL query statement
		 * @param array $param An array of values to inject in the statement
		 * @return \stdClass[]
		 */
		function getAll(string $sql, array $param): array {
			return [(object)['sql' => $sql, 'param' => $param]];
		}

		/**
		 * Execute a query and return the first row as an ArrayObject
		 * @api getRow
		 * @param string $sql An SQL query statement
		 * @param array $param An array of values to inject in the statement
		 * @return \stdClass|null
		 */
		function getRow(string $sql, array $param) {
			return (object)['sql' => $sql, 'param' => $param];
		}

		/**
		 * Execute a query and return the first column of each row
		 * @api getColumn
		 * @param string $sql An SQL query statement
		 * @param array $param An array of values to inject in the statement
		 * @return array
		 */
		function getColumn(string $sql, array $param): array {
			return [$sql, $param];
		}

		/**
		 * Execute a query and return the first value of the first row
		 * @api getValue
		 * @param string $sql An SQL query statement
		 * @param array $param An array of values to inject in the statement
		 * @return mixed
		 */
		function getValue(string $sql, array $param) {
			return $sql;
		}

		/**
		 * Execute a statement and return the number of affected rows
		 * @api execute
		 * @param string $sql An SQL statement
		 * @param array $param An array of values to inject in the statement
		 * @return int
		 */
		function execute(string $sql, array $param): int {
			return count($param);
		}

		function getLastError() {
			return null;
		}

		function getLastQueryError() {
			return null;
		}

		function getLastInsertID(): string {
			return '0';
		}

		/**
		 * @api beginTransaction
		 * Open a database transaction
		 */
		function beginTransaction()
		{
		}

		/**
		 * @api rollbackTransaction
		 * Rolls back the current transaction
		 */
		function rollbackTransaction()
		{
		}

		/**
		 * @api commitTransaction
		 * Commits the current transaction
		 */
		function commitTransaction()
		{
		}
	}