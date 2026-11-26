<?php
	namespace KrameWork\Users\Schema;

	use KrameWork\Database\Schema\ManagedTable;

	require_once(__DIR__ . '/../../Database/Schema/ManagedTable.php');

	/**
	 * Class UserManagedTable
	 * Base class for the tables of the user system.
	 * The tables use portable SQL and unquoted names in the default schema, so they work on MySQL, PostgreSQL, SQLite and SQL Server.
	 *
	 * @package KrameWork\Users\Schema
	 */
	abstract class UserManagedTable extends ManagedTable
	{
		/**
		 * Returns the name of the database schema. The tables use the default schema of the connection.
		 *
		 * @api getSchema
		 * @return string
		 */
		public function getSchema(): string {
			return '';
		}

		/**
		 * Returns the table name. The name is not quoted and has no schema.
		 *
		 * @api getFullName
		 * @return string
		 */
		public function getFullName(): string {
			return $this->getName();
		}
	}
