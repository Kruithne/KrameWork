<?php
	namespace KrameWork\Storage;

	require_once(__DIR__ . '/File.php');

	class JSONException extends \Exception {}

	/**
	 * Class JSONFile
	 * Wrapper class for easily managing JSON files.
	 *
	 * @package KrameWork\Storage
	 * @author Kruithne (kruithne@gmail.com)
	 */
	class JSONFile extends File
	{
		/**
		 * JSONFile constructor.
		 *
		 * @api __construct
		 * @param string|File|null $source Path to the file.
		 * @param bool $useContainer Loaded/inserted data will be contained using an ArrayObject.
		 * @param bool $autoLoad Attempt to read data from the file on instantiation.
		 * @throws JSONException
		 */
		public function __construct(string|File|null $source = null, protected bool $useContainer = true, bool $autoLoad = true) {
			if ($useContainer)
				$this->jsonData = new \ArrayObject([], \ArrayObject::ARRAY_AS_PROPS);

			parent::__construct($source, $autoLoad);
		}

		/**
		 * Returns true if a key exists in this JSON object.
		 * @return bool
		 */
		public function has(string $key):bool {
			$this->verifyDataObject();
			return isset($this->jsonData[$key]);
		}

		/**
		 * Obtain a value from the JSON container.
		 *
		 * @api __get
		 * @param string $key Key to lookup.
		 * @return mixed|null
		 * @throws JSONException
		 */
		public function __get(string $key): mixed {
			$this->verifyDataObject();
			return $this->jsonData[$key] ?? null;
		}

		/**
		 * Set the value of a specific key in the JSON container.
		 *
		 * @api __set
		 * @param string $key Key to store the value for.
		 * @param mixed $value Value to store at the given key.
		 * @throws JSONException
		 */
		public function __set(string $key, mixed $value): void {
			$this->verifyDataObject();
			$this->jsonData[$key] = $value;
		}

		/**
		 * Remove a value from the JSON container with the given key.
		 *
		 * @api __unset
		 * @param string $key Key to remove from the container.
		 */
		public function __unset(string $key): void {
			$this->verifyDataObject();
			unset($this->jsonData[$key]);
		}

		/**
		 * Attempt to read and decode JSON data from the file.
		 * Read data is not returned, but made available through the wrapper.
		 *
		 * @api read
		 * @throws JSONException
		 */
		public function read(): void {
			if ($this->path != null)
				parent::read();

			if ($this->data === null)
				throw new JSONException('Cannot decode JSON: No data given.');

			$decoded = \json_decode($this->data, $this->assoc, $this->depth, $this->options);
			if (\json_last_error() !== JSON_ERROR_NONE)
				$this->throwJSONError();

			$this->jsonData = $this->useContainer ? new \ArrayObject((array) $decoded,\ArrayObject::ARRAY_AS_PROPS) : $decoded;
		}

		/**
		 * Save the contents of the JSON container to a file.
		 * Using an alternative path to save will not change the original
		 * path stored by the wrapper.
		 *
		 * @api save
		 * @param string|null $file Path to save the file. If omitted, uses original file path.
		 * @param bool $overwrite Overwrite file if it exists.
		 * @throws JSONException
		 */
		public function save(?string $file = null, bool $overwrite = true): void {
			$encoded = \json_encode($this->jsonData);
			if ($encoded === false)
				$this->throwJSONError();

			$this->data = $encoded;
			parent::save($file, $overwrite);
		}

		/**
		 * Throw the latest JSON error as an exception.
		 *
		 * @internal
		 * @throws JSONException
		 */
		private function throwJSONError(): never {
			throw new JSONException('JSON error: ' . \json_last_error_msg());
		}

		/**
		 * Throw an exception if the internal data object is not initiated.
		 *
		 * @internal
		 * @throws JSONException
		 */
		private function verifyDataObject(): void {
			if ($this->jsonData === null)
				throw new JSONException('Attempt to invoke value on a non-initiated JSON file.');
		}

		/**
		 * Dispatch this as encoded data to the output buffer.
		 *
		 * @param bool $applyHeader If true, content-type header will be set.
		 */
		public function sendToBuffer(bool $applyHeader = true): void {
			if ($applyHeader)
				\header('Content-Type: application/json');

			echo \json_encode($this->getData());
		}

		/**
		 * Get the JSON container inside this wrapper.
		 * Returns a string if not using containers (see constructor).
		 *
		 * @api getData
		 * @param bool $forceRead Call read() if no data.
		 * @return mixed
		 */
		public function getData(bool $forceRead = false): mixed {
			if ($forceRead && $this->data === null)
				$this->read();

			return $this->jsonData;
		}

		/**
		 * Retrieve the JSON encoded data of this wrapper encoded as base64.
		 *
		 * @api getBase64Data
		 * @param bool $forceRead Call read() if no data.
		 * @return string
		 * @throws JSONException
		 */
		public function getBase64Data(bool $forceRead = false): string {
			$encoded = \json_encode($this->getData($forceRead));
			if ($encoded === false)
				$this->throwJSONError();

			return \base64_encode($encoded);
		}

		/**
		 * Set the data contained by this wrapper.
		 * Not recommended unless not using containers (see constructor).
		 *
		 * @api setData
		 * @param mixed $data JSON object.
		 */
		public function setData(mixed $data): void {
			$this->jsonData = $data;
		}

		/**
		 * Get the raw data string for this wrapper.
		 *
		 * @api getRawData
		 * @return string|null
		 */
		public function getRawData(): ?string {
			return $this->data;
		}

		/**
		 * Set the raw data string for this file.
		 *
		 * @api setRawData
		 * @param string $data Raw data.
		 */
		public function setRawData(string $data): void {
			$this->data = $data;
		}

		/**
		 * Set the recursion depth for file reading.
		 * Defaults to 512 if not set.
		 *
		 * @api setRecursionDepth
		 * @param int $depth Recursion depth.
		 */
		public function setRecursionDepth(int $depth): void {
			$this->depth = $depth;
		}

		/**
		 * Set if this file should read objects as associative arrays.
		 * Defaults to false if not set.
		 *
		 * @api setAssociative
		 * @param bool $assoc Associative state.
		 */
		public function setAssociative(bool $assoc): void {
			$this->assoc = $assoc;
		}

		/**
		 * Set the JSON options bit-mask.
		 *
		 * @api setOptions
		 * @param int $mask Bit-mask options flag.
		 */
		public function setOptions(int $mask): void {
			$this->options = $mask;
		}

		/**
		 * Copy the state of another File instance to this instance.
		 *
		 * @api marshalFrom
		 * @param File $file
		 */
		public function marshalFrom(File $file): void {
			parent::marshalFrom($file);

			if ($file instanceof JSONFile) {
				$this->jsonData = $file->jsonData;
				$this->useContainer = $file->useContainer;
				$this->depth = $file->depth;
				$this->assoc = $file->assoc;
				$this->options = $file->options;
			}
		}


		/**
		 * @var mixed
		 */
		protected mixed $jsonData = null;

		/**
		 * @var int Recursion depth.
		 */
		protected int $depth = 512;

		/**
		 * @var bool Convert objects into associative arrays.
		 */
		protected bool $assoc = false;

		/**
		 * @var int Bit-mask for JSON encoding options.
		 */
		protected int $options = 0;
	}