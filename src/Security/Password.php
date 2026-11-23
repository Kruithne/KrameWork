<?php
	namespace KrameWork\Security;
	require_once(__DIR__ . '/IMaskable.php');

	/**
	 * Class Password
	 * Represents a password.
	 *
	 * @package KrameWork\Security
	 * @author Kruithne <kruithne@gmail.com>
	 */
	class Password implements IMaskable
	{
		/**
		 * Password constructor.
		 *
		 * @api __construct
		 * @param string $value Plaintext password.
		 */
		public function __construct(private string $value) {}

		/**
		 * Return the password as an MD5 hash.
		 * Note: MD5 hashes are not secure for password storage.
		 *
		 * @api asMD5Hash
		 * @return string
		 */
		public function asMD5Hash():string {
			return \md5($this->value);
		}

		/**
		 * Get the masked value of this password.
		 *
		 * @api asMask
		 * @param string $char Mask character.
		 * @return string
		 */
		public function asMask(string $char = '*'): string {
			return \str_repeat($char, $this->length());
		}

		/**
		 * Get a one-way hash for this password.
		 *
		 * @api asHash
		 * @param string $algorithm Algorithm to hash with.
		 * @return string
		 */
		public function asHash(string $algorithm = \PASSWORD_DEFAULT):string {
			return \password_hash($this->value, $algorithm);
		}

		/**
		 * Verify this password matches a given hash.
		 *
		 * @api verify
		 * @param string $hash
		 * @return bool
		 */
		public function verify(string $hash):bool {
			return \password_verify($this->value, $hash);
		}

		/**
		 * Get the length of this password.
		 *
		 * @api length
		 * @return int
		 */
		public function length():int {
			return \strlen($this->value);
		}

		/**
		 * Get the plain-text password contained by this object.
		 *
		 * @api __toString
		 * @return string
		 * @link http://php.net/manual/en/language.oop5.magic.php#language.oop5.magic.tostring
		 */
		function __toString():string {
			return $this->value;
		}
	}