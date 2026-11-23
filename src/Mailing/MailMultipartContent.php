<?php
	namespace KrameWork\Mailing;

	use KrameWork\Utils\StringBuilder;
	require_once(__DIR__ . "/MailMultipart.php");

	/**
	 * Class MailMultipartContent
	 * MIME Multipart Content Block
	 *
	 * @package KrameWork\Mailing
	 * @author Kruithne (kruithne@gmail.com)
	 */
	class MailMultipartContent implements IMailPart
	{
		/**
		 * MailMultipartContent constructor.
		 *
		 * @api
		 * @param string $type Content-type (with trailing parameters).
		 * @param MailMultipart|null $parent
		 */
		public function __construct(string $type, ?MailMultipart $parent = null) {
			$this->headers = [];
			$this->content = '';
			$this->addHeader('Content-Type', $type);

			if ($parent)
				$parent->add($this);
		}

		/**
		 * Set the transfer encoding of this content block.
		 *
		 * @api
		 * @param string $encoding
		 */
		public function setTransferEncoding(string $encoding): void {
			$this->addHeader('Content-Transfer-Encoding', $encoding);
		}

		/**
		 * Set the content disposition for this content block.
		 * Examples: inline, attachment
		 *
		 * @api
		 * @param string $disposition
		 */
		public function setContentDisposition(string $disposition): void {
			$this->addHeader('Content-Disposition', $disposition);
		}

		/**
		 * Set the attachment/content ID for this block.
		 *
		 * @api
		 * @param string $id
		 */
		public function setAttachmentID(string $id): void {
			$this->addHeader('Content-ID', '<' . $id . '>');
			$this->addHeader('X-Attachment-Id', $id);
		}

		/**
		 * Set the content for this multipart content block.
		 *
		 * @api
		 * @param string $content Content for this block.
		 * @param bool $encodeBase64 Encode the content in base64.
		 */
		public function setContent(string $content, bool $encodeBase64 = false): void {
			if ($encodeBase64)
				$content = \chunk_split(\base64_encode($content), 70, "\r\n");

			$this->content = $content;
		}

		/**
		 * Add a header for this content block.
		 *
		 * @api
		 * @param string $name Name of the header.
		 * @param string $value Value of the header.
		 */
		public function addHeader(string $name, string $value): void {
			$this->headers[$name] = $value;
		}

		/**
		 * Compile this multipart into the given StringBuilder.
		 *
		 * @api
		 * @param StringBuilder $builder Compilation target.
		 * @param IMailPart $parent Parent of the block.
		 * @return void
		 * @throws InvalidParentException
		 */
		public function compile(StringBuilder $builder, IMailPart $parent): void {
			if (!($parent instanceof MailMultipart))
				throw new InvalidParentException('MailMultipartContent parent needs to be MailMultipart');

			$builder->appendLine('--' . $parent->getBoundaryID());
			foreach ($this->headers as $name => $value)
				$builder->appendLine($name . ': ' . $value);

			$builder->newLine()->appendLine($this->content);
		}

		/**
		 * @var array
		 */
		private array $headers;

		/**
		 * @var string
		 */
		private string $content;
	}