<?php
	require_once("/home/travis/build/Kruithne/KrameWork/testing/resources/default_bootstrap.php");

	class SessionTest extends PHPUnit_Framework_TestCase
	{
		/**
		 * Check that setting/getting with our static Session class works.
		 */
		public function testSessionGetSet()
		{
			Session::Set("testSessionGetSet", 42);
			$rt = Session::Get("testSessionGetSet");

			$this->assertEquals(42, $rt);
		}

		/**
		 * Confirm that trying to get a value that does not exist returns NULL.
		 */
		public function testSessionInvalid()
		{
			$rt = Session::Get("someValueWeDidNotSet");
			$this->assertEquals(null, $rt);
		}

		/**
		 * Values deleted using our Session interface should return NULL.
		 */
		public function testSessionDelete()
		{
			Session::Set("testSessionDelete", 1337);
			Session::Delete("testSessionDelete");
			$rt = Session::Get("testSessionDelete");

			$this->assertEquals(null, $rt);
		}
	}
?>