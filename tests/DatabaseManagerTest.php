<?php

	use KrameWork\Database\Schema\DatabaseManager;

	require_once(__DIR__ . '/../src/Database/Schema/DatabaseManager.php');

	class DatabaseManagerTest extends \PHPUnit\Framework\TestCase
	{
		/**
		 * Checks that a schema update with no registered tables returns an empty status list without a warning.
		 */
		public function testUpdateSchemaWithoutTables() {
			$this->assertSame([], (new DatabaseManager())->updateSchema());
		}
	}
