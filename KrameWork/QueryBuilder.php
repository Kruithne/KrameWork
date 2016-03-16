<?php
	// These interfaces only provide hinting for IDEs when using the query builder features of CRUD.
	// They should not be used in production.

	interface IQueryAnd
	{
		/**
		 * Adds an AND directive to the SQL statement
		 * @param string $column The column name to filter by
		 * @return IQueryColumn
		 */
		public function andColumn($column);
	}
	interface IQueryBetween
	{
		/**
		 * Do a range match on the preceeding column specification
		 * @param mixed $low The lower bound.
		 * @param mixed $high The upper bound.
		 * @return IQueryPredicate
		 */
		public function between($low, $high);
	}
	interface IQueryEquals
	{
		/**
		 * Do an exact match on the preceding column specification
		 * @param string $value The pattern to look for or exclude.
		 * @return IQueryPredicate
		 */
		public function equals($value);
	}
	interface IQueryEqualsCaseInsensitive
	{
		/**
		 * Do a case-insensitive match on the preceding column specification
		 * @param string $value The pattern to look for or exclude.
		 * @return IQueryPredicate
		 */
		public function equalsCaseInsensitive($value);
	}
	interface IQueryGreaterThan
	{
		/**
		 * Check for values above the specified value.
		 * @param string $value The pattern to look for or exclude.
		 * @return IQueryPredicate
		 */
		public function greaterThan($value);
	}
	interface IQueryLessThan
	{
		/**
		 * Check for values below the specified value.
		 * @param string $value The pattern to look for or exclude.
		 * @return IQueryPredicate
		 */
		public function lessThan($value);
	}
	interface IQueryLike
	{
		/**
		 * Do a wildcard match on the preceding column specification
		 * @param string $value The pattern to look for or exclude.
		 * @return IQueryPredicate
		 */
		public function like($value);
	}
	interface IQueryNotLike
	{
		/**
		 * Do a negative wildcard match on the preceding column specification
		 * @param string $value The pattern to look for or exclude.
		 * @return IQueryPredicate
		 */
		public function notLike($value);
	}
	interface IQueryNull
	{
		/**
		 * Require that the preceding column specification is null
		 * @return IQueryPredicate
		 */
		public function isNull();
	}
	interface IQueryNotNull
	{
		/**
		 * Require that the preceding column specification is a non null value
		 * @return IQueryPredicate
		 */
		public function notNull();
	}
	interface IQueryOr
	{
		/**
		 * Adds an OR directive to the SQL statement
		 * @param string $column The column name to filter by
		 * @return IQueryColumn
		 */
		public function orColumn($column);
	}
	interface IQueryTerminus
	{
		/**
		 * Prepare and execute the built query, returning the result set.
		 * @return object[] The data-type as specified by CRUD instance the query was built from.
		 */
		public function execute();
	}
	interface IQueryLimit extends IQueryTerminus
	{
		/**
		 * Skip the first N rows in the dataset
		 * @param int $offset The number of rows to skip
		 * @return IQueryOffset
		 */
		public function offset($offset);
	}
	interface IQueryOffset extends IQueryTerminus
	{
		/**
		 * Only return N rows from the dataset
		 * @param int $count The maximum number of rows to return.
		 * @return IQueryTerminus
		 */
		public function limit($count);
	}
	interface IQueryColumn extends IQueryBetween, IQueryEquals, IQueryGreaterThan, IQueryLessThan, IQueryLike, IQueryNotLike, IQueryNull, IQueryNotNull {}
	interface IQueryPredicate extends IQueryAnd, IQueryOr, IQueryLimit {}
?>
