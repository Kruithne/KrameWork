<?php
	class TestDatabaseConnection extends KW_DatabaseConnection
	{
		public function __construct()
		{
			parent::__construct('sqlite:test_database.sq3', null, null);
		}
	}
?>