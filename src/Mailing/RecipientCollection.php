<?php
	namespace KrameWork\Mailing;

	class InvalidRecipientException extends \Exception {}

	/**
	 * Class RecipientCollection
	 * Fluent API mail recipient container.
	 *
	 * @package KrameWork\Mailing
	 */
	class RecipientCollection
	{
		/**
		 * RecipientCollection constructor.
		 *
		 * @api __construct
		 */
		public function __construct() {
			$this->recipients = [];
		}

		/**
		 * Add a recipient (or multiple) to this collection.
		 * Arrays must be in an email=>name format (name can be null).
		 *
		 * @api add
		 * @param string|array<string, string|null> $email RFC 822 compliant e-mail address(es).
		 * @param null|string $name Name of the e-mail recipient.
		 * @param bool $encode Encode the e-mail recipient name.
		 * @return RecipientCollection
		 * @throws InvalidRecipientException
		 */
		public function add(string|array $email, ?string $name = null, bool $encode = true):RecipientCollection {
			// Array: Treat array as $email => $name key/value pair array.
			if (\is_array($email)) {
				foreach ($email as $nodeEmail => $nodeName)
					$this->add($nodeEmail, $nodeName, $encode);
			} else {
				// filter_var provides RFC 822 validation, mail() requires
				// RFC 2822 compliance, but this should be enough to catch
				// most issues.
				$validate = \filter_var($email, \FILTER_VALIDATE_EMAIL);
				if ($validate === false)
					throw new InvalidRecipientException('Invalid e-mail address (RFC 822)');

				// Encode name.
				if ($encode && $name !== null)
					$name = '=?UTF-8?B?' . \base64_encode($name) . '?=';

				// Add the recipient to the stack.
				$this->recipients[strval($email)] = $name;
			}
			return $this;
		}

		/**
		 * Remove a recipient (or multiple) from this collection.
		 *
		 * @api remove
		 * @param string|string[] $email E-mail address(es) to remove.
		 * @return RecipientCollection
		 */
		public function remove(string|array $email):RecipientCollection {
			// Array: Treat all elements as individual recipients.
			if (\is_array($email)) {
				foreach ($email as $node)
					$this->remove($node);
			} else {
				$email = (string) $email; // Ensure we have a string.

				// Delete the recipient from the stack.
				if (\array_key_exists($email, $this->recipients))
					unset($this->recipients[$email]);
			}

			return $this;
		}

		/**
		 * Clear all recipients from this collection.
		 *
		 * @api clear
		 * @return RecipientCollection
		 */
		public function clear():RecipientCollection {
			$this->recipients = [];
			return $this;
		}

		/**
		 * Check if this collection contains no recipients.
		 *
		 * @api isEmpty
		 * @return bool
		 */
		public function isEmpty():bool {
			return !$this->recipients;
		}

		/**
		 * Get the e-mail addresses in this collection.
		 *
		 * @api getAddresses
		 * @return string[]
		 */
		public function getAddresses():array {
			return \array_map('strval', \array_keys($this->recipients));
		}

		/**
		 * Compile the recipients into a comma-separated string.
		 * Recipients without a name are written as an address only.
		 *
		 * @api __toString
		 * @return string
		 */
		public function __toString(): string {
			$result = [];
			foreach ($this->recipients as $email => $name)
				$result[] = $name === null ? '<' . $email . '>' : '"' . $name . '" <' . $email . '>';

			return \implode(',', $result);
		}

		/**
		 * @var array<string, string|null>
		 */
		private array $recipients;
	}