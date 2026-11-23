<?php
	namespace KrameWork\Mailing;

	use KrameWork\Storage\File;
	require_once(__DIR__ . '/../Storage/File.php');

	/**
	 * Class AttachmentFile
	 * Wrapper for attachable files.
	 *
	 * @package KrameWork\Mailing
	 * @author Kruithne (kruithne@gmail.com)
	 */
	class AttachmentFile extends File
	{
		/**
		 * Set if this attachment should be embedded inline.
		 *
		 * @api setInline
		 * @param bool $inline Embed attachment inline.
		 */
		public function setInline(bool $inline): void {
			$this->isInline = $inline;
		}

		/**
		 * Check if this attachment should be inline.
		 *
		 * @api isInline
		 * @return bool
		 */
		public function isInline():bool {
			return $this->isInline;
		}

		/**
		 * Set the content ID of this attachment.
		 *
		 * @api setContentID
		 * @param string $id CID for this attachment.
		 */
		public function setContentID(string $id): void {
			$this->contentID = $id;
		}

		/**
		 * Get the content ID of this attachment.
		 *
		 * @api getContentID
		 * @return string
		 */
		public function getContentID():string {
			return $this->contentID ?? $this->getName();
		}

		/**
		 * @var bool|null
		 */
		private ?bool $isInline = null;

		/**
		 * @var string|null
		 */
		private ?string $contentID = null;
	}