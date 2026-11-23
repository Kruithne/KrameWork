<?php
	namespace KrameWork\Database\Schema;

	require_once(__DIR__ . '/ManagedTable.php');
	require_once(__DIR__ . '/../Driver/Generic.php');

	/**
	 * Class to handle a managed table hosted by a MySQL Server
	 * @author docpify <morten@runsafe.no>
	 * @package KrameWork\Database
	 */
	abstract class MySQLManagedTable extends ManagedTable
	{
		/**
		 * @api GetSchema
		 * Returns the name of the database schema
		 * @return string The database schema name
		 */
		public function getSchema(): string {
			return '';
		}

		/**
		 * @api GetFullName
		 * Returns the fully qualified table name, including schema.
		 * @return string Fully qualified table name
		 */
		public function getFullName(): string {
			return $this->quoteIdentifier($this->getName());
		}

		public function quoteIdentifier(string $identifier): string
		{
			return "`{$identifier}`";
		}
	}
