<?php
	class KW_DatabaseConnection
	{
		public function __construct($dsn, $username, $password)
		{
			$this->connection = new PDO($dsn, $username, $password);
		}

		public function query($sql)
		{
			return $this->connection->query($sql);
		}

		public function prepare($sql)
		{
			return $this->connection->prepare($sql);
		}

		public function getLastInsertID($table)
		{
			return $this->connection->lastInsertId($table);
		}

		private $connection;
	}
?>