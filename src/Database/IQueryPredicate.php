<?php
	namespace KrameWork\Database;

	/**
	 * Base class for a database table driver
	 * @author docpify <morten@runsafe.no>
	 * @package KrameWork\Database
	 */
	interface IQueryPredicate
	{
		/**
		 * Apply an offset to the result set
		 * @api offsset
		 * @param int $offset
		 * @return IQueryPredicate
		 */
		public function offset(int $offset): IQueryPredicate;

		/**
		 * Limit the number of rows matched
		 * @api limit
		 * @param int $limit
		 * @return IQueryPredicate
		 */
		public function limit(int $limit): IQueryPredicate;

		/**
		 * Sort the result set by a column in ascending order
		 * @api orderBy
		 * @param string $column Column name
		 * @return IQueryPredicate
		 */
		public function orderBy(string $column): IQueryPredicate;

		/**
		 * Sort the result set by a column in descending order
		 * @api orderByDesc
		 * @param string $column Column name
		 * @return IQueryPredicate
		 */
		public function orderByDesc(string $column): IQueryPredicate;

		/**
		 * Execute a select statement and return the data set
		 * @api select
		 * @return array array of arrays of key/value pairs
		 */
		public function select(): array;

		/**
		 * @api update
		 * @param array $updateSpec Array of key/value pairs to persist to the database
		 * @return bool Success
		 */
		public function update(array $updateSpec): bool;

		/**
		 * Delete the matched rows
		 * @api delete
		 * @return bool Success
		 */
		public function delete(): bool;
	}