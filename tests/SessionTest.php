<?php
	use KrameWork\Caching\Session;
	require_once(__DIR__ . '/../src/Caching/Session.php');

	class SessionTest extends PHPUnit\Framework\TestCase
	{
		/**
		 * Test that non-numeric values are reset to 0 before incrementing or decrementing.
		 */
		public function testNonNumericReset() {
			$_SESSION = [];
			$session = new Session(false);

			$session->foo = 'bar';
			$session->increment('foo');
			$this->assertEquals(1, $session->foo);

			$session->bar = [42];
			$session->decrement('bar');
			$this->assertEquals(-1, $session->bar);
		}
	}
