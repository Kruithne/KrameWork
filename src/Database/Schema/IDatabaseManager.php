<?php
	namespace KrameWork\Database\Schema;

	/**
	 * Base class for a database connection driver
	 * @author docpify <morten@runsafe.no>
	 * @package KrameWork\Database
	 */
	interface IDatabaseManager
	{
		/**
		 * Add a managed table to the database
		 * @api __set
		 * @param string $name
		 * @param IManagedTable $table
		 */
		public function __set(string $name, IManagedTable $table);

		/**
		 * @api __get
		 * @param string $name A table name
		 * @return IDatabaseTable|IManagedTable A table driver
		 */
		public function __get(string $name);

		/**
		 * @api updateSchema
		 * Update the database schema according to managed tables
		 * @return array Managed table names as keys with status for each as values
		 */
		public function updateSchema(): array;
	}
