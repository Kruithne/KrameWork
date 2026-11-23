<?php
	namespace KrameWork\Database\Schema;

	/**
	 * Base class for a database table driver
	 * @author docpify <morten@runsafe.no>
	 * @package KrameWork\Database
	 */
	interface IManagedTable
	{
		/**
		 * @api getSchema
		 * Returns the name of the database schema
		 * @return string The database schema name
		 */
		public function getSchema();

		/**
		 * @api getName
		 * Returns the name of the database table
		 * @return string The database table name
		 */
		public function getName();

		/**
		 * @api getFullName
		 * Returns the fully qualified table name, including schema.
		 * @return string Fully qualified table name
		 */
		public function getFullName();

		/**
		 * @api getRevision
		 * Returns a set of revision definitions
		 * @return TableVersion[]
		 */
		public function versionLog();

		/**
		 * @api getLatestVersion
		 * Returns a set of queries to create the latest revision of the table
		 * @return string[]
		 */
		public function latestVersion();

		/**
		 * @api update
		 * Updates the table in the database
		 * @return string Status
		 */
		public function update();

		/**
		 * @api drop
		 * Delete the table from the database
		 * @return bool Success
		 */
		public function drop();

		/**
		 * @api create
		 * Create the table in the database
		 * @return bool Success
		 */
		public function create();
	}
