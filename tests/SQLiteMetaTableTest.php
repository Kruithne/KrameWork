<?php

	use KrameWork\Database\ConnectionString;
	use KrameWork\Database\Database;
	use KrameWork\Database\Schema\SQLiteMetaTable;

	require_once(__DIR__ . '/../src/Database/Database.php');
	require_once(__DIR__ . '/../src/Database/Schema/SQLiteMetaTable.php');

	class SQLiteMetaTableTest extends \PHPUnit\Framework\TestCase
	{
		private Database $db;

		protected function setUp(): void {
			$this->db = new Database(new ConnectionString('sqlite::memory:'), Database::DB_DRIVER_PDO);
		}

		/**
		 * Checks that an unknown table has version 0, so it gets created.
		 */
		public function testUnknownTableHasVersionZero() {
			$meta = new SQLiteMetaTable($this->db);
			$this->assertSame(0, $meta->getVersion('users'));
		}

		/**
		 * Checks that a new version replaces the stored version.
		 */
		public function testSetVersionReplacesStoredVersion() {
			$meta = new SQLiteMetaTable($this->db);
			$meta->setVersion('users', 2);
			$meta->setVersion('users', 5);
			$this->assertSame(5, $meta->getVersion('users'));
		}

		/**
		 * Checks that a new instance on an existing database keeps the stored versions.
		 */
		public function testExistingVersionsKeptOnReconnect() {
			(new SQLiteMetaTable($this->db))->setVersion('users', 3);
			$meta = new SQLiteMetaTable($this->db);
			$this->assertSame(3, $meta->getVersion('users'));
		}
	}
