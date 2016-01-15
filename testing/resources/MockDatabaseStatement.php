<?php
	class MockDatabaseStatement implements IDatabaseStatement
	{
		public function __construct($sql)
		{
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
		}

		public function getRows()
		{
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

		private $sql;
		private $data = array();
		private $type = array();
	}
?>
