<?php
	require_once("/home/travis/build/Kruithne/KrameWork/testing/resources/default_bootstrap.php");

	class MySQLCRUDTest extends PHPUnit_Framework_TestCase
	{
		/**
		 * Test that generated insert query is correct and gets executed
		 */
		public function testAutoIDCreateOperation()
		{
			$db = MockDatabaseConnection::Get('mysql');
			$manager = new MockSchemaManager($db);
			$crud = new MockCRUD($manager);
			$db->begin();
			$result = $crud->create((object)array('value' => '{test}'));
			$sql = $db->end();
			$this->assertEquals('INSERT INTO __mock__ (value) VALUES (:value);SELECT LAST_INSERT_ID() AS id', $sql, 'Insert query mismatch');
		}

		/**
		 * Test that generated select query is correct and gets executed
		 */
		public function testAutoIDReadAllOperation()
		{
			$db = MockDatabaseConnection::Get('mysql');
			$manager = new MockSchemaManager($db);
			$crud = new MockCRUD($manager);
			$db->begin();
			$result = $crud->read();
			$sql = $db->end();
			$this->assertEquals('SELECT * FROM __mock__', $sql, 'Select all query mismatch');
		}

		/**
		 * Test that generated select query is correct and gets executed
		 */
		public function testAutoIDReadOneOperation()
		{
			$db = MockDatabaseConnection::Get('mysql');
			$manager = new MockSchemaManager($db);
			$crud = new MockCRUD($manager);
			$db->begin();
			$result = $crud->read(1);
			$sql = $db->end();
			$this->assertEquals('SELECT * FROM __mock__ WHERE id = :id', $sql, 'Select one query mismatch');
		}

		/**
		 * Test that generated update query is correct and gets executed
		 */
		public function testAutoIDUpdateOperation()
		{
			$db = MockDatabaseConnection::Get('mysql');
			$manager = new MockSchemaManager($db);
			$crud = new MockCRUD($manager);
			$db->begin();
			$result = $crud->update((object)array('id' => 1, 'value' => 'mock'));
			$sql = $db->end();
			$this->assertEquals('UPDATE __mock__ SET value = :value WHERE id = :id', $sql, 'Update query mismatch');
		}

		/**
		 * Test that generated delete query is correct and gets executed
		 */
		public function testAutoIDDeleteOperation()
		{
			$db = MockDatabaseConnection::Get('mysql');
			$manager = new MockSchemaManager($db);
			$crud = new MockCRUD($manager);
			$db->begin();
			$result = $crud->delete((object)array('id' => 1));
			$sql = $db->end();
			$this->assertEquals('DELETE FROM __mock__ WHERE id = :id', $sql, 'Delete query mismatch');
		}

		/**
		 * Test that generated insert query is correct and gets executed
		 */
		public function testCompositeCreateOperation()
		{
			$db = MockDatabaseConnection::Get('mysql');
			$manager = new MockSchemaManager($db);
			$crud = new MockCRUD($manager, array('a','b'), false);
			$db->begin();
			$result = $crud->create((object)array('a' => 1, 'b' => 2, 'value' => '{test}'));
			$sql = $db->end();
			$this->assertEquals('INSERT INTO __mock__ (a,b,value) VALUES (:a, :b, :value);SELECT * FROM __mock__ WHERE a = :a AND b = :b', $sql, 'Insert query mismatch');
		}

		/**
		 * Test that generated select query is correct and gets executed
		 */
		public function testCompositeReadAllOperation()
		{
			$db = MockDatabaseConnection::Get('mysql');
			$manager = new MockSchemaManager($db);
			$crud = new MockCRUD($manager, array('a','b'), false);
			$db->begin();
			$result = $crud->read();
			$sql = $db->end();
			$this->assertEquals('SELECT * FROM __mock__', $sql, 'Select all query mismatch');
		}

		/**
		 * Test that generated select query is correct and gets executed
		 */
		public function testCompositeReadSubsetOperation()
		{
			$db = MockDatabaseConnection::Get('mysql');
			$manager = new MockSchemaManager($db);
			$crud = new MockCRUD($manager, array('a','b'), false);
			$db->begin();
			$result = $crud->read(array('a' => 1, 'b' => '*'));
			$sql = $db->end();
			$this->assertEquals('SELECT * FROM __mock__ WHERE (:a_null = 1 OR a = :a) AND (:b_null = 1 OR b = :b)', $sql, 'Select all query mismatch');
		}

		/**
		 * Test that generated select query is correct and gets executed
		 */
		public function testCompositeReadOneOperation()
		{
			$db = MockDatabaseConnection::Get('mysql');
			$manager = new MockSchemaManager($db);
			$crud = new MockCRUD($manager, array('a','b'), false);
			$db->begin();
			$result = $crud->read(array('a' => 1, 'b' => 2));
			$sql = $db->end();
			$this->assertEquals('SELECT * FROM __mock__ WHERE a = :a AND b = :b', $sql, 'Select one query mismatch');
		}

		/**
		 * Test that generated update query is correct and gets executed
		 */
		public function testCompositeUpdateOperation()
		{
			$db = MockDatabaseConnection::Get('mysql');
			$manager = new MockSchemaManager($db);
			$crud = new MockCRUD($manager, array('a','b'), false);
			$db->begin();
			$result = $crud->update((object)array('a' => 1, 'b' => 2, 'value' => 'mock'));
			$sql = $db->end();
			$this->assertEquals('UPDATE __mock__ SET value = :value WHERE a = :a AND b = :b', $sql, 'Update query mismatch');
		}

		/**
		 * Test that generated delete query is correct and gets executed
		 */
		public function testCompositeDeleteOperation()
		{
			$db = MockDatabaseConnection::Get('mysql');
			$manager = new MockSchemaManager($db);
			$crud = new MockCRUD($manager, array('a','b'), false);
			$db->begin();
			$result = $crud->delete((object)array('a' => 1, 'b' => 2));
			$sql = $db->end();
			$this->assertEquals('DELETE FROM __mock__ WHERE a = :a AND b = :b', $sql, 'Delete query mismatch');
		}

		/**
		 * Test that generated insert query is correct and gets executed
		 */
		public function testCreateOperation()
		{
			$db = MockDatabaseConnection::Get('mysql');
			$manager = new MockSchemaManager($db);
			$crud = new MockCRUD($manager, null, false);
			$db->begin();
			$result = $crud->create((object)array('value' => '{test}'));
			$sql = $db->end();
			$this->assertEquals('INSERT INTO __mock__ (value) VALUES (:value);SELECT * FROM __mock__', $sql, 'Insert query mismatch');
		}

		/**
		 * Test that generated select query is correct and gets executed
		 */
		public function testReadAllOperation()
		{
			$db = MockDatabaseConnection::Get('mysql');
			$manager = new MockSchemaManager($db);
			$crud = new MockCRUD($manager, null, false);
			$db->begin();
			$result = $crud->read();
			$sql = $db->end();
			$this->assertEquals('SELECT * FROM __mock__', $sql, 'Select all query mismatch');
		}

		/**
		 * Test that generated delete query is correct and gets executed
		 */
		public function testDeleteOperation()
		{
			$db = MockDatabaseConnection::Get('mysql');
			$manager = new MockSchemaManager($db);
			$crud = new MockCRUD($manager, null, false);
			$db->begin();
			$result = $crud->delete((object)array('value' => 'mock'));
			$sql = $db->end();
			$this->assertEquals('DELETE FROM __mock__', $sql, 'Delete query mismatch');
		}
	}
?>
