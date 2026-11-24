<?php
	namespace KrameWork\Database\Driver;

	use KrameWork\Database\ConnectionString;

	/**
	 * This driver wraps a PDO connection, supporting a wide range of database engines.
	 * @author docpify <morten@runsafe.no>
	 * @author Kruithne <kruithne@gmail.com>
	 * @package KrameWork\Database\Driver
	 */
	class PDO implements Generic
	{
		/**
		 * PDO constructor.
		 * @api __construct
		 * @param ConnectionString $connection
		 */
		public function __construct(private ConnectionString $connection) {
			$this->connect();
		}

		private function connect(): void
		{
			if (\class_exists('\KrameWork\Runtime\ErrorHandler'))
				\KrameWork\Runtime\ErrorHandler::suspend();
			$error = false;
			try
			{
				$this->pdo = new \PDO($this->connection->__toString(), $this->connection->getUsername(), $this->connection->getPassword());
				$this->pdo->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);
			}
			catch (\Throwable $e)
			{
				$error = $e->getMessage();
			}
			if (class_exists('\KrameWork\Runtime\ErrorHandler'))
				\KrameWork\Runtime\ErrorHandler::resume();
			if($error)
				throw new \Exception($error);
		}

		public function __sleep(): array { return ['connection']; }
		public function __wakeup(): void { $this->connect(); }

		/**
		 * @var \PDO
		 */
		private \PDO $pdo;

		/**
		 * @var \PDOStatement|null
		 */
		private ?\PDOStatement $lastQuery = null;

		/**
		 * Execute a query and return an array of ArrayObjects
		 * @api getAll
		 * @param string $sql An SQL query statement
		 * @param array<int|string, mixed> $param An array of values to inject in the statement
		 * @return \stdClass[]
		 */
		function getAll(string $sql, array $param): array {
			$query = $this->lastQuery = $this->pdo->prepare($sql);
			$this->bind($query, $param);
			$query->execute();

			/** @var \stdClass[] $rows */
			$rows = $query->fetchAll(\PDO::FETCH_OBJ);
			return $rows;
		}

		/**
		 * Execute a query and return the first row as an ArrayObject
		 * @api getRow
		 * @param string $sql An SQL query statement
		 * @param array<int|string, mixed> $param An array of values to inject in the statement
		 * @return \stdClass|null
		 */
		function getRow(string $sql, array $param): ?\stdClass {
			$query = $this->lastQuery = $this->pdo->prepare($sql);
			$this->bind($query, $param);
			$query->execute();
			return $query->fetchObject() ?: null;
		}

		/**
		 * Execute a query and return the first column of each row
		 * @api getColumn
		 * @param string $sql An SQL query statement
		 * @param array<int|string, mixed> $param An array of values to inject in the statement
		 * @return mixed[]
		 */
		function getColumn(string $sql, array $param): array {
			$query = $this->lastQuery = $this->pdo->prepare($sql);
			$this->bind($query, $param);
			$query->execute();
			return $query->fetchAll(\PDO::FETCH_COLUMN, 0);
		}

		/**
		 * Execute a query and return the first value of the first row
		 * @api getValue
		 * @param string $sql An SQL query statement
		 * @param array<int|string, mixed> $param An array of values to inject in the statement
		 * @return mixed|null
		 */
		function getValue(string $sql, array $param): mixed {
			$query = $this->lastQuery = $this->pdo->prepare($sql);
			$this->bind($query, $param);
			$query->execute();

			$value = $query->fetchColumn();
			return $value === false ? null : $value;
		}

		/**
		 * Execute a statement and return the number of affected rows
		 * @api execute
		 * @param string $sql An SQL statement
		 * @param array<int|string, mixed> $param An array of values to inject in the statement
		 * @return int
		 */
		function execute(string $sql, array $param): int {
			$query = $this->lastQuery = $this->pdo->prepare($sql);
			$this->bind($query, $param);
			$query->execute();
			return $query->rowCount();
		}

		/**
		 * Return the last error for this database connection
		 * For query-specific errors, use getLastQueryError().
		 * @return array{0: string, 1: int|null, 2: string|null}
		 */
		function getLastError(): array {
			/** @var array{0: string, 1: int|null, 2: string|null} $error */
			$error = $this->pdo->errorInfo();
			return $error;
		}

		/**
		 * Returns the error for the last executed query.
		 * @return array{0: string, 1: int|null, 2: string|null}|null
		 */
		function getLastQueryError(): ?array {
			/** @var array{0: string, 1: int|null, 2: string|null}|null $error */
			$error = $this->lastQuery?->errorInfo();
			return $error;
		}

		/**
		 * Returns the ID of the last inserted row.
		 * @return string
		 * @throws \Exception If the driver cannot return the ID
		 */
		function getLastInsertID(): string {
			$id = $this->pdo->lastInsertId();
			if ($id === false)
				throw new \Exception('Unable to retrieve last insert ID');

			return $id;
		}

		/**
		 * @api beginTransaction
		 * Open a database transaction
		 */
		public function beginTransaction(): bool {
			return $this->pdo->beginTransaction();
		}

		/**
		 * Rolls back the current transaction
		 * @api rollbackTransaction
		 * @return bool False if no transaction is active
		 */
		public function rollbackTransaction(): bool {
			if (!$this->pdo->inTransaction())
				return false;

			return $this->pdo->rollBack();
		}

		/**
		 * Commits the current transaction
		 * @api commitTransaction
		 * @return bool False if no transaction is active
		 */
		public function commitTransaction(): bool {
			if (!$this->pdo->inTransaction())
				return false;

			return $this->pdo->commit();
		}

		/**
		 * Bind the parameters to the prepared statement, guessing at parameter types.
		 * @param \PDOStatement $query A prepared statement
		 * @param array<int|string, mixed> $param Parameters to bind
		 * @throws \Exception If a mixed usage of numeric and string indices are detected
		 */
		private function bind(\PDOStatement $query, array $param): void
		{
			if (!$param)
				return;
			$syntax = null;
			foreach ($param as $k => $v) {
				$type = \PDO::PARAM_STR;
				if (\is_int($v))
					$type = \PDO::PARAM_INT;
				if (\is_bool($v))
					$type = \PDO::PARAM_BOOL;

				if (\is_int($k)) {
					if ($syntax !== null && $syntax != 1)
						throw new \Exception("Mixed parameter type in parameter list");
					$syntax = 1;
					$query->bindValue($k + 1, $v, $type);
				} else {
					if ($syntax !== null && $syntax != 2)
						throw new \Exception("Mixed parameter type in parameter list");
					$syntax = 2;
					$query->bindValue(':' . $k, $v, $type);
				}
			}
		}
	}
