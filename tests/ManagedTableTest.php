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
		 * Checks that a new table is built by applying every revision in the log.
		 */
		public function testCreateAppliesAllRevisions() {
			$db = new Database(new ConnectionString('sqlite::memory:'), Database::DB_DRIVER_PDO);
			$meta = new SQLiteMetaTable($db);
			$table = $this->createTable($db, $meta, [
				new TableVersion(1, ['CREATE TABLE "main"."users" ("id" INTEGER)']),
				new TableVersion(2, ['ALTER TABLE "main"."users" ADD "name" TEXT'])
			]);

			$this->assertSame('Table created with revision 2', $table->update());
			$this->assertSame(1, $db->execute('INSERT INTO "main"."users" ("id", "name") VALUES (?, ?)', [1, 'test']));
		}

		/**
		 * Checks that a failed table creation rolls back the earlier statements and stores no version.
		 */
		public function testFailedCreateRollsBack() {
			$db = new Database(new ConnectionString('sqlite::memory:'), Database::DB_DRIVER_PDO);
			$meta = new SQLiteMetaTable($db);
			$table = $this->createTable($db, $meta, [
				new TableVersion(1, ['CREATE TABLE "main"."users" ("id" INTEGER)', 'INVALID SQL'])
			]);

			$this->assertStringStartsWith('Table creation failed', $table->create());
			$this->assertSame(0, $meta->getVersion('users'));
			$this->assertNull($db->getValue('SELECT name FROM sqlite_master WHERE type = ? AND name = ?', ['table', 'users']));
		}

		/**
		 * Checks that table creation with a gap in the revision log is rejected and creates nothing.
		 */
		public function testCreateRejectsInvalidLog() {
			$db = new Database(new ConnectionString('sqlite::memory:'), Database::DB_DRIVER_PDO);
			$meta = new SQLiteMetaTable($db);
			$table = $this->createTable($db, $meta, [new TableVersion(1, []), new TableVersion(3, [])]);

			$this->assertStringStartsWith('Revision log is invalid', $table->create());
			$this->assertSame(0, $meta->getVersion('users'));
			$this->assertNull($db->getValue('SELECT name FROM sqlite_master WHERE type = ? AND name = ?', ['table', 'users']));
		}

		/**
		 * Checks that an update with a gap in the revision log is rejected and applies no revision.
		 */
		public function testUpdateRejectsInvalidLog() {
			$db = new Database(new ConnectionString('sqlite::memory:'), Database::DB_DRIVER_PDO);
			$meta = new SQLiteMetaTable($db);
			$db->execute('CREATE TABLE "main"."users" ("id" INTEGER)', []);
			$meta->setVersion('users', 1);
			$table = $this->createTable($db, $meta, [
				new TableVersion(1, []),
				new TableVersion(3, ['ALTER TABLE "main"."users" ADD "name" TEXT'])
			]);

			$this->assertStringStartsWith('Revision log is invalid', $table->update());
			$this->assertSame(1, $meta->getVersion('users'));
		}

		/**
		 * Creates a users table with the given revision log.
		 * @param Database $db Database connection
		 * @param SQLiteMetaTable $meta Meta table
		 * @param TableVersion[] $log Revision log
		 * @return SQLiteManagedTable Managed table
		 */
		private function createTable(Database $db, SQLiteMetaTable $meta, array $log): SQLiteManagedTable {
			return new class($db, $meta, $log) extends SQLiteManagedTable {
				public function __construct(Database $db, SQLiteMetaTable $meta, private array $log) {
					parent::__construct($db, $meta);
				}

				public function getName(): string {
					return 'users';
				}

				public function versionLog(): array {
					return $this->log;
				}
			};
		}
	}
