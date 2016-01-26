<?php
	interface ISchemaManager
	{
		/**
		 * Fetch a table by name
		 *
		 * @param string $name Name of table to return the specification of.
		 */
		public function __get($name);

		/**
		 * Add a new table to be managed.
		 *
		 * @param IRepository $spec A table specification.
		 */
		public function addTable(IRepository $spec);

		/**
		 * Called to execute schema management once all tables have been defined.
		 */
		public function update();

		/**
		 * Auto-update a table according to the specification.
		 *
		 * @param IRepository $spec The table specification to act upon.
		 */
		public function upgrade(IRepository $spec);

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
