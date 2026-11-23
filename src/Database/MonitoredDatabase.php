<?php
	namespace KrameWork\Database;

	use KrameWork\Timing\Timer;

	require_once(__DIR__ . '/Database.php');
	require_once(__DIR__ . '/../Timing/Timer.php');

	/**
	 * Database connection with build in performance monitoring
	 * @author docpify <morten@runsafe.no>
	 * @package KrameWork\Database
	 */
	class MonitoredDatabase extends Database
	{
		/**
		 * MonitoredDatabase constructor.
		 * @api __construct
		 * @param ConnectionString $connection A connection string
		 * @param int $driver A Database::DB_DRIVER_ constant
		 * @param int $threshold Trigger a warning if execution takes longer than $threshold seconds
		 */
		function __construct(ConnectionString $connection, int $driver, private int $threshold = 2) {
			parent::__construct($connection, $driver);
			$this->timer = new Timer(Timer::FORMAT_MICROSECONDS);
			$this->statistics = [];
		}

		/**
		 * Execute a query and return an array of ArrayObjects
		 * @api getAll
		 * @param string $sql An SQL query statement
		 * @param array $param An array of values to inject in the statement
		 * @return \stdClass[]
		 */
		function getAll(string $sql, array $param = []): array {
			$this->timer->start();
			$result = parent::getAll($sql, $param);
			$this->log($sql, $param);
			return $result;
		}

		/**
		 * Execute a query and return the first column of each row
		 * @api getColumn
		 * @param string $sql An SQL query statement
		 * @param array $param An array of values to inject in the statement
		 * @return array
		 */
		function getColumn(string $sql, array $param = []): array {
			$this->timer->start();
			$result = parent::getColumn($sql, $param);
			$this->log($sql, $param);
			return $result;
		}

		/**
		 * Execute a query and return the first row as an ArrayObject
		 * @api getRow
		 * @param string $sql An SQL query statement
		 * @param array $param An array of values to inject in the statement
		 * @return \stdClass|null
		 */
		function getRow(string $sql, array $param = []): ?\stdClass {
			$this->timer->start();
			$result = parent::getRow($sql, $param);
			$this->log($sql, $param);
			return $result;
		}

		/**
		 * Execute a query and return the first value of the first row
		 * @api getValue
		 * @param string $sql An SQL query statement
		 * @param array $param An array of values to inject in the statement
		 * @return mixed
		 */
		function getValue(string $sql, array $param = []): mixed {
			$this->timer->start();
			$result = parent::getValue($sql, $param);
			$this->log($sql, $param);
			return $result;
		}

		/**
		 * Execute a statement and return the number of affected rows
		 * @api execute
		 * @param string $sql An SQL statement
		 * @param array $param An array of values to inject in the statement
		 * @return int
		 */
		function execute(string $sql, array $param = []): int {
			$this->timer->start();
			$result = parent::execute($sql, $param);
			$this->log($sql, $param);
			return $result;
		}

		/**
		 * Log timing information about a completed database statement.
		 * @internal
		 * @param string $sql
		 * @param array $param
		 */
		private function log(string $sql, array $param): void {
			$time = $this->timer->stop();
			$start = (string)$this->timer->getStartTimestamp();
			if ($time > $this->threshold)
				\trigger_error($this->formatWarning($sql, $param, $time), E_USER_WARNING);

			// It is highly unlikely two queries would have the same timestamp;
			// But just to be safe, we pack this into an array.
			if (!isset($this->statistics[$start]))
				$this->statistics[$start] = [];
			$this->statistics[$start][] = [$sql, $param, $time];
		}

		/**
		 * Format a warning message when a statement takes longer to execute than the configured threshold.
		 * @internal
		 * @param string $sql The SQL statement that was executed.
		 * @param array $param The parameters it was executed with.
		 * @param float $time The number of seconds execution took.
		 * @return string
		 */
		private function formatWarning(string $sql, array $param, float $time): string {
			return \sprintf('Query completed in %3$.2e seconds: %1$s {%2$s}', $sql, \json_encode($param), $time);
		}

		/**
		 * Returns statistics data collected over the lifetime of the object
		 * @api getStatistics
		 * @return array
		 */
		public function getStatistics(): array {
			return $this->statistics;
		}

		/**
		 * @var Timer
		 */
		private Timer $timer;

		/**
		 * @var array
		 */
		private array $statistics;
	}
