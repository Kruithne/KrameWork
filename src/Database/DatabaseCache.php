<?php
	namespace KrameWork\Database;

	use KrameWork\Caching\IDataCache;

	/**
	 * Wrapper class to automatically cache query results
	 * @author docpify <morten@runsafe.no>
	 * @package KrameWork\Database
	 */
	class DatabaseCache
	{
		public function __construct(IDataCache $cache, Database $database, int $defaultTtl) {
			$this->cache = $cache;
			$this->database = $database;
			$this->ttl = $defaultTtl;
		}

		/**
		 * Execute a query and return an array of ArrayObjects, caching the results.
		 * @api getAll
		 * @param string $sql An SQL query statement
		 * @param array $param An array of values to inject in the statement
		 * @param int $ttl Number of seconds to cache the results, 0 forces refresh
		 * @return array|\ArrayObject[]
		 */
		function getAll(string $sql, array $param = [], ?int $ttl = null): array {
			if ($ttl === null)
				$ttl = $this->ttl;

			$key = 'all_' . $this->getKey($sql, $param);
			if ($this->cache->exists($key) && $ttl > 0)
				return $this->cache->__get($key);

			$data = $this->database->getAll($sql, $param);
			$this->cache->store($key, $data, $ttl);
			return $data;
		}

		/**
		 * Execute a query and return the first row as an ArrayObject, caching the result.
		 * @api getRow
		 * @param string $sql An SQL query statement
		 * @param array $param An array of values to inject in the statement
		 * @param int $ttl Number of seconds to cache the results, 0 forces refresh
		 * @return \ArrayObject
		 */
		function getRow(string $sql, array $param = [], ?int $ttl = null): \ArrayObject {
			if ($ttl === null)
				$ttl = $this->ttl;

			$key = 'row_' . $this->getKey($sql, $param);
			if ($this->cache->exists($key) && $ttl > 0)
				return $this->cache->__get($key);

			$data = $this->database->getRow($sql, $param);
			$this->cache->store($key, $data, $ttl);
			return $data;
		}

		/**
		 * Execute a query and return the first column of each row, caching the results.
		 * @api getColumn
		 * @param string $sql An SQL query statement
		 * @param array $param An array of values to inject in the statement
		 * @param int $ttl Number of seconds to cache the results, 0 forces refresh
		 * @return array
		 */
		function getColumn(string $sql, array $param = [], ?int $ttl = null): array {
			if ($ttl === null)
				$ttl = $this->ttl;

			$key = 'col_' . $this->getKey($sql, $param);
			if ($this->cache->exists($key) && $ttl > 0)
				return $this->cache->__get($key);

			$data = $this->database->getColumn($sql, $param);
			$this->cache->store($key, $data, $ttl);
			return $data;
		}

		/**
		 * Execute a query and return the first value of the first row, caching the value.
		 * @api getValue
		 * @param string $sql An SQL query statement
		 * @param array $param An array of values to inject in the statement
		 * @param int $ttl Number of seconds to cache the results, 0 forces refresh
		 * @return mixed
		 */
		function getValue(string $sql, array $param = [], ?int $ttl = null) {
			if ($ttl === null)
				$ttl = $this->ttl;

			$key = 'val_' . $this->getKey($sql, $param);
			if ($this->cache->exists($key) && $ttl > 0)
				return $this->cache->__get($key);

			$data = $this->database->getValue($sql, $param);
			$this->cache->store($key, $data, $ttl);
			return $data;
		}

		/**
		 * Execute a statement and return the number of affected rows, without caching the result.
		 * @api execute
		 * @param string $sql An SQL statement
		 * @param array $param An array of values to inject in the statement
		 * @return int
		 */
		function execute(string $sql, array $param = []): int {
			return $this->database->execute($sql, $param);
		}

		/**
		 * @param $sql string An SQL statement
		 * @param $param array SQL parameters
		 * @return string The cache key
		 */
		private function getKey($sql, $param): string {
			return \sha1(\json_encode([$sql, $param]));
		}

		/**
		 * @var IDataCache
		 */
		private $cache;

		/**
		 * @var Database
		 */
		private $database;

		/**
		 * @var int
		 */
		private $ttl;
	}