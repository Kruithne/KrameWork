<?php
	class KW_DeferredStatement implements IDatabaseStatement
	{
		public function __construct($sql, PDO $db)
		{
			$this->sql = $sql;
			$this->db = $db;
		}

		public function getQueryString()
		{
			if(!$this->statement)
				$this->prepare();
			return $this->statement->getQueryString();
		}

		public function setValue($key, $value)
		{
			if(!$this->statement)
				$this->prepare();
			return $this->statement->setValue($key, $value);
		}

		public function setType($key, $type)
		{
			if(!$this->statement)
				$this->prepare();
			return $this->statement->setType($key, $type);
		}

		public function __set($key, $value)
		{
			if(!$this->statement)
				$this->prepare();
			return $this->statement->__set($key, $value);
		}

		public function copyValuesFromRow(IDataContainer $row, $prependChar = ':')
		{
			if(!$this->statement)
				$this->prepare();
			return $this->statement->copyValuesFromRow($row, $prependChar);
		}

		public function execute()
		{
			if(!$this->statement)
				$this->prepare();
			return $this->statement->execute();
		}

		public function getRows()
		{
			if(!$this->statement)
				$this->prepare();
			return $this->statement->getRows();
		}

		public function getFirstRow()
		{
			if(!$this->statement)
				$this->prepare();
			return $this->statement->getFirstRow();
		}

		public function getRowCount()
		{
			if(!$this->statement)
				$this->prepare();
			return $this->statement->getRowCount();
		}

		public function getErrorCode()
		{
			if(!$this->statement)
				$this->prepare();
			return $this->statement->getErrorCode();
		}

		private function prepare()
		{
			$this->statement = new KW_DatabaseStatement($this->sql, $this->db);
		}

		private $sql;
		private $db;
		private $statement;
	}
?>
