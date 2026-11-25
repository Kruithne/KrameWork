<?php
	namespace KrameWork\Storage;

	require_once(__DIR__ . '/DirectoryItem.php');

	class FileNotFoundException extends \Exception {}
	class FileReadException extends \Exception {}
	class FileWriteException extends \Exception {}

	/**
	 * Class File
	 * Wrapper class for easily managing file-system files.
	 *
	 * @package KrameWork\Storage
	 */
	class File extends DirectoryItem
	{
		/**
		 * File constructor.
		 * If an instance of File is provided as $source, the state of the provided
		 * instance will be cloned to this one. Both $autoLoad and $touch will be
		 * ignored in that scenario.
		 *
		 * @api __construct
		 * @param string|File|null $source Path to the file, or another File instance to clone.
		 * @param bool $autoLoad Attempt to load the file contents on instantiation.
		 * @param bool $touch Touch the file, creating it if missing.
		 * @throws FileNotFoundException
		 * @throws FileReadException
		 */
		public function __construct(string|File|null $source = null, bool $autoLoad = true, bool $touch = false) {
			if (self::$hasFileinfo === NULL) {
				self::$hasFileinfo = \extension_loaded('fileinfo');
			}

			if ($source instanceof File) {
				$this->marshalFrom($source);
			} elseif ($source !== null) {
				parent::__construct($source);

				if ($touch && !$this->exists())
					\file_put_contents($this->path, '');

				if ($autoLoad)
					$this->read();
			}
		}

		/**
		 * Check if the file exists and is valid.
		 *
		 * @api isValid
		 * @return bool
		 */
		public function isValid():bool {
			return $this->exists() && \is_file($this->path);
		}

		/**
		 * Attempt to delete the file.
		 *
		 * @api delete
		 * @return bool
		 */
		public function delete():bool {
			return @\unlink($this->path);
		}

		/**
		 * Get the size of this file in bytes.
		 *
		 * @api getSize
		 * @return int
		 */
		public function getSize():int {
			$size = @\filesize($this->path);
			return $size !== false ? $size : 0;
		}

		/**
		 * Get the extension of this file (without leading period).
		 *
		 * @api getExtension
		 * @return string
		 */
		public function getExtension():string {
			$parts = \explode(".", $this->name);
			$size = \count($parts);
			return $size > 1 ? $parts[$size - 1] : '';
		}

		/**
		 * Attempts to get the MIME type of a file.
		 * Requires the fileinfo extension to be enabled.
		 * Returns 'unknown' on failure.
		 *
		 * @api getFileType
		 * @return string
		 */
		public function getFileType():string {
			if (self::$hasFileinfo) {
				$info = new \finfo(\FILEINFO_MIME_TYPE);
				$type = $info->file($this->path);

				if ($type)
					return $type;
			}
			return 'unknown';
		}

		/**
		 * Attempt to read the data from a file.
		 * Read data is not returned, but available through the wrapper.
		 *
		 * @api read
		 * @throws FileNotFoundException
		 * @throws FileReadException
		 */
		public function read(): void {
			if ($this->path === '')
				throw new FileNotFoundException('Cannot resolve file: No path given.');

			if (!\is_file($this->path))
				throw new FileNotFoundException('Cannot resolve file: It does not exist.');

			$raw = \file_get_contents($this->path);
			if ($raw === false)
				throw new FileReadException('Unable to read data from file.');

			$this->data = $raw;
		}

		/**
		 * Attempt to save the data in the wrapper to a file.
		 * Using an alternative path to save will not change the original
		 * path stored by the wrapper.
		 *
		 * @api save
		 * @param string|null $file Path to save the file. If omitted, will use wrapper path.
		 * @param bool $overwrite Overwrite the file if it exists.
		 * @throws FileWriteException
		 */
		public function save(?string $file = null, bool $overwrite = true): void {
			$file = $file ?? $this->path;

			if (!$overwrite && \is_file($file))
				throw new FileWriteException('Cannot write file: Already exists (specify overwrite?)');

			if (\file_put_contents($file, $this->data ?? '') === false)
				throw new FileWriteException('Unable to write data to file.');
		}

		/**
		 * Get the data contained by the wrapper after a read() or manual set.
		 *
		 * @api getData
		 * @param bool $forceRead Call read() if data is missing.
		 * @return null|string
		 */
		public function getData(bool $forceRead = false): mixed {
			if ($forceRead && $this->data === null)
				$this->read();

			return $this->data;
		}

		/**
		 * Retrieve the raw data of this file encoded as base64.
		 *
		 * @api getBase64Data
		 * @param bool $forceRead Call read() if data is missing.
		 * @return string
		 */
		public function getBase64Data(bool $forceRead = false):string {
			if ($forceRead && $this->data === null)
				$this->read();

			return \base64_encode($this->data ?? '');
		}

		/**
		 * Set the data for this file wrapper. Overwrites existing.
		 *
		 * @api setData
		 * @param string|null $data Data to store in the wrapper.
		 */
		public function setData(?string $data): void {
			$this->data = $data;
		}

		/**
		 * Copy the state of another File instance to this instance.
		 *
		 * @api marshalFrom
		 * @param File $file
		 */
		public function marshalFrom(File $file): void {
			$this->name = $file->name;
			$this->path = $file->path;
			$this->data = $file->data;
		}

		/**
		 * Data loaded from the file.
		 * @var string|null
		 */
		protected ?string $data = null;

		/**
		 * Holds a check to see if fileinfo extension for PHP is loaded
		 *
		 * This is set only during the first call to this class constructor
		 *
		 * @var bool|null
		 */
		private static ?bool $hasFileinfo = null;
	}