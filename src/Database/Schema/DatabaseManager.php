<?php
	namespace KrameWork\Database\Schema;

	require_once(__DIR__ . '/IDatabaseManager.php');

	class DatabaseManager implements IDatabaseManager
	{
		/**
		 * Add a managed table to the database
		 * @api __set
		 * @param string $name
		 * @param IManagedTable $table
		 */
		public function __set(string $name, IManagedTable $table): void {
			$this->tables[$name] = $table;
		}

		/**
		 * @api __get
		 * @param string $name A table name
		 * @return IManagedTable|null A table driver
		 */
		public function __get(string $name): mixed {
			return isset($this->tables[$name]) ? $this->tables[$name] : null;
		}

		/**
		 * @api updateSchema
		 * Update the database schema according to managed tables
		 * @return array<string, string> Managed table names as keys with status for each as values
		 */
		public function updateSchema(): array {
			$state = [];
			foreach ($this->tables as $name => $table)
				$state[$name] = $table->update();

			return $state;
		}

		/**
		 * @var IManagedTable[]
		 */
		private array $tables = [];
	}
