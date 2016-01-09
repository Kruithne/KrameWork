<?php
	require_once("/home/travis/build/Kruithne/KrameWork/testing/resources/default_bootstrap.php");

	class FrameworkTest extends PHPUnit_Framework_TestCase
	{
		/**
		 * Checks if true == true. This should literally never fail.
		 */
		public function testIsTrue()
		{
			$this->assertEquals(true, true, "The world as we know it, has broken.");
		}
	}
?>