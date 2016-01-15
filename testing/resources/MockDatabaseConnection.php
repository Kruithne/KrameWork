<?php
	class MockDatabaseConnection implements IDatabaseConnection
	{
		public static function Get()
		{
		 	if(self::$instance == null)
		 		self::$instance = new MockDatabaseConnection();
			return self::$instance;
		}

		public function getType()
		{
			return 'fake';
		}

		public function prepare($sql)
		{
			return new MockDatabaseStatement($sql, $this);
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

		public function begin()
		{
			$this->log = array();
		}

		public function run($message)
		{
			$this->log[] = $message;
		}

		public function end()
		{
			return join(';', $this->log);
		}

		private $log = array();
		private $id = 1;
		private static $instance = null;
	}
?>
