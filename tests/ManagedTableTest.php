<?php

	use KrameWork\Database\ConnectionString;
	use KrameWork\Database\Database;
	use KrameWork\Database\Schema\SQLiteManagedTable;
	use KrameWork\Database\Schema\SQLiteMetaTable;
	use KrameWork\Database\Schema\TableVersion;

	require_once(__DIR__ . '/../src/Database/Database.php');
	require_once(__DIR__ . '/../src/Database/Schema/SQLiteManagedTable.php');
	require_once(__DIR__ . '/../src/Database/Schema/SQLiteMetaTable.php');
	require_once(__DIR__ . '/../src/Database/Schema/TableVersion.php');

	class ManagedTableTest extends \PHPUnit\Framework\TestCase
	{
		/**
		 * Checks that a failed table creation rolls back the earlier statements and stores no version.
		 */
		public function testFailedCreateRollsBack() {
			$db = new Database(new ConnectionString('sqlite::memory:'), Database::DB_DRIVER_PDO);
			$meta = new SQLiteMetaTable($db);
			$table = new class($db, $meta) extends SQLiteManagedTable {
				public function getName(): string {
					return 'users';
				}

				public function versionLog(): array {
					return [new TableVersion(1, [])];
				}

				public function latestVersion(): array {
					return ['CREATE TABLE "main"."users" ("id" INTEGER)', 'INVALID SQL'];
				}
			};

			$this->assertStringStartsWith('Table creation failed', $table->create());
			$this->assertSame(0, $meta->getVersion('users'));
			$this->assertNull($db->getValue('SELECT name FROM sqlite_master WHERE type = ? AND name = ?', ['table', 'users']));
		}
	}
