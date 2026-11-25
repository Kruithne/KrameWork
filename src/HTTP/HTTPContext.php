<?php
	namespace KrameWork\HTTP;

	use KrameWork\Storage\JSONException;
	use KrameWork\Storage\JSONFile;
	use KrameWork\Storage\UploadedFile;

	class InvalidRequestTypeException extends \Exception {}
	class UnimplementedServerException extends \Exception {}

	/**
	 * Class HTTPContext
	 * @package KrameWork
	 */
	class HTTPContext
	{
		/**
		 * Obtain an array containing all files with the given key.
		 *
		 * @api getFiles
		 * @param string $key Key to lookup files for.
		 * @param bool $useWrappers Use KrameWork file wrappers.
		 * @return \ArrayObject<string, mixed>[]|\KrameWork\Storage\UploadedFile[]
		 */
		public static function getFiles(string $key, bool $useWrappers = true): array {
			$files = [];
			$node = $_FILES[$key] ?? null;

			// No files to return, lame.
			if (!\is_array($node))
				return $files;

			$names = (array) ($node['name'] ?? []);
			$types = (array) ($node['type'] ?? []);
			$paths = (array) ($node['tmp_name'] ?? []);
			$errors = (array) ($node['error'] ?? []);
			$sizes = (array) ($node['size'] ?? []);

			$count = \count($names);
			if ($count == 0) // Bork.
				return $files;

			if ($useWrappers)
				require_once(__DIR__ . '/../Storage/UploadedFile.php');

			for ($i = 0; $i < $count; $i++) {
				if ($useWrappers) {
					$path = $paths[$i] ?? null;
					$name = $names[$i] ?? null;
					$error = $errors[$i] ?? null;

					if (!\is_string($path) || !\is_string($name) || !\is_int($error))
						continue;

					$files[] = new UploadedFile($path, $name, $error);
				} else {
					$files[] = new \ArrayObject([
						'name' => $names[$i],
						'type' => $types[$i],
						'path' => $paths[$i],
						'error' => $errors[$i],
						'size' => $sizes[$i]
					], \ArrayObject::ARRAY_AS_PROPS);
				}
			}

			return $files;
		}

		/**
		 * Check if this request contains an uploaded file with the given key.
		 *
		 * @api hasFile
		 * @param string $key Key to check for.
		 * @return bool
		 */
		public static function hasFile(string $key): bool {
			return \array_key_exists($key, $_FILES);
		}

		/**
		 * Retrieve the decoded query string for this request.
		 *
		 * @api getQueryData
		 * @return array<int|string, mixed>
		 */
		public static function getQueryData(): array {
			if (self::$cacheQueryData)
				return self::$cacheQueryData;

			self::$cacheQueryData = self::extractURLEncodedData(self::getQueryString());
			return self::$cacheQueryData;
		}

		/**
		 * Retrieve a value from the query string with the given key.
		 *
		 * @api getQueryDataValue
		 * @param string $key Key contained in the query string.
		 * @return mixed|null
		 */
		public static function getQueryDataValue(string $key): mixed {
			return self::getQueryData()[$key] ?? null;
		}

		/**
		 * Retrieve multiple values from the query string.
		 * Accepts a variable amount of string arguments.
		 *
		 * @api getQueryDataValues
		 * @param string ...$keys Keys contained in the query string.
		 * @return mixed[]
		 */
		public static function getQueryDataValues(string ...$keys):array {
			$out = [];
			foreach ($keys as $key)
				$out[] = self::getQueryDataValue($key);

			return $out;
		}

		/**
		 * Check if this request contains form data.
		 * Occurs when content-type is multipart/form-data or application/x-www-form-urlencoded
		 *
		 * @api hasFormData
		 * @return bool
		 */
		public static function hasFormData():bool {
			return self::hasMultipartFormData() || self::getContentType(false) == 'application/x-www-form-urlencoded';
		}

		/**
		 * Check if this request contains multipart form data.
		 * Occurs when content-type is multipart/form-data
		 *
		 * @api hasMultipartFormData
		 * @return bool
		 */
		public static function hasMultipartFormData():bool {
			return self::getContentType(false) == 'multipart/form-data';
		}

		/**
		 * Retrieve a value from submitted form data with the given key.
		 *
		 * @api getFormDataValue
		 * @param string $key Key to get value for.
		 * @return mixed|null
		 */
		public static function getFormDataValue(string $key): mixed {
			return self::getFormData()[$key] ?? null;
		}

		/**
		 * Retrieve multiple values from submitted form data.
		 * Accepts variable amount of string arguments.
		 *
		 * @api getFormDataValues
		 * @param string ...$keys Keys to get values for.
		 * @return mixed[]
		 */
		public static function getFormDataValues(string ...$keys):array {
			$out = [];
			foreach ($keys as $key)
				$out[] = self::getFormDataValue($key);

			return $out;
		}

		/**
		 * Retrieve the content of the request as decoded form data.
		 * Result is stored in a key/value pair array.
		 * Array values (foo[]) are returned as arrays.
		 *
		 * @api getFormData
		 * @return array<int|string, mixed>
		 */
		public static function getFormData():array {
			if (self::$cacheFormData)
				return self::$cacheFormData;

			if (!self::hasFormData())
				return [];

			// If the content-type is multipart/form-data, then extracting the raw input manually is
			// not available, thus we resort to using $_POST, which luckily is more likely to contain
			// correct data in this scenario. During application/x-www-form-urlencoded requests, we
			// parse the raw data manually to avoid issues with $_POST being blank given valid yet
			// reserved characters within keys, or under certain server configurations.
			self::$cacheFormData = self::hasMultipartFormData() ? $_POST : self::extractURLEncodedData(self::getRequestContent());
			return self::$cacheFormData;
		}

		/**
		 * Retrieve the request content as (optionally decoded) JSON.
		 * Skipping decode and content-type validation: use getRequestContent() instead.
		 *
		 * @api getJSON
		 * @param bool $decode Parse the string and validate it.
		 * @param bool $ignoreContentType Skip validation of the content-type.
		 * @param bool $wrapper Wrap the decoded JSON object in a JSONFile wrapper.
		 * @return JSONFile|mixed|string
		 * @throws InvalidRequestTypeException
		 */
		public static function getJSON(bool $decode = true, bool $ignoreContentType = false, bool $wrapper = true): mixed {
			if (!$ignoreContentType && self::getContentType(false) != 'application/json')
				throw new InvalidRequestTypeException('Request content is not declared as application/json.');

			if ($wrapper) {
				require_once(__DIR__ . '/../Storage/JSONFile.php');
				try {
					$json = new JSONFile(null, true, false);
					$json->setRawData(self::getRequestContent());
					$json->read();
					return $json;
				} catch (JSONException $e) {
					throw new InvalidRequestTypeException('Request content did not contain valid json.');
				}
			} else {
				$content = self::getRequestContent();
				if ($decode) {
					$decoded = \json_decode($content);
					if (\json_last_error() !== JSON_ERROR_NONE)
						throw new InvalidRequestTypeException('Request content did not contain valid json.');

					return $decoded;
				}

				return $content;
			}
		}

		/**
		 * Check if this request was made over https protocol.
		 *
		 * @api isSecure
		 * @return bool
		 */
		public static function isSecure():bool {
			// ISAPI IIS returns "off", others do not set.
			return ($_SERVER['HTTPS'] ?? 'off') !== 'off';
		}

		/**
		 * Retrieve the raw content of the request.
		 * Result is not parsed or validated.
		 *
		 * @api getRequestContent
		 * @return string
		 */
		public static function getRequestContent():string {
			return (string) \file_get_contents('php://input');
		}

		/**
		 * Retrieve the length of the raw request content.
		 *
		 * @api getContentLength
		 * @return int
		 */
		public static function getContentLength():int {
			$length = $_SERVER['CONTENT_LENGTH'] ?? 0;
			return \is_numeric($length) ? \intval($length) : 0;
		}

		/**
		 * Retrieve the user-agent string for the current request.
		 * Returns 'Unknown' if not available.
		 *
		 * @api getUserAgent
		 * @return string
		 */
		public static function getUserAgent():string {
			return self::getServerString('HTTP_USER_AGENT', 'Unknown');
		}

		/**
		 * Retrieve the referer URL for this request.
		 * Returns an empty string if not available.
		 *
		 * @api getReferer
		 * @return string
		 */
		public static function getReferrer():string {
			return self::getServerString('HTTP_REFERER');
		}

		/**
		 * Retrieve the content-type of this request.
		 * Returns 'text/plain' if not available.
		 *
		 * @api getContentType
		 * @param bool $parameters Include content-type parameters.
		 * @return string
		 */
		public static function getContentType(bool $parameters = true):string {
			$type = self::getServerString('CONTENT_TYPE', 'text/plain');
			if (!$parameters) {
				$parting = \strpos($type, ';');
				if ($parting !== false)
					$type = \substr($type, 0, $parting);
			}
			return $type;
		}

		/**
		 * Retrieve the remote address for this request.
		 * Returns an empty string if not available.
		 *
		 * @api getRemoteAddress
		 * @return string
		 */
		public static function getRemoteAddress():string {
			return self::getServerString('REMOTE_ADDR');
		}

		/**
		 * Retrieve the URI of this request (includes query string).
		 * Returns an empty string if not available.
		 *
		 * @api getRequestURI
		 * @return string
		 */
		public static function getRequestURI():string {
			return self::getServerString('REQUEST_URI');
		}

		/**
		 * Retrieve the query string used in this request.
		 * Returns an empty string if not available.
		 *
		 * @api getQueryString
		 * @return string
		 */
		public static function getQueryString():string {
			return self::getServerString('QUERY_STRING');
		}

		/**
		 * Retrieve the method of this request.
		 * Defaults to 'GET' if not available.
		 *
		 * @api getRequestMethod
		 * @return string
		 */
		public static function getRequestMethod():string {
			return self::getServerString('REQUEST_METHOD', 'GET');
		}

		/**
		 * Returns request headers as a key/value array.
		 * All header names will be lowercase regardless of how they were sent.
		 * @return array<string, string>
		 */
		public static function getRequestHeaders():array {
			if (self::$cacheRequestHeaders)
				return self::$cacheRequestHeaders;

			$headers = [];
			if (\function_exists('apache_request_headers')) {
				// Apache
				foreach (\apache_request_headers() as $name => $value) {
					if (\is_string($value))
						$headers[\strtolower($name)] = $value;
				}
			} else {
				// NGINX
				foreach ($_SERVER as $name => $value) {
					if (\is_string($name) && \is_string($value) && \str_starts_with($name, 'HTTP_'))
						$headers[\str_replace('_', '-', \strtolower(\substr($name, 5)))] = $value;
				}
			}

			self::$cacheRequestHeaders = $headers;
			return $headers;
		}

		/**
		 * Retrieves a specific request header or NULL if it was not sent.
		 * @return string|null
		 */
		public static function getRequestHeader(string $key): ?string {
			return self::getRequestHeaders()[\strtolower($key)] ?? null;
		}

		public static function reset(): void {
			self::$cacheFormData = null;
			self::$cacheQueryData = null;
			self::$cacheRequestHeaders = null;
		}

		/**
		 * Decode a URL encoded key/value string.
		 *
		 * @internal
		 * @param string $input URL encoded string to decode.
		 * @return array<int|string, mixed> Decoded key/value pair array.
		 */
		private static function extractURLEncodedData(string $input):array {
			\mb_parse_str($input, $out);
			return $out;
		}

		/**
		 * Retrieve a $_SERVER value as a string.
		 *
		 * @internal
		 * @param string $key Key to read from $_SERVER.
		 * @param string $default Value to return if the key is missing or not a string.
		 * @return string
		 */
		private static function getServerString(string $key, string $default = ''):string {
			$value = $_SERVER[$key] ?? $default;
			return \is_string($value) ? $value : $default;
		}

		/**
		 * Internal cache for parsed form data.
		 * @var array<int|string, mixed>|null
		 */
		private static ?array $cacheFormData = null;

		/**
		 * Internal cache for parsed query data.
		 * @var array<int|string, mixed>|null
		 */
		private static ?array $cacheQueryData = null;

		/**
		 * Internal cache for request headers.
		 * @var array<string, string>|null
		 */
		private static ?array $cacheRequestHeaders = null;
	}