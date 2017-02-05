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
			$this->assertEquals(42, $enum->valueOf('test_1'), 'Enum value "test_1" does not equal 42.');
			$this->assertEquals(1337, $enum->valueOf('test_2'), 'Enum value "test_2" does not equal 1337.');

			// Case sensitivity.
			$this->assertEquals(42, $enum->valueOf('TEST_1', true), 'Enum value "TEST_1" does not equal 42 (case-sensitive).');
			$this->assertEquals(null, $enum->valueOf('test_1', true), 'Enum value "test_1" does not equal null (case-sensitive).');

			// Missing keys.
			$this->assertEquals(null, $enum->valueOf('missingKey', true), 'Enum value "missingKey" does not equal null (case-sensitive).');
			$this->assertEquals(null, $enum->valueOf('missingKey', false), 'Enum value "missingKey" does not equal null.');
		}
	}
