<?php
	require_once("/home/travis/build/Kruithne/KrameWork/testing/resources/default_bootstrap.php");

	class SchemaManagerTest extends PHPUnit_Framework_TestCase
	{
		/**
		 * Basic test to see that _metatable is attempted created on update
		 */
		public function testSchemaManager()
		{
			$db = new MockDatabaseConnection('mysql');
			$db->begin();
			$manager = new KW_SchemaManager($db);
			$manager->update();
			$expected = '';
			$this->assertEquals($db->end(), $expected, 'Meta table version does not match expected version number.');
		}
	}
?>
