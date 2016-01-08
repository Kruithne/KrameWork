<?php
	require_once("/home/travis/build/Kruithne/KrameWork/testing/resources/default_bootstrap.php");

	class FrameworkTest extends PHPUnit_Framework_TestCase
	{
		public function testIsTrue()
		{
			$this->assertEquals(true, true);
		}
	}
?>