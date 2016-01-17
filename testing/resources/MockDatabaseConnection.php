<?php
	class MockDatabaseConnection implements IDatabaseConnection
	{
		public static function Get($type)
		{
		 	if(!isset(self::$instance[$type]))
		 		self::$instance[$type] = new MockDatabaseConnection($type);
			return self::$instance[$type];
		}

		public function __construct($type)
		{
			$this->type = $type;
		}

		public function getType()
		{
			return $this->type;
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

		private $type;
		private $log = array();
		private $id = 1;
		private static $instance = array();
	}
?>
