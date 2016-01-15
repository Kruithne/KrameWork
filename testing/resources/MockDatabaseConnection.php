<?php
	class MockDatabaseConnection implements IDatabaseConnection
	{
		public function getType()
		{
			return 'fake';
		}

		public function prepare($sql)
		{
			return new MockDatabaseStatement($sql);
		}

		public function execute($sql)
		{
		}

		public function getLastInsertID($table)
		{
			return $this->id;
		}

		/**
		 * Use in tests
		 */
		public function increment()
		{
			$this->id++;
		}

		private $id = 1;
	}
?>
