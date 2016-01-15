<?php
	require_once("/home/travis/build/Kruithne/KrameWork/testing/resources/default_bootstrap.php");

	class CRUDTest extends PHPUnit_Framework_TestCase
	{
		/**
		 * 
		 */
		public function testSchemaManager()
		{
			$db = MockDatabaseConnection::Get();
			$manager = new MockSchemaManager($db);
			$crud = new MockCRUD($manager);
			$db->begin();
			$result = $crud->create((object)array('value' => '{test}'));
			error_log($db->end());
			//$this->assertEquals(1, $version, 'Meta table version does not match expected version number.');
		}

		private $manager;
	}
?>
