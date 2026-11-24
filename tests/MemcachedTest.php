<?php
	use KrameWork\Caching\CacheOperationException;
	use KrameWork\Caching\Memcached;
	require_once(__DIR__ . '/../src/Caching/Memcached.php');

	class MemcachedTest extends PHPUnit\Framework\TestCase
	{
		/**
		 * Test that reading a value throws an exception when the server cannot be reached.
		 */
		public function testGetUnreachableServerThrows() {
			$cache = new Memcached('127.0.0.1', 1);

			$this->expectException(CacheOperationException::class);
			$cache->__get('foo');
		}

		/**
		 * Test that checking a value throws an exception when the server cannot be reached.
		 */
		public function testExistsUnreachableServerThrows() {
			$cache = new Memcached('127.0.0.1', 1);

			$this->expectException(CacheOperationException::class);
			$cache->exists('foo');
		}
	}
