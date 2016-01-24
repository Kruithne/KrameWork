<?php
	class KW_DatabaseStatement implements IDatabaseStatement
	{
		const SQLARG = ':';

		/**
		 * Construct an SQL database statement.
		 *
		 * @param string $sql An SQL statement to execute.
		 * @param PDO $connection PDO database connection.
		 */
		public function __construct($sql, $connection)
		{
			$this->sql = $sql;
			$this->connection = $connection;
			$this->statement = $this->connection->prepare($this->sql);
		}

		/**
		 * Set a parameter for this statement.
		 * @param string $key
		 * @param object $value
		 */
		public function __set($key, $value)
		{
			$this->setValue(self::SQLARG . $key, $value);
		}

		/**
		 * Retrieve the SQL query string set in this statement.
		 *
		 * @return null|string Statement SQL, will be null if not yet set.
		 */
		public function getQueryString()
		{
			return $this->sql;
		}

		/**
		 * Sets a value for this statement.
		 *
		 * @param string $key The key used in the statement.
		 * @param mixed $value The value to assign to this key.
		 * @return IDatabaseStatement $this Statement instance.
		 */
		public function setValue($key, $value)
		{
			$this->values[$key] = $value;
			return $this;
		}

		/**
		 * Set the type for a parameter.
		 * @param string $key
		 * @param int $type
		 * @see http://php.net/manual/en/pdo.constants.php
		 */
		public function setType($key, $type)
		{
			$this->types[$key] = $type;
		}

		/**
		 * Copies the values already stored inside a row.
		 *
		 * @param IDataContainer $row A row to extract from.
		 * @return IDatabaseStatement Statement instance.
		 */
		public function copyValuesFromRow(IDataContainer $row)
		{
			$row_array = $row->getAsArray();

			foreach ($row_array as $key => $value)
				$this->setValue(self::SQLARG . $key, $value);

			return $this;
		}

		/**
		 * Executes the statement and collects retrieved rows.
		 *
		 * @return IDatabaseStatement $this Database statement instance.
		 */
		public function execute()
		{
			foreach ($this->values as $key => $value)
			{
				$dataType = null;

				if (isset($this->types[$key]))
					$dataType = $this->types[$key];
				else if (is_int($value))
					$dataType = PDO::PARAM_INT;
				elseif (is_bool($value))
					$dataType = PDO::PARAM_BOOL;
				elseif (is_null($value))
					$dataType = PDO::PARAM_NULL;
				elseif (is_string($value))
					$dataType = PDO::PARAM_STR;

				$this->statement->bindValue($key, $value, $dataType);
			}

			$time = microtime(true);
			$this->statement->execute();
			$this->trace($time);
			$this->executed = true;
			$this->rows = null;
			return $this;
		}

		/**
		 * Write an entry to the trace log if tracing is enabled
		 *
		 * @param float $time microtime(true) before query execution
		 */
		private function trace($time)
		{
			if(!class_exists('KW_DatabaseConnection') || !KW_DatabaseConnection::$trace)
				return;

			KW_DatabaseConnection::$trace[] = array(
				'timestamp' => $time,
				'sql' => $this->sql,
				'time' => microtime(true) - $time,
				'param' => $this->values
			);
		}

		/**
		 * Returns an array of database rows retrieved. Will be empty if the statement is not executed.
		 *
		 * @return IDataContainer[]
		 */
		public function getRows()
		{
			if (!$this->executed)
				$this->execute();

			if ($this->rows == null)
			{
				$this->rows = array();
				while ($raw_row = $this->statement->fetch(PDO::FETCH_ASSOC))
				{
					$row = new KW_DatabaseRow();
					foreach ($raw_row as $column => $field)
						$row->__set($column, $field);

					$this->rows[] = $row;
				}
			}

			return $this->rows;
		}

		/**
		 * Returns the first row of the data retrieved. Will by null if no results were returned.
		 * @return IDataContainer|null
		 */
		public function getFirstRow()
		{
			$rows = $this->getRows();
			return isset($rows[0]) ? $rows[0] : null;
		}

		/**
		 * Returns the amount of rows in this statement.
		 *
		 * @return int Amount of rows in this statement. Always zero until executed.
		 */
		public function getRowCount()
		{
			return count($this->rows);
		}

		/**
		 * @return string
		 */
		public function getErrorCode()
		{
			return $this->statement->errorCode();
		}

		/**
		 * @var array
		 */
		private $values = array();

		/**
		 * @var string|null The SQL statement, will bw null if not yet set.
		 */
		private $sql;

		/**
		 * @var IDataContainer[]
		 */
		private $rows = array();

		/**
		 * @var PDO
		 */
		private $connection;

		/**
		 * @var PDOStatement
		 */
		private $statement;

		/**
		 * @var bool
		 */
		private $executed;

		/**
		 * @var int[]
		 */
		private $types = array();
	}
?>
