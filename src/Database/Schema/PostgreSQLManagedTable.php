<?php
	namespace KrameWork\Database\Schema;

	require_once(__DIR__ . '/ManagedTable.php');
	require_once(__DIR__ . '../Driver/Generic.php');

	/**
	 * Class to handle a managed table hosted by a PostgreSQL Server
	 * @author docpify <morten@runsafe.no>
	 * @package KrameWork\Database
	 */
	abstract class PostgreSQLManagedTable extends ManagedTable
	{
		public function getSchema(): string {
			return 'public';
		}
	}
