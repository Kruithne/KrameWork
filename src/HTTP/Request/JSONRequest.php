<?php
	namespace KrameWork\HTTP\Request;
	require_once(__DIR__ . '/WebRequest.php');

	class InvalidMethodException extends \Exception {}

	/**
	 * Class JSONRequest
	 * Create an invoke a HTTP(s) request with a JSON response.
	 *
	 * @package KrameWork\HTTP
	 * @author Kruithne <kruithne@gmail.com>
	 */
	class JSONRequest extends WebRequest
	{
		public function __construct($url, $method = self::METHOD_GET) {
			parent::__construct($url, $method);
			$this->setHeader('Accept', 'application/json');
		}

		/**
		 * Send the request.
		 * Return boolean indicates success.
		 *
		 * @api postJson
		 * @param mixed $object The data to encode as JSON and post to the server
		 * @return bool
		 * @throws InvalidMethodException
		 */
		public function postJSON(object $object):bool {
			if($this->method != WebRequest::METHOD_POST)
				throw new InvalidMethodException('JSON-body requests require METHOD_POST.');

			if (!$this->hasHeader('Content-type'))
				$this->setHeader('Content-type', 'application/json');

			$url = $this->url . (\strpos($this->url, '?') !== false ? '&' : '?');
			$url .= \http_build_query($this->data);

			$this->sendRequest($url, \json_encode($object));
			return $this->success;
		}

		/**
		 * Get the JSON decoded response from this request.
		 * Returns null if unable to decode response.
		 *
		 * @api getResponse
		 * @return mixed
		 * @throws ResponseNotAvailableException
		 */
		public function getResponse() {
			return \json_decode(parent::getResponse());
		}
	}