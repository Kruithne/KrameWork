<?php
	use KrameWork\Security\Password;
	require_once(__DIR__ . '/../src/Security/Password.php');

	class PasswordTest extends PHPUnit\Framework\TestCase
	{
		/**
		 * Test that a hash made with the default algorithm verifies against the same password.
		 */
		public function testDefaultHashVerifies() {
			$password = new Password('hunter2');
			$this->assertTrue($password->verify($password->asHash()));
		}
	}
