<?php
	class MockDatabaseStatement implements IDatabaseStatement
	{
		public function __construct($sql, $db)
		{
			$this->sql = $sql;
			$this->db = $db;
			error_log('Prepare statement: '.$sql);
		}

		public function getQueryString()
		{
			return $this->sql;
		}

		public function setValue($key, $value)
		{
			$this->data[$key] = $value;
		}

		public function setType($key, $type)
		{
			$this->type[$key] = $type;
		}

		public function __set($key, $value)
		{
			$this->data[$key] = $value;
		}

		public function copyValuesFromRow($row, $prependChar = ':')
		{
			return null;
		}

		public function execute()
		{
			$this->executed = true;
			error_log('Executing "'.$this->sql.'"');
			$this->db->run($this->sql);
			return $this;
		}

		public function getRows()
		{
			if (!$this->executed)
				$this->execute();
			return array();
		}

		public function getFirstRow()
		{
			return false;
		}

		public function getRowCount()
		{
			return 0;
		}

		public function getErrorCode()
		{
			return '';
		}

		private $executed;
		private $sql;
		private $db;
		private $data = array();
		private $type = array();
	}
?>
