<?php
	namespace KrameWork\Storage;

	require_once(__DIR__ . "/File.php");

	/**
	 * Class UploadedFile
	 * Wrapper class for easily managing uploaded files.
	 *
	 * @package KrameWork\Storage
	 * @author Kruithne (kruithne@gmail.com)
	 */
	class UploadedFile extends File
	{
		/**
		 * UploadedFile constructor.
		 *
		 * @api __construct
		 * @param string $source|File Temporary location, or another File instance.
		 * @param string $name Uploaded name.
		 * @param int $errorCode Upload error code.
		 */
		public function __construct($source, string $name, int $errorCode) {
			$this->name = $name;
			$this->errorCode = $errorCode;

			parent::__construct($source, false, false);
		}

		/**
		 * Check if the uploaded file is valid.
		 *
		 * @api isValid
		 * @return bool
		 */
		public function isValid(): bool {
			return $this->errorCode === \UPLOAD_ERR_OK && parent::isValid();
		}

		/**
		 * Get the error code for this upload.
		 *
		 * @api getErrorCode
		 * @return int
		 */
		public function getErrorCode():int {
			return $this->errorCode;
		}

		/**
		 * Copy the state of another File instance to this instance.
		 *
		 * @api marshalFrom
		 * @param File $file
		 */
		public function marshalFrom(File $file) {
			parent::marshalFrom($file);

			if ($file instanceof UploadedFile)
				$this->errorCode = $file->errorCode;
		}


		/**
		 * @var int Error code for the upload.
		 */
		protected $errorCode;
	}