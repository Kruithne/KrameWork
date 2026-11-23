<?php
	use KrameWork\HTTP\HTTPHeaders;

	require_once(__DIR__ . '/../src/HTTP/HTTPHeaders.php');

	class HTTPHeadersTest extends PHPUnit\Framework\TestCase
	{
		/**
		 * Test that a pre-compiled string with invalid base64 characters is rejected.
		 */
		public function testInvalidBase64IsRejected() {
			$encoded = \base64_encode('["X-Test: 1"]');
			$corrupt = \substr($encoded, 0, 4) . '*' . \substr($encoded, 4);

			$headers = new HTTPHeaders($corrupt);
			$this->assertEquals(\base64_encode('[]'), (string) $headers);
		}

		/**
		 * Test that a pre-compiled string with non-array JSON is rejected.
		 */
		public function testNonArrayJSONIsRejected() {
			$headers = new HTTPHeaders(\base64_encode('"abc"'));
			$this->assertEquals(\base64_encode('[]'), (string) $headers);
		}
	}
