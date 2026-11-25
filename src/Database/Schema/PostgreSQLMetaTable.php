<?php
	namespace KrameWork\Database\Schema;

	use \KrameWork\Database\Driver\Generic;

	require_once(__DIR__ . '/IMetaTable.php');
	require_once(__DIR__ . '/../Driver/Generic.php');

	/**
	 * Class to store managed table versions in a PostgreSQL database
	 * @package KrameWork\Database
	 */
	class PostgreSQLMetaTable implements IMetaTable
	{
		/**
		 * Creates the meta table if it does not exist
		 * @api __construct
		 * @param Generic $db Database connection
		 */
		public function __construct(private Generic $db) {
			$installed = $this->db->getValue(
				'SELECT table_name FROM information_schema.tables WHERE table_schema = ? AND table_name = ?',
				[$this->getSchema(), $this->getName()]
			);
			if (!$installed) {
				$this->db->execute("CREATE TABLE {$this->getFullName()} (\"table\" VARCHAR(50) NOT NULL PRIMARY KEY, \"version\" INTEGER NOT NULL)", []);
				$this->setVersion($this->getName(), 1);
			}
		}

		/**
		 * Returns the stored version of a table
		 * @api getVersion
		 * @param string $table Table name
		 * @return int Stored version, or 0 if the table is unknown
		 */
		public function getVersion(string $table): int {
			$version = $this->db->getValue("SELECT \"version\" FROM {$this->getFullName()} WHERE \"table\" = ?", [$table]);
			return \is_numeric($version) ? (int) $version : 0;
		}

		/**
		 * Stores the version of a table
		 * @api setVersion
		 * @param string $table Table name
		 * @param int $version Version to store
		 */
		public function setVersion(string $table, int $version): void {
			$this->db->execute(
				"INSERT INTO {$this->getFullName()} (\"table\", \"version\") VALUES (?, ?) ON CONFLICT (\"table\") DO UPDATE SET \"version\" = EXCLUDED.\"version\"",
				[$table, $version]
			);
		}

		/**
		 * Returns the name of the database schema
		 * @api getSchema
		 * @return string The database schema name
		 */
		public function getSchema(): string {
			return 'public';
		}

		/**
		 * Returns the name of the meta table
		 * @api getName
		 * @return string The meta table name
		 */
		public function getName(): string {
			return '_tables';
		}

		/**
		 * Returns the fully qualified meta table name, including schema
		 * @api getFullName
		 * @return string Fully qualified meta table name
		 */
		public function getFullName(): string {
			return "\"{$this->getSchema()}\".\"{$this->getName()}\"";
		}
	}
