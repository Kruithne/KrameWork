<?php
	namespace KrameWork\Storage;

	/**
	 * Class MemoryFile
	 * In-memory file implementation.
	 *
	 * @package KrameWork\Storage
	 * @author Kruithne (kruithne@gmail.com)
	 */
	class MemoryFile extends File
	{
		/**
		 * MemoryFile constructor.
		 *
		 * @api __construct
		 * @param string|File $name File name, or another File instance to marshal.
		 * @param string $content Data for the file.
		 * @param string $contentType Data content-type.
		 */
		public function __construct(string|File $name, string $content, protected string $contentType = "text/plain") {
			$this->valid = true;
			$this->data = $content;

			parent::__construct($name, false, false);
		}

		/**
		 * Check if the file exists and is valid.
		 * A MemoryFile is valid until delete() is called.
		 *
		 * @api isValid
		 * @return bool
		 */
		public function isValid(): bool {
			return $this->valid;
		}

		/**
		 * Get the size of this file in bytes.
		 *
		 * @api getSize
		 * @return int
		 */
		public function getSize(): int {
			return \strlen($this->data ?? '');
		}

		/**
		 * Get the content type of this file.
		 *
		 * @api getFileType
		 * @return string
		 */
		public function getFileType(): string {
			return $this->contentType;
		}

		/**
		 * Get the data contained in this memory file.
		 *
		 * @api getData
		 * @param bool $forceRead No effect, included for implementation sake.
		 * @return null|string
		 */
		public function getData(bool $forceRead = false): ?string {
			return $this->data;
		}

		/**
		 * Set the data contained in this memory file.
		 *
		 * @api setData
		 * @param string|null $data Data to store in the wrapper.
		 */
		public function setData(?string $data): void {
			$this->data = $data;
		}

		/**
		 * 'Delete' the memory file, erasing the content from the
		 * container and marking the instance as invalid.
		 *
		 * @api delete
		 * @return bool
		 */
		public function delete(): bool {
			$this->valid = false;
			$this->data = null;
			return true;
		}

		/**
		 * Retrieve the raw data of this file encoded as base64.
		 *
		 * @api getBase64Data
		 * @param bool $forceRead No effect, included for implementation sake.
		 * @return string
		 */
		public function getBase64Data(bool $forceRead = false): string {
			return \base64_encode($this->data ?? '');
		}

		/**
		 * Copy the state of another File instance to this instance.
		 *
		 * @api marshalFrom
		 * @param File $file
		 */
		public function marshalFrom(File $file): void {
			parent::marshalFrom($file);

			$this->contentType = $file->getFileType();
			$this->valid = $file->isValid();
		}

		/**
		 * @var bool
		 */
		protected bool $valid;
	}