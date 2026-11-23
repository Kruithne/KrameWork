<?php
	namespace KrameWork\Caching;

	/**
	 * Interface IDataCache
	 * Template for user-cache classes.
	 *
	 * @package KrameWork\Caching
	 * @author Kruithne (kruithne@gmail.com)
	 */
	interface IDataCache
	{
		/**
		 * Obtain a value from the cache.
		 * Returns null if the item does not exist.
		 *
		 * @api __get
		 * @param string $key Key of the value.
		 * @return mixed|null
		 */
		public function __get(string $key);

		/**
		 * Store a value in the cache.
		 * Value will not expire, use store() if needed.
		 *
		 * @api __set
		 * @param string $key Key to store the value under.
		 * @param mixed $value Value to store in the cache.
		 */
		public function __set(string $key, $value);

		/**
		 * Store a value in the cache.
		 *
		 * @api store
		 * @param string $key Key to store the value under.
		 * @param mixed $value Value to store in the cache.
		 * @param int $expire 60*60*24*30 >= Unix Timestamp, otherwise seconds. 0 = Never.
		 */
		public function store(string $key, $value, int $expire = 0);

		/**
		 * Check if a value exists in the cache.
		 *
		 * @param string $key Key used to store the value.
		 * @return bool True if the key exists in the cache.
		 */
		public function exists(string $key):bool;

		/**
		 * Remove an item stored in the cache.
		 *
		 * @api __unset
		 * @param string $key Key of the item to remove.
		 */
		public function __unset(string $key);

		/**
		 * Flush the cache, removing all stored data.
		 *
		 * @api flush
		 */
		public function flush();

		/**
		 * Increase a numeric value in the cache.
		 *
		 * @api increment
		 * @param string $key Key of the value.
		 * @param int $weight How much to increment the value.
		 */
		public function increment(string $key, int $weight = 1);

		/**
		 * Decrease a numeric value in the cache.
		 *
		 * @api decrement
		 * @param string $key Key of the value.
		 * @param int $weight How much to decrement the value.
		 */
		public function decrement(string $key, int $weight = 1);
	}
