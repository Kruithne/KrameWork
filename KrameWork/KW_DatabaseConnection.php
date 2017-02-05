<?php
	class KW_DatabaseConnection implements IDatabaseConnection
	{
		/**
		 * Constructs a database connection wrapper.
		 *
		 * @param string $dsn DB DSN (PDO format)
		 * @param string|null $username Username to connect with. Set to null if N/A.
		 * @param string|null $password Password to connect with. Set to null if N/A.
		 */
		public function __construct($dsn, $username, $password)
		{
			$this->connection = new PDO($dsn, $username, $password);
			$this->connection->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
		}

		/**
		 * Return the type of DBMS
		 */
		public function getType()
		{
			return $this->connection->getAttribute(PDO::ATTR_DRIVER_NAME);
		}

		/**
		 * Returns a database statement.
		 *
		 * @param string $sql An SQL query for this statement.
		 * @return IDatabaseStatement A database statement.
		 */
		public function prepare($sql)
		{
			return new KW_DeferredStatement($sql, $this->connection);
		}

		/**
		 * Execute an SQL query.
		 *
		 * @param string $sql An SQL query string.
		 * @return int Amount of rows effected by the execution of this query.
		 */
		public function execute($sql)
		{
			return $this->connection->exec($sql);
		}

		/**
		 * @param string $table The name of the table to check.
		 * @return string ID of the last inserted row.
		 */
		public function getLastInsertID($table)
		{
			return $this->connection->lastInsertId($table);
		}

		/**
		 * @var bool $trace Enable database performance tracing
		 */
		public static $trace = false;

		/**
		 * @var array $traceLog When tracing is enabled, information on all queries that were executed.
		 */
		public static $traceLog = array();

		/**
		 * @var PDO
		 */
		private $connection;
	}
