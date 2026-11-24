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
		public function __construct(private IDataCache $cache, private Database $database, private int $defaultTtl) {}

		/**
		 * Execute a query and return an array of ArrayObjects, caching the results.
		 * @api getAll
		 * @param string $sql An SQL query statement
		 * @param array<int|string, mixed> $param An array of values to inject in the statement
		 * @param int|null $ttl Number of seconds to cache the results, 0 forces refresh
		 * @return \stdClass[]
		 */
		function getAll(string $sql, array $param = [], ?int $ttl = null): array {
			if ($ttl === null)
				$ttl = $this->defaultTtl;

			$key = 'all_' . $this->getKey($sql, $param);
			$cached = $ttl > 0 ? $this->cache->__get($key) : null;
			if (\is_array($cached))
				return $cached;

			$data = $this->database->getAll($sql, $param);
			$this->cache->store($key, $data, $ttl);
			return $data;
		}

		/**
		 * Execute a query and return the first row as an ArrayObject, caching the result.
		 * @api getRow
		 * @param string $sql An SQL query statement
		 * @param array<int|string, mixed> $param An array of values to inject in the statement
		 * @param int|null $ttl Number of seconds to cache the results, 0 forces refresh
		 * @return \stdClass|null
		 */
		function getRow(string $sql, array $param = [], ?int $ttl = null): ?\stdClass {
			if ($ttl === null)
				$ttl = $this->defaultTtl;

			$key = 'row_' . $this->getKey($sql, $param);
			$cached = $ttl > 0 ? $this->cache->__get($key) : null;
			if ($cached instanceof \stdClass)
				return $cached;

			$data = $this->database->getRow($sql, $param);
			$this->cache->store($key, $data, $ttl);
			return $data;
		}

		/**
		 * Execute a query and return the first column of each row, caching the results.
		 * @api getColumn
		 * @param string $sql An SQL query statement
		 * @param array<int|string, mixed> $param An array of values to inject in the statement
		 * @param int|null $ttl Number of seconds to cache the results, 0 forces refresh
		 * @return mixed[]
		 */
		function getColumn(string $sql, array $param = [], ?int $ttl = null): array {
			if ($ttl === null)
				$ttl = $this->defaultTtl;

			$key = 'col_' . $this->getKey($sql, $param);
			$cached = $ttl > 0 ? $this->cache->__get($key) : null;
			if (\is_array($cached))
				return $cached;

			$data = $this->database->getColumn($sql, $param);
			$this->cache->store($key, $data, $ttl);
			return $data;
		}

		/**
		 * Execute a query and return the first value of the first row, caching the value.
		 * @api getValue
		 * @param string $sql An SQL query statement
		 * @param array<int|string, mixed> $param An array of values to inject in the statement
		 * @param int|null $ttl Number of seconds to cache the results, 0 forces refresh
		 * @return mixed
		 */
		function getValue(string $sql, array $param = [], ?int $ttl = null): mixed {
			if ($ttl === null)
				$ttl = $this->defaultTtl;

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
		 * @param array<int|string, mixed> $param An array of values to inject in the statement
		 * @return int
		 */
		function execute(string $sql, array $param = []): int {
			return $this->database->execute($sql, $param);
		}

		/**
		 * @param string $sql An SQL statement
		 * @param array<int|string, mixed> $param SQL parameters
		 * @return string The cache key
		 */
		private function getKey(string $sql, array $param): string {
			return \sha1(\json_encode([$sql, $param], \JSON_THROW_ON_ERROR));
		}
	}