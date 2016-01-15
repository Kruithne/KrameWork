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
	}
?>
