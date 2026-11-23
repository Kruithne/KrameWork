<?php

	use KrameWork\Database\ConnectionString;
	use KrameWork\Database\Database;

	require_once(__DIR__ . '/../src/Database/Database.php');

	class PDODriverTest extends \PHPUnit\Framework\TestCase
	{
		private Database $db;

		protected function setUp(): void {
			$this->db = new Database(new ConnectionString('sqlite::memory:'), Database::DB_DRIVER_PDO);
			$this->db->execute('CREATE TABLE test (id INTEGER PRIMARY KEY, value)');
			$this->db->execute('INSERT INTO test (id, value) VALUES (1, 1), (2, 0), (3, \'\'), (4, NULL), (5, 2)');
		}

		public function testGetAll() {
			$rows = $this->db->getAll('SELECT id, value FROM test WHERE id < 3 ORDER BY id');
			$this->assertEquals([(object) ['id' => 1, 'value' => 1], (object) ['id' => 2, 'value' => 0]], $rows);
		}

		public function testGetRow() {
			$this->assertEquals((object) ['id' => 2, 'value' => 0], $this->db->getRow('SELECT id, value FROM test WHERE id = ?', [2]));
			$this->assertNull($this->db->getRow('SELECT id FROM test WHERE id = ?', [99]));
		}

		public function testGetColumn() {
			$this->assertSame([1, 0, '', null, 2], $this->db->getColumn('SELECT value FROM test ORDER BY id'));
		}

		public function testGetValue() {
			$this->assertSame(0, $this->db->getValue('SELECT value FROM test WHERE id = :id', ['id' => 2]));
			$this->assertNull($this->db->getValue('SELECT value FROM test WHERE id = :id', ['id' => 99]));
		}

		public function testExecute() {
			$this->assertSame(2, $this->db->execute('UPDATE test SET value = ? WHERE id > ?', [9, 3]));
		}

		public function testGetLastInsertID() {
			$this->db->execute('INSERT INTO test (value) VALUES (?)', [3]);
			$this->assertSame('6', $this->db->getLastInsertID());
		}

		public function testMixedParameters() {
			$this->expectExceptionMessage('Mixed parameter type in parameter list');
			$this->db->getAll('SELECT * FROM test WHERE id = ?', [0 => 1, 'id' => 2]);
		}

		public function testRollbackTransaction() {
			$this->db->beginTransaction();
			$this->db->execute('DELETE FROM test');
			$this->db->rollbackTransaction();
			$this->assertSame(5, $this->db->getValue('SELECT COUNT(*) FROM test'));
		}

		/**
		 * Checks that a rollback with no active transaction returns false and does not throw.
		 */
		public function testRollbackWithoutTransaction() {
			$this->assertFalse($this->db->rollbackTransaction());
		}
	}
