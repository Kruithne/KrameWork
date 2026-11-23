<?php
	namespace KrameWork\Mailing;

	/**
	 * Class EncodedContent
	 * Encoded content container.
	 *
	 * @package KrameWork\Mailing
	 * @author Kruithne (kruithne@gmail.com)
	 */
	class EncodedContent
	{
		const E_BASE64 = 'base64';
		const E_QUOTED_PRINTABLE = 'quoted-printable';
		const E_7BIT = '7bit';

		/**
		 * EncodedContent constructor.
		 *
		 * @api __construct
		 * @param string $encoding Content encoding.
		 */
		public function __construct(string $encoding = self::E_7BIT) {
			$this->encoding = $encoding;
		}

		/**
		 * Set the content of this container.
		 *
		 * @api setContent
		 * @param string $content Content data.
		 */
		public function setContent($content) {
			$this->content = $content;
		}

		/**
		 * Check if this container has content.
		 *
		 * @api hasContent
		 * @return bool
		 */
		public function hasContent():bool {
			return !!$this->content;
		}

		/**
		 * Set the encoding of this content.
		 *
		 * @api setEncoding
		 * @param string $encoding Encoding to use for this content.
		 * @return bool
		 */
		public function setEncoding(string $encoding):bool{
			if ($encoding !== self::E_7BIT && $encoding !== self::E_BASE64 && $encoding !== self::E_QUOTED_PRINTABLE) {
				return false;
			}

			$this->encoding = $encoding;

			return true;
		}

		/**
		 * Get the encoding of this content.
		 *
		 * @api getEncoding
		 * @return string
		 */
		public function getEncoding() {
			return $this->encoding;
		}

		/**
		 * Encode and return the content.
		 *
		 * @api __toString
		 * @return string
		 * @link http://php.net/manual/en/language.oop5.magic.php#language.oop5.magic.tostring
		 */
		function __toString() {
			$content = $this->content;

			if ($this->encoding == self::E_BASE64)
				$content = \chunk_split(\base64_encode($content), 70, "\r\n");
			else if ($this->encoding == self::E_QUOTED_PRINTABLE)
				$content = \quoted_printable_encode($content);

			return $content;
		}


		/**
		 * @var string
		 */
		private $encoding;

		/**
		 * @var string
		 */
		private $content;
	}