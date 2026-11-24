<?php
	namespace KrameWork\Caching;

	require_once(__DIR__ . '/IDataCache.php');
	require_once(__DIR__ . '/CacheOperationException.php');

	class SessionInitiationException extends CacheOperationException {}

	/**
	 * Class Session
	 * Session handling instance.
	 *
	 * @package KrameWork
	 * @author Kruithne (kruithne@gmail.com)
	 */
	class Session implements IDataCache
	{
		/**
		 * Session constructor.
		 *
		 * @api __construct
		 * @param bool $autoStart Start session on instantiation.
		 * @throws SessionInitiationException
		 */
		public function __construct(bool $autoStart = true) {
			if ($autoStart)
				$this->start();
		}

		/**
		 * Attempt to start a session.
		 *
		 * @api start
		 * @throws SessionInitiationException
		 */
		public function start(): void {
			// Check if session is already started.
			if ($this->isActive())
				return;

			// Ensure we're not on a CLI.
			if (\PHP_SAPI == 'cli')
				throw new SessionInitiationException('Session cannot be started in CLI environment.');

			if (!\session_start())
				throw new SessionInitiationException('Unable to start session.');
		}

		/**
		 * Delete all existing data in the session and start a
		 * newly generated one.
		 *
		 * @api flush
		 * @throws SessionInitiationException
		 */
		public function flush(): void {
			if (!\session_unset() || !\session_regenerate_id(false) || !\session_destroy() || !\session_start())
				throw new SessionInitiationException('Unable to flush session.');
		}

		/**
		 * Check if a session is currently active.
		 *
		 * @api isActive
		 * @return bool
		 */
		public function isActive(): bool {
			// CLI does not support sessions.
			if (\PHP_SAPI == 'cli')
				return false;

			return \session_status() == \PHP_SESSION_ACTIVE;
		}

		/**
		 * Retrieve a session value.
		 *
		 * @api __get
		 * @param string $name Key to retrieve from the session.
		 * @return mixed
		 * @link http://php.net/manual/en/language.oop5.overloading.php#language.oop5.overloading.members
		 */
		function __get(string $name): mixed {
			return $_SESSION[$name] ?? null;
		}

		/**
		 * Set a session value.
		 *
		 * @api __set
		 * @param string $name Key to set the value with.
		 * @param mixed $value Value to store in the session.
		 * @return void
		 * @link http://php.net/manual/en/language.oop5.overloading.php#language.oop5.overloading.members
		 */
		function __set(string $name, mixed $value): void {
			if($value === null)
			{
				$this->__unset($name);
				return;
			}
			$_SESSION[$name] = $value;
		}

		/**
		 * Unset a session value.
		 *
		 * @api __unset
		 * @param string $name Key to remove from the session.
		 * @return void
		 * @link http://php.net/manual/en/language.oop5.overloading.php#language.oop5.overloading.members
		 */
		function __unset(string $name): void {
			unset($_SESSION[$name]);
		}

		/**
		 * Store a value in the cache.
		 *
		 * @api store
		 * @param string $key Key to store the value under.
		 * @param mixed $value Value to store in the cache.
		 * @param int $expire No effect for this implementation.
		 */
		public function store(string $key, mixed $value, int $expire = 0): void {
			if($value === null)
			{
				$this->__unset($key);
				return;
			}
			$_SESSION[$key] = $value;
		}

		/**
		 * Check if a value exists in the cache.
		 *
		 * @param string $key Key used to store the value.
		 * @return bool True if the key exists in the cache.
		 */
		public function exists(string $key): bool {
			return isset($_SESSION[$key]);
		}

		/**
		 * Increase a numeric value in the cache.
		 * A missing or non-numeric value starts at 0.
		 *
		 * @api increment
		 * @param string $key Key of the value.
		 * @param int $weight How much to increment the value.
		 * @return int|float The new value.
		 */
		public function increment(string $key, int $weight = 1): int|float {
			$value = $this->__get($key);
			$value = (\is_numeric($value) ? $value : 0) + $weight;
			$this->store($key, $value);

			return $value;
		}

		/**
		 * Decrease a numeric value in the cache.
		 * A missing or non-numeric value starts at 0.
		 *
		 * @api decrement
		 * @param string $key Key of the value.
		 * @param int $weight How much to decrement the value.
		 * @return int|float The new value.
		 */
		public function decrement(string $key, int $weight = 1): int|float {
			$value = $this->__get($key);
			$value = (\is_numeric($value) ? $value : 0) - $weight;
			$this->store($key, $value);

			return $value;
		}
	}
