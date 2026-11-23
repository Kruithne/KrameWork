<?php
	namespace KrameWork\Mailing;

	use KrameWork\Utils\StringBuilder;
	require_once(__DIR__ . "/../Utils/StringBuilder.php");

	interface IMailPart {
		/**
		 * Compile this multipart block into the given StringBuilder.
		 *
		 * @api
		 * @param StringBuilder|null $builder Compilation target.
		 * @param IMailPart|null $parent Parent multipart block.
		 * @return StringBuilder|null
		 */
		public function compile(StringBuilder $builder, IMailPart $parent);
	}

	class InvalidParentException extends \Exception {}

	/**
	 * Class MailMultipart
	 * MIME Multipart Boundary Block
	 *
	 * @package KrameWork\Mailing
	 * @author Kruithne (kruithne@gmail.com)
	 */
	class MailMultipart implements IMailPart
	{
		/**
		 * MailMultipart constructor.
		 *
		 * @api
		 * @param string $type Multipart type, such as 'mixed' or 'alternative'.
		 * @param MailMultipart $parent Parent of this multipart block.
		 */
		public function __construct(string $type, ?MailMultipart $parent = null) {
			$this->parts = [];
			$this->type = $type;
			$this->boundaryID = \uniqid('==_mimepart_' . self::$id++);

			if ($parent)
				$parent->add($this);
		}

		/**
		 * Get the boundary ID for this multipart block.
		 *
		 * @api
		 * @return string
		 */
		public function getBoundaryID():string {
			return $this->boundaryID;
		}

		/**
		 * Add another multipart block to this one.
		 *
		 * @api
		 * @param IMailPart $part Block to add to this.
		 */
		public function add(IMailPart $part) {
			$this->parts[] = $part;
		}

		/**
		 * Get the content-type of this block, with trailing boundary ID.
		 *
		 * @api
		 * @return string
		 */
		public function getContentType():string {
			return 'multipart/' . $this->type . '; boundary="' . $this->boundaryID . '"';
		}

		/**
		 * Compile this multipart block into the given StringBuilder.
		 *
		 * @api
		 * @param StringBuilder|null $builder Compilation target.
		 * @param IMailPart|null $parent Parent multipart block.
		 * @return StringBuilder|null
		 * @throws InvalidParentException
		 */
		public function compile(?StringBuilder $builder = null, ?IMailPart $parent = null) {
			$builder = $builder ?? new StringBuilder(StringBuilder::LE_UNIX);

			if ($parent) {
				if (!($parent instanceof MailMultipart))
					throw new InvalidParentException('MailMultipart parent needs to be MailMultipart');

				$builder->appendLine('--' . $parent->getBoundaryID());
				$builder->appendLine('Content-Type: ' . $this->getContentType());
				$builder->newLine();
			}

			foreach ($this->parts as $part)
				$part->compile($builder, $this);

			$builder->appendLine('--' . $this->boundaryID . '--');

			return $builder;
		}

		/**
		 * @var IMailPart[]
		 */
		private $parts;

		/**
		 * @var string
		 */
		private $type;

		/**
		 * @var string
		 */
		private $boundaryID;

		/**
		 * @var int
		 */
		private static $id = 1;
	}