<?php
	namespace KrameWork\Database;

	require_once(__DIR__ . '/Driver/Generic.php');
	require_once(__DIR__ . '/Driver/PDO.php');
	require_once(__DIR__ . '/ConnectionString.php');

	class UnknownDriverException extends \Exception {}

	/**
	 * Base class for a database connection
	 * @author docpify <morten@runsafe.no>
	 * @author Kruithne <kruithne@gmail.com>
	 * @package KrameWork\Database
	 */
	class Database implements Driver\Generic
	{
		/**
		 * Connect using the PDO driver
		 */
		const DB_DRIVER_FAKE = 0; // For unit tests
		const DB_DRIVER_PDO = 1;

		/**
		 * Database constructor.
		 * @api __construct
		 * @param ConnectionString $connection Connection string specifying how to connect
		 * @param int $driver One of the Database::DB_DRIVER_ constants
		 * @throws UnknownDriverException
		 */
		public function __construct(ConnectionString $connection, int $driver = self::DB_DRIVER_PDO) {
			$this->driver = match ($driver) {
				self::DB_DRIVER_FAKE => new Driver\Fake($connection),
				self::DB_DRIVER_PDO => new Driver\PDO($connection),
				default => throw new UnknownDriverException('Unsupported driver type '.$driver)
			};
		}

		/**
		 * @var Driver\Generic The database driver we are using to run queries
		 */
		private Driver\Generic $driver;

		/**
		 * Execute a query and return an array of ArrayObjects
		 * @api getAll
		 * @param string $sql An SQL query statement
		 * @param array $param An array of values to inject in the statement
		 * @return \stdClass[]
		 */
		function getAll(string $sql, array $param = []): array {
			return $this->driver->getAll($sql, $param);
		}

		/**
		 * Execute a query and return the first row as an ArrayObject
		 * @api getRow
		 * @param string $sql An SQL query statement
		 * @param array $param An array of values to inject in the statement
		 * @return \stdClass|null
		 */
		function getRow(string $sql, array $param = []): ?\stdClass {
			return $this->driver->getRow($sql, $param);
		}

		/**
		 * Execute a query and return the first column of each row
		 * @api getColumn
		 * @param string $sql An SQL query statement
		 * @param array $param An array of values to inject in the statement
		 * @return array
		 */
		function getColumn(string $sql, array $param = []): array {
			return $this->driver->getColumn($sql, $param);
		}

		/**
		 * Execute a query and return the first value of the first row
		 * @api getValue
		 * @param string $sql An SQL query statement
		 * @param array $param An array of values to inject in the statement
		 * @return mixed|null
		 */
		function getValue(string $sql, array $param = []): mixed {
			return $this->driver->getValue($sql, $param);
		}

		/**
		 * Execute a statement and return the number of affected rows
		 * @api execute
		 * @param string $sql An SQL statement
		 * @param array $param An array of values to inject in the statement
		 * @return int
		 */
		function execute(string $sql, array $param = []): int {
			return $this->driver->execute($sql, $param);
		}

		/**
		 * Return the last error for this database connection
		 * For query-specific errors, use getLastQueryError().
		 * @return array|null
		 */
		function getLastError(): ?array {
			return $this->driver->getLastError();
		}

		/**
		 * Returns the error for the last executed query.
		 * @return array|null
		 */
		function getLastQueryError(): ?array {
			return $this->driver->getLastQueryError();
		}

		/**
		 * Returns the ID of the last inserted row.
		 * @return string
		 */
		function getLastInsertID(): string {
			return $this->driver->getLastInsertID();
		}

		function beginTransaction(): bool {
			return $this->driver->beginTransaction();
		}

		function commitTransaction(): bool {
			return $this->driver->commitTransaction();
		}

		function rollbackTransaction(): bool {
			return $this->driver->rollbackTransaction();
		}
	}