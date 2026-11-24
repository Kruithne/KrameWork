<?php
	namespace KrameWork\Caching;

	require_once(__DIR__ . "/IDataCache.php");
	require_once(__DIR__ . '/CacheOperationException.php');

	/**
	 * Class Memcached
	 * Interface layer for Memcached.
	 *
	 * @package KrameWork\Caching
	 * @author Kruithne (kruithne@gmail.com)
	 */
	class Memcached implements IDataCache
	{
		/**
		 * Memcached constructor.
		 *
		 * @api __construct
		 * @param string $server Memcached server address.
		 * @param int $port Memcached server port.
		 */
		public function __construct(string $server = "127.0.0.1", int $port = 11211) {
			$this->cache = new \Memcached;
			$this->cache->addServer($server, $port);
		}

		/**
		 * Obtain a value from the cache.
		 * Returns null if the item does not exist.
		 *
		 * @api __get
		 * @param string $key Key of the value.
		 * @return mixed|null
		 */
		public function __get(string $key): mixed {
			$value = $this->cache->get($key);
			if ($this->cache->getResultCode() == \Memcached::RES_NOTFOUND)
				return null;

			return $value;
		}

		/**
		 * Store a value in the cache.
		 * Value will not expire. Use store() if needed.
		 *
		 * @api store
		 * @param string $key Key to store the value under.
		 * @param mixed $value Value to store in the cache.
		 * @param int $expire 60*60*24*30 >= Unix Timestamp, otherwise seconds. 0 = Never.
		 */
		public function store(string $key, mixed $value, int $expire = 0): void {
			if($value === null)
			{
				$this->__unset($key);
				return;
			}
			$this->cache->set($key, $value, $expire);
		}

		/**
		 * Check if a value exists in the cache.
		 *
		 * @param string $key Key used to store the value.
		 * @return bool True if the key exists in the cache.
		 */
		public function exists(string $key): bool {
			$this->cache->get($key);
			return $this->cache->getResultCode() != \Memcached::RES_NOTFOUND;
		}

		/**
		 * Store a value in the cache.
		 *
		 * @api __set
		 * @param string $key Key to store the value under.
		 * @param mixed $value Value to store in the cache.
		 */
		public function __set(string $key, mixed $value): void {
			$this->store($key, $value);
		}

		/**
		 * Remove an item stored in the cache.
		 *
		 * @api __unset
		 * @param string $key Key of the item to remove.
		 */
		public function __unset(string $key): void {
			$this->cache->delete($key);
		}

		/**
		 * Flush the cache, removing all stored data.
		 *
		 * @api flush
		 */
		public function flush(): void {
			$this->cache->flush();
		}

		/**
		 * Increase an integer value in the cache.
		 * A missing value starts at 0.
		 *
		 * @api increment
		 * @param string $key Key of the value.
		 * @param int $weight How much to increment the value.
		 * @return int The new value.
		 * @throws CacheOperationException
		 */
		public function increment(string $key, int $weight = 1): int {
			$value = $this->cache->increment($key, $weight);
			if ($value === false && $this->cache->getResultCode() === \Memcached::RES_NOTFOUND) {
				if ($this->cache->add($key, $weight))
					return $weight;

				$value = $this->cache->increment($key, $weight);
			}

			if ($value === false)
				throw new CacheOperationException('Unable to increment value: ' . $key);

			return $value;
		}

		/**
		 * Decrease an integer value in the cache.
		 * A missing value starts at 0. The value does not go below 0.
		 *
		 * @api decrement
		 * @param string $key Key of the value.
		 * @param int $weight How much to decrement the value.
		 * @return int The new value.
		 * @throws CacheOperationException
		 */
		public function decrement(string $key, int $weight = 1): int {
			$value = $this->cache->decrement($key, $weight);
			if ($value === false && $this->cache->getResultCode() === \Memcached::RES_NOTFOUND) {
				if ($this->cache->add($key, 0))
					return 0;

				$value = $this->cache->decrement($key, $weight);
			}

			if ($value === false)
				throw new CacheOperationException('Unable to decrement value: ' . $key);

			return $value;
		}

		/**
		 * @var \Memcached
		 */
		private \Memcached $cache;
	}