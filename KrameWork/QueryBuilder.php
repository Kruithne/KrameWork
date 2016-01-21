<?php
	interface IQueryAnd
	{
		/**
		 * Adds an AND directive to the SQL statement
		 * @param string $column The column name to filter by
		 * @return IQueryColumn
		 */
		public function and($column);
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
		 * Do an exact match on the preceeding column specification
		 * @param string $value The pattern to look for or exclude.
		 * @return IQueryPredicate
		 */
		public function equals($value);
	}
	interface IQueryGreaterThan
	{
		/**
		 * Check for values above the specified value.
		 * @param string $value The pattern to look for or exclude.
		 * @return IQueryPredicate
		 */
		public function greaterThan($value)l
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
		 * Do a wildcard match on the preceeding column specification
		 * @param string $value The pattern to look for or exclude.
		 * @return IQueryPredicate
		 */
		public function like($value);
	}
	interface IQueryNot
	{
		/**
		 * Inverts the following predicate
		 * @return IQueryColumnInverted
		 */
		public function not();
	}
	interface IQueryOr
	{
		/**
		 * Adds an OR directive to the SQL statement
		 * @param string $column The column name to filter by
		 * @return IQueryColumn
		 */
		public function or($column);
	}
	interface IQueryColumnInverted extends IQueryBetween, IQueryEquals, IQueryGraterThan, IQueryLessThan, IQueryLike {}
	interface IQueryColumn extends IQueryColumnInverted, IQueryNot {}
	interface IQueryPredicate extends IQueryAnd, IQueryOr {}
?>
