<?php
	use KrameWork\HTTP\HTTPHeader;
	use KrameWork\HTTP\HTTPHeaders;

	require_once(__DIR__ . '/../src/HTTP/HTTPHeader.php');
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

		/**
		 * Test that non-string elements in a pre-compiled string are discarded.
		 */
		public function testNonStringElementsAreDiscarded() {
			$headers = new HTTPHeaders(\base64_encode('["X-Test: 1",5,{"a":2},["b"]]'));
			$this->assertEquals(\base64_encode('["X-Test: 1"]'), (string) $headers);
		}

		/**
		 * Test that serializing a header with invalid UTF-8 throws.
		 */
		public function testInvalidUTF8Throws() {
			$headers = new HTTPHeaders([new class extends HTTPHeader {
				/**
				 * Get the field name for this header.
				 *
				 * @api getFieldName
				 * @return string
				 */
				public function getFieldName(): string {
					return 'X-Test';
				}

				/**
				 * Get the field value for this header.
				 *
				 * @api getFieldValue
				 * @return string
				 */
				public function getFieldValue(): string {
					return "\xB1";
				}
			}]);

			$headers->compile();
			$this->expectException(\JsonException::class);
			(string) $headers;
		}
	}
