<?php
	require_once("/home/travis/build/Kruithne/KrameWork/testing/resources/default_bootstrap.php");

	class CRUDTest extends PHPUnit_Framework_TestCase
	{
		/**
		 * Test that generated insert query is correct and gets executed
		 */
		public function testCreateOperation()
		{
			$db = MockDatabaseConnection::Get();
			$manager = new MockSchemaManager($db);
			$crud = new MockCRUD($manager);
			$db->begin();
			$result = $crud->create((object)array('value' => '{test}'));
			$sql = $db->end();
			$this->assertEquals('INSERT INTO __mock__ (value) VALUES (:value)', $sql, 'Insert query mismatch');
		}

		/**
		 * Test that generated select query is correct and gets executed
		 */
		public function testReadAllOperation()
		{
			$db = MockDatabaseConnection::Get();
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
		public function testReadOneOperation()
		{
			$db = MockDatabaseConnection::Get();
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
		public function testUpdateOperation()
		{
			$db = MockDatabaseConnection::Get();
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
		public function testDeleteOperation()
		{
			$db = MockDatabaseConnection::Get();
			$manager = new MockSchemaManager($db);
			$crud = new MockCRUD($manager);
			$db->begin();
			$result = $crud->delete((object)array('id' => 1));
			$sql = $db->end();
			$this->assertEquals('DELETE FROM __mock__ WHERE id = :id', $sql, 'Delete query mismatch');
		}
	}
?>
