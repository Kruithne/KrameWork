<?php
	require_once("/home/travis/build/Kruithne/KrameWork/testing/resources/default_bootstrap.php");

	class SchemaManagerTest extends PHPUnit_Framework_TestCase
	{
		/**
		 * Basic test to see that _metatable is attempted created on update
		 */
		public function testSchemaManager()
		{
			$db = new MockDatabaseConncetion();
			$manager = new KW_SchemaManager($db);
			$manager->update();
			$version = $manager->getCurrentVersion('_metatable');
			$this->assertEquals(1, $version, 'Meta table version does not match expected version number.');
		}
	}
?>
