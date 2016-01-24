<?php
	require_once("/home/travis/build/Kruithne/KrameWork/testing/resources/default_bootstrap.php");

	class EnumTest extends PHPUnit_Framework_TestCase
	{
		/**
		 * Test basic functions of an enum.
		 */
		public function testBasicEnumFunctions()
		{
			$enum = new MockEnum();
			$this->assertEquals(42, $enum->valueOf('test_1'));
			$this->assertEquals(1337, $enum->valueOf('test_2'));

			// Case sensitivity.
			$this->assertEquals(42, $enum->valueOf('TEST_1', true));
			$this->assertEquals(null, $enum->valueOf('test_1', true));

			// Missing keys.
			$this->assertEquals(null, $enum->valueOf('missingKey', true));
			$this->assertEquals(null, $enum->valueOf('missingKey', false));
		}
	}
?>