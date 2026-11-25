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
		 * @param string $prefix Prefix added to every key. Empty for no prefix.
		 * @throws CacheOperationException
		 */
		public function __construct(string $server = "127.0.0.1", int $port = 11211, string $prefix = '') {
			$this->cache = new \Memcached;
			$this->cache->addServer($server, $port);

			if ($prefix !== '' && !@$this->cache->setOption(\Memcached::OPT_PREFIX_KEY, $prefix))
				throw new CacheOperationException('Invalid key prefix: ' . $prefix);
		}

		/**
		 * Obtain a value from the cache.
		 * Returns null if the item does not exist.
		 *
		 * @api __get
		 * @param string $key Key of the value.
		 * @return mixed|null
		 * @throws CacheOperationException
		 */
		public function __get(string $key): mixed {
			$value = $this->cache->get($key);
			$code = $this->cache->getResultCode();
			if ($code === \Memcached::RES_NOTFOUND)
				return null;

			if ($code !== \Memcached::RES_SUCCESS)
				throw new CacheOperationException('Unable to read value: ' . $key);

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
		 * @throws CacheOperationException
		 */
		public function store(string $key, mixed $value, int $expire = 0): void {
			if($value === null)
			{
				$this->__unset($key);
				return;
			}
			if (!$this->cache->set($key, $value, $expire))
				throw new CacheOperationException('Unable to store value: ' . $key);
		}

		/**
		 * Check if a value exists in the cache.
		 *
		 * @param string $key Key used to store the value.
		 * @return bool True if the key exists in the cache.
		 * @throws CacheOperationException
		 */
		public function exists(string $key): bool {
			$this->cache->get($key);
			$code = $this->cache->getResultCode();
			if ($code === \Memcached::RES_NOTFOUND)
				return false;

			if ($code !== \Memcached::RES_SUCCESS)
				throw new CacheOperationException('Unable to read value: ' . $key);

			return true;
		}

		/**
		 * Store a value in the cache.
		 *
		 * @api __set
		 * @param string $key Key to store the value under.
		 * @param mixed $value Value to store in the cache.
		 * @throws CacheOperationException
		 */
		public function __set(string $key, mixed $value): void {
			$this->store($key, $value);
		}

		/**
		 * Remove an item stored in the cache.
		 *
		 * @api __unset
		 * @param string $key Key of the item to remove.
		 * @throws CacheOperationException
		 */
		public function __unset(string $key): void {
			if (!$this->cache->delete($key) && $this->cache->getResultCode() !== \Memcached::RES_NOTFOUND)
				throw new CacheOperationException('Unable to remove value: ' . $key);
		}

		/**
		 * Flush the cache, removing all stored data.
		 * This removes all keys on the server, including keys without the prefix.
		 *
		 * @api flush
		 * @throws CacheOperationException
		 */
		public function flush(): void {
			if (!$this->cache->flush())
				throw new CacheOperationException('Unable to flush cache.');
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