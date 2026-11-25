<?php
	namespace KrameWork\Caching;

	require_once(__DIR__ . "/IDataCache.php");
	require_once(__DIR__ . '/CacheOperationException.php');

	/**
	 * Class APCu
	 * Interface for APCu.
	 *
	 * @package KrameWork\Caching
	 */
	class APCu implements IDataCache
	{
		/**
		 * APCu constructor.
		 *
		 * @api __construct
		 * @param string $prefix Prefix added to every key. Empty for no prefix.
		 * @throws CacheOperationException
		 */
		public function __construct(private string $prefix = '') {
			if (!\function_exists('apcu_enabled') || !\apcu_enabled())
				throw new CacheOperationException('APCu is not installed or enabled.');
		}

		/**
		 * Obtain a value from the cache.
		 * Returns null if the item does not exist.
		 * Null is never stored, so it cannot be confused with a stored value.
		 *
		 * @api __get
		 * @param string $key Key of the value.
		 * @return mixed|null
		 */
		public function __get(string $key): mixed {
			$value = \apcu_fetch($this->prefix . $key, $success);
			return $success ? $value : null;
		}

		/**
		 * Store a value in the cache.
		 * Storing null removes the key.
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
			if ($expire > 60*60*24*30)
				$expire = $expire - \time();

			if (!\apcu_store($this->prefix . $key, $value, $expire))
				throw new CacheOperationException('Unable to store value: ' . $key);
		}

		/**
		 * Check if a value exists in the cache.
		 *
		 * @api exists
		 * @param string $key Key used to store the value.
		 * @return bool True if the key exists in the cache.
		 */
		public function exists(string $key): bool {
			return \apcu_exists($this->prefix . $key);
		}

		/**
		 * Store a value in the cache.
		 * Value will not expire, use store() if needed.
		 * Storing null removes the key.
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
		 */
		public function __unset(string $key): void {
			\apcu_delete($this->prefix . $key);
		}

		/**
		 * Flush the cache, removing all stored data.
		 * With a prefix, only keys with that prefix are removed.
		 *
		 * @api flush
		 */
		public function flush(): void {
			if ($this->prefix === '') {
				\apcu_clear_cache();
				return;
			}

			\apcu_delete(new \APCUIterator('/^' . \preg_quote($this->prefix, '/') . '/', \APC_ITER_KEY));
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
			$value = \apcu_inc($this->prefix . $key, $weight);
			if ($value === false)
				throw new CacheOperationException('Unable to increment value: ' . $key);

			return $value;
		}

		/**
		 * Decrease an integer value in the cache.
		 * A missing value starts at 0.
		 *
		 * @api decrement
		 * @param string $key Key of the value.
		 * @param int $weight How much to decrement the value.
		 * @return int The new value.
		 * @throws CacheOperationException
		 */
		public function decrement(string $key, int $weight = 1): int {
			$value = \apcu_dec($this->prefix . $key, $weight);
			if ($value === false)
				throw new CacheOperationException('Unable to decrement value: ' . $key);

			return $value;
		}
	}