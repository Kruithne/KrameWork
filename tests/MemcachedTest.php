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

		/**
		 * Test that storing a value throws an exception when the server cannot be reached.
		 */
		public function testStoreUnreachableServerThrows() {
			$cache = new Memcached('127.0.0.1', 1);

			$this->expectException(CacheOperationException::class);
			$cache->store('foo', 'bar');
		}

		/**
		 * Test that removing a value throws an exception when the server cannot be reached.
		 */
		public function testUnsetUnreachableServerThrows() {
			$cache = new Memcached('127.0.0.1', 1);

			$this->expectException(CacheOperationException::class);
			unset($cache->foo);
		}

		/**
		 * Test that a key prefix longer than Memcached allows throws an exception.
		 */
		public function testInvalidPrefixThrows() {
			$this->expectException(CacheOperationException::class);
			new Memcached('127.0.0.1', 1, \str_repeat('a', 129));
		}
	}
