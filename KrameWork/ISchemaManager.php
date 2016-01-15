<?php
	interface ISchemaManager
	{
		/**
		 * Add a new table to be managed.
		 *
		 * @param ISchemaTable $spec A table specification.
		 */
		public function addTable($spec);

		/**
		 * Fetch a table by name
		 *
		 * @param string $name Name of table to return the specification of.
		 */
		public function __get($name);

		/**
		 * Called to execute schema management once all tables have been defined.
		 */
		public function update();

		/**
		 * Auto-update a table according to the specification.
		 *
		 * @param ISchemaTable $spec The table specification to act upon.
		 */
		public function upgrade($spec);

		/**
		 * Read the current table version.
		 *
		 * @param string $table Name of table whose version is wanted.
		 */
		public function getCurrentVersion($table);

		/**
		 * Load current version information from the database.
		 */
		public function loadVersionTable();
	}
?>
