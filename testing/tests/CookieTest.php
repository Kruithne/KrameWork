<?php
	require_once("/home/travis/build/Kruithne/KrameWork/testing/resources/default_bootstrap.php");

	class CookieTest extends PHPUnit_Framework_TestCase
	{
		/**
		 * Check that setting/getting with our static Cookie class works.
		 */
		public function testCookieGetSet()
		{
			Cookie::Set("testCookieGetSet", 42, time() + (60 * 10));
			$rt = Cookie::Get("testCookieGetSet");

			$this->assertEquals(42, $rt, "Failed to set/get a cookie value.");
		}

		/**
		 * Confirm that trying to get a Cookie value that does not exist returns NULL.
		 */
		public function testCookieInvalid()
		{
			$rt = Cookie::Get("someValueWeDidNotSet");
			$this->assertEquals(null, $rt, "Querying an invalid cookie value did not return NULL.");
		}

		/**
		 * Values deleted using our Cookie interface should return NULL.
		 */
		public function testCookieDelete()
		{
			Cookie::Set("testCookieDelete", 1337, time() + (60 * 10));
			Cookie::Delete("testCookieDelete");
			$rt = Cookie::Get("testCookieDelete");

			if ($rt !== null && $rt !== '')
				$this->fail("Querying a deleted cookie did not return NULL or blank (expiring).");
		}
	}
?>