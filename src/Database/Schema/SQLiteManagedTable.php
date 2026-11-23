<?php
	namespace KrameWork\Database\Schema;

	require_once(__DIR__ . '/ManagedTable.php');
	require_once(__DIR__ . '/../Driver/Generic.php');

	/**
	 * Class to handle a managed table hosted by an SQLite database
	 * @author docpify <morten@runsafe.no>
	 * @package KrameWork\Database
	 */
	abstract class SQLiteManagedTable extends ManagedTable
	{
		public function getSchema(): string {
			return 'main';
		}
	}
