<?php
	require_once("/home/travis/build/Kruithne/KrameWork/testing/resources/default_bootstrap.php");

	class CRUDTest extends PHPUnit_Framework_TestCase
	{
		/**
		 * 
		 */
		public function testSchemaManager()
		{
			$manager = new MockSchemaManager(MockDatabaseConnection::Get());
			$crud = new MockCRUD($manager);
			$result = $crud->create((object)array('value' => '{test}'));
			error_log($result);
			//$this->assertEquals(1, $version, 'Meta table version does not match expected version number.');
		}

		private $manager;
	}
?>
