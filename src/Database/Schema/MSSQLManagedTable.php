<?php
	namespace KrameWork\Database\Schema;

	require_once(__DIR__ . '/ManagedTable.php');
	require_once(__DIR__ . '/../Driver/Generic.php');

	/**
	 * Class to handle a managed table hosted by a Microsoft SQL Server
	 * @author docpify <morten@runsafe.no>
	 * @package KrameWork\Database
	 */
	abstract class MSSQLManagedTable extends ManagedTable
	{
		/**
		 * @api GetSchema
		 * Returns the name of the database schema
		 * @return string The database schema name
		 */
		public function getSchema() {
			return 'dbo';
		}

		public function quoteIdentifier($identifier)
		{
			return "[{$identifier}]";
		}
	}
