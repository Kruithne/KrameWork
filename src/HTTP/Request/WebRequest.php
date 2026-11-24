<?php
	namespace KrameWork\HTTP\Request;

	use KrameWork\HTTP\HTTPHeader;

	class ResponseNotAvailableException extends \Exception {}

	/**
	 * Class WebRequest
	 * Create and invoke a HTTP(s) request.
	 *
	 * @package KrameWork\HTTP
	 * @author Kruithne <kruithne@gmail.com>
	 */
	class WebRequest
	{
		const METHOD_POST = 'POST';
		const METHOD_GET = 'GET';

		/**
		 * WebRequest constructor.
		 *
		 * @api __construct
		 * @param string $url Request endpoint.
		 * @param string $method Request method (use class constants).
		 */
		public function __construct(protected string $url, protected string $method = self::METHOD_GET) {
			$this->data = [];
			$this->headers = ['Content-type' => 'application/x-www-form-urlencoded'];
		}

		/**
		 * Add a header to this request.
		 *
		 * @api addHeader
		 * @param string $fieldName Field name of the header.
		 * @param string $fieldValue Field value of the header.
		 */
		public function setHeader(string $fieldName, string $fieldValue): void {
			$this->headers[$fieldName] = $fieldValue;
		}

		/**
		 * Add a HTTP header object to the request.
		 *
		 * @api addHeaderObject
		 * @param HTTPHeader $header Header object to add.
		 */
		public function setHeaderObject(HTTPHeader $header): void {
			$this->setHeader($header->getFieldName(), $header->getFieldValue());
		}

		/**
		 * Add multiple headers to the request.
		 * Array must be in fieldName => fieldValue format.
		 *
		 * @api addHeaders
		 * @param array<string, string> $headers Array of headers to add.
		 */
		public function setHeaders(array $headers): void {
			foreach ($headers as $fieldName => $fieldValue)
				$this->setHeader($fieldName, $fieldValue);
		}

		/**
		 * Check if this request has a header set.
		 *
		 * @api hasHeader
		 * @param string $checkName Field name to check for.
		 * @return bool
		 */
		public function hasHeader(string $checkName):bool {
			return $this->getHeader($checkName) !== null;
		}

		/**
		 * Get a header set on this request.
		 * Returns a two-value array (fieldName, fieldValue) or null.
		 *
		 * @api getHeader
		 * @param string $getName Field name of the header.
		 * @return string[]|null
		 */
		public function getHeader(string $getName): ?array {
			$getName = \strtolower($getName);
			foreach ($this->headers as $fieldName => $fieldValue)
				if (\strtolower($fieldName) == $getName)
					return [$fieldName, $fieldValue];

			return null;
		}

		/**
		 * Get a value set for this request.
		 *
		 * @param string $name Name of the value.
		 * @return mixed
		 * @link http://php.net/manual/en/language.oop5.overloading.php#language.oop5.overloading.members
		 */
		function __get(string $name): mixed {
			return $this->data[$name] ?? null;
		}

		/**
		 * run when writing data to inaccessible members.
		 *
		 * @param string $name Name of the value.
		 * @param mixed $value Value itself.
		 * @link http://php.net/manual/en/language.oop5.overloading.php#language.oop5.overloading.members
		 */
		function __set(string $name, mixed $value): void {
			if (\is_bool($value))
				$value = $value ? 'true' : 'false';

			$this->data[$name] = $value;
		}

		/**
		 * Send the request.
		 * Return boolean indicates success.
		 *
		 * @api send
		 * @return bool
		 */
		public function send():bool {
			return $this->sendRequest($this->url, \http_build_query($this->data));
		}

		/**
		 * Get the compiled headers for this request.
		 *
		 * @internal
		 * @return string
		 */
		protected function compileHeaders():string {
			$headers = '';
			foreach ($this->headers as $fieldName => $fieldValue)
				$headers .= $fieldName . ': ' . $fieldValue . "\r\n";

			return $headers;
		}

		/**
		 * Send a HTTP request.
		 *
		 * @param string $url URL endpoint to request.
		 * @param string $content Content to send with the request.
		 * @return bool
		 */
		protected function sendRequest(string $url, string $content): bool {
			$params = ['method' => $this->method];

			if ($this->method == self::METHOD_GET) {
				if (\strlen($content)) {
					$url .= (\str_contains($url, '?') ? '&' : '?');
					$url .= $content;
				}
			} else if ($this->method == self::METHOD_POST) {
				$params['content'] = $content;
				$this->setHeader('Content-length', (string) \strlen($content));
			}

			$params['header'] = $this->compileHeaders();

			$result = \file_get_contents($url, false, \stream_context_create(['http' => $params]));
			$this->success = $result !== false;

			if ($result !== false)
				$this->result = $result;

			return $this->success;
		}

		/**
		 * Get the response from this request.
		 *
		 * @api getResponse
		 * @return mixed
		 * @throws ResponseNotAvailableException
		 */
		public function getResponse(): mixed {
			if ($this->result === null)
				throw new ResponseNotAvailableException('Response not available (request failed/not sent)');

			return $this->result;
		}

		/**
		 * True/false depending on success of request.
		 * Returns null if request has not yet been sent.
		 *
		 * @api success
		 * @return bool|null
		 */
		public function success(): ?bool {
			return $this->success;
		}

		/**
		 * Return the response for this request.
		 *
		 * @api __toString
		 * @return string
		 * @throws ResponseNotAvailableException
		 * @link http://php.net/manual/en/language.oop5.magic.php#language.oop5.magic.tostring
		 */
		function __toString():string {
			if ($this->result === null)
				throw new ResponseNotAvailableException('Response not available (request failed/not sent)');

			return $this->result;
		}

		/**
		 * @var null|bool
		 */
		protected ?bool $success = null;

		/**
		 * @var string|null
		 */
		protected ?string $result = null;

		/**
		 * @var array<string, mixed>
		 */
		protected array $data;

		/**
		 * @var array<string, string>
		 */
		protected array $headers;
	}