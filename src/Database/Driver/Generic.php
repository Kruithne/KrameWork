<?php
	namespace KrameWork\Database\Driver;

	/**
	 * This interface defined the methods a database driver need to support the KrameWork database stack.
	 * @package KrameWork\Database\Driver
	 */
	interface Generic
	{
		/**
		 * Execute a query and return an array of ArrayObjects
		 * @api getAll
		 * @param string $sql An SQL query statement
		 * @param array<int|string, mixed> $param An array of values to inject in the statement
		 * @return \stdClass[]
		 */
		function getAll(string $sql, array $param): array;

		/**
		 * Execute a query and return the first row as an ArrayObject
		 * @api getRow
		 * @param string $sql An SQL query statement
		 * @param array<int|string, mixed> $param An array of values to inject in the statement
		 * @return \stdClass|null
		 */
		function getRow(string $sql, array $param): ?\stdClass;

		/**
		 * Execute a query and return the first column of each row
		 * @api getColumn
		 * @param string $sql An SQL query statement
		 * @param array<int|string, mixed> $param An array of values to inject in the statement
		 * @return mixed[]
		 */
		function getColumn(string $sql, array $param): array;

		/**
		 * Execute a query and return the first value of the first row
		 * @api getValue
		 * @param string $sql An SQL query statement
		 * @param array<int|string, mixed> $param An array of values to inject in the statement
		 * @return mixed|null
		 */
		function getValue(string $sql, array $param): mixed;

		/**
		 * Execute a statement and return the number of affected rows
		 * @api execute
		 * @param string $sql An SQL statement
		 * @param array<int|string, mixed> $param An array of values to inject in the statement
		 * @return int
		 */
		function execute(string $sql, array $param): int;

		/**
		 * Return the last error for this database connection
		 * For query-specific errors, use getLastQueryError().
		 * @return array{0: string, 1: int|null, 2: string|null}|null
		 */
		function getLastError(): ?array;

		/**
		 * Returns the error for the last executed query.
		 * @return array{0: string, 1: int|null, 2: string|null}|null
		 */
		function getLastQueryError(): ?array;

		/**
		 * Returns the ID of the last inserted row.
		 * @return string
		 */
		function getLastInsertID(): string;

		/**
		 * @api beginTransaction
		 * Open a database transaction
		 */
		function beginTransaction(): bool;

		/**
		 * @api rollbackTransaction
		 * Rolls back the current transaction
		 */
		function rollbackTransaction(): bool;

		/**
		 * @api commitTransaction
		 * Commits the current transaction
		 */
		function commitTransaction(): bool;
	}