<?php
	require_once("/home/travis/build/Kruithne/KrameWork/testing/resources/default_bootstrap.php");

	class SchemaManagerTest extends PHPUnit_Framework_TestCase
	{
		/**
		 * Basic test to see that _metatable is attempted created on update
		 */
		public function testMySQLSchemaManager()
		{
			$db = new MockDatabaseConnection('mysql');
			$db->begin();
			$kernel = new MockManyInjector([]);
			$manager = new KW_SchemaManager($db, $kernel);
			$manager->update();
			$expected = 'SHOW TABLES LIKE \'_metatable\';
INSERT INTO `_metatable` (`table`,`version`) VALUES (:table,:version)
	ON DUPLICATE KEY UPDATE `version`=VALUES(`version`)
';
			$this->assertEquals($db->end(), $expected, 'Meta table version does not match expected version number.');
		}

		public function testPostgreSchemaManager()
		{
			$db = new MockDatabaseConnection('pgsql');
			$db->begin();
			$kernel = new MockManyInjector([]);
			$manager = new KW_SchemaManager($db, $kernel);
			$manager->update();
			$expected = '
SELECT c.relname 
FROM   pg_catalog.pg_class c
JOIN   pg_catalog.pg_namespace n ON n.oid = c.relnamespace
WHERE  n.nspname = \'public\'
AND    c.relname = \'_metatable\'
;
INSERT INTO _metatable ("table","version")
SELECT _table, 0
FROM (
	SELECT CAST(:table AS VARCHAR) AS _table
) AS i
LEFT JOIN _metatable ON (_metatable."table" = i._table)
WHERE _metatable."table" IS NULL
;UPDATE _metatable SET "version"=:version WHERE "table"=:table';
			$this->assertEquals($db->end(), $expected, 'Meta table version does not match expected version number.');
		}
	}
