<?php
	use KrameWork\HTTP\Request\JSONRequest;
	use KrameWork\HTTP\Request\WebRequest;
	require_once(__DIR__ . '/../src/HTTP/Request/JSONRequest.php');

	/**
	 * Class JSONRequestTest
	 * Tests for the JSONRequest class.
	 */
	class JSONRequestTest extends PHPUnit\Framework\TestCase
	{
		/**
		 * Test that postJSON() throws when the object cannot be encoded as JSON.
		 */
		public function testPostJSONThrowsOnEncodeFailure() {
			$req = new JSONRequest('localhost', WebRequest::METHOD_POST);

			$this->expectException(\JsonException::class);
			$req->postJSON((object) ['value' => "\xB1"]);
		}
	}
