<?php
	namespace KrameWork\Utils;

	/**
	 * Class UUID
	 * UUID generation functions.
	 *
	 * @package KrameWork\Utils
	 * @author Andrew Moore (v3, v4, v5 base)
	 * @author Ben Ramsey <ben@benramsey.com> (class constants)
	 */
	class UUID
	{
		/**
		 * When this namespace is specified, the name string is a fully-qualified domain name.
		 * @link http://tools.ietf.org/html/rfc4122#appendix-C
		 */
		const NAMESPACE_DNS = '6ba7b810-9dad-11d1-80b4-00c04fd430c8';

		/**
		 * When this namespace is specified, the name string is a URL.
		 * @link http://tools.ietf.org/html/rfc4122#appendix-C
		 */
		const NAMESPACE_URL = '6ba7b811-9dad-11d1-80b4-00c04fd430c8';

		/**
		 * When this namespace is specified, the name string is an ISO OID.
		 * @link http://tools.ietf.org/html/rfc4122#appendix-C
		 */
		const NAMESPACE_OID = '6ba7b812-9dad-11d1-80b4-00c04fd430c8';

		/**
		 * When this namespace is specified, the name string is an X.500 DN in DER or a text output format.
		 * @link http://tools.ietf.org/html/rfc4122#appendix-C
		 */
		const NAMESPACE_X500 = '6ba7b814-9dad-11d1-80b4-00c04fd430c8';

		/**
		 * The nil UUID is special form of UUID that is specified to have all 128 bits set to zero.
		 * @link http://tools.ietf.org/html/rfc4122#section-4.1.7
		 */
		const NIL = '00000000-0000-0000-0000-000000000000';

		/**
		 * Generate an RFC 4122 compliant v3 (namespace based) UUID.
		 * Returns UUID::NIL when given an invalid namespace.
		 *
		 * @api generate_v3
		 * @param string $namespace UUID namespace.
		 * @param string $name UUID name.
		 * @return string
		 */
		public static function generate_v3(string $namespace, string $name):string {
			return self::generateNamespaceUUID($namespace, $name, 3);
		}

		/**
		 * Generate an RFC 4122 compliant v4 (pseudo-random) UUID.
		 *
		 * @api generate_v4
		 * @return string
		 */
		public static function generate_v4():string {
			$bytes = \random_bytes(16);
			$bytes[6] = \chr((\ord($bytes[6]) & 0x0f) | 0x40);
			$bytes[8] = \chr((\ord($bytes[8]) & 0x3f) | 0x80);

			return self::format($bytes);
		}

		/**
		 * Generate an RFC 4122 compliant v5 (namespace based) UUID.
		 * Returns UUID::NIL when given an invalid namespace.
		 *
		 * @api generate_v5
		 * @param string $namespace UUID namespace.
		 * @param string $name UUID name.
		 * @return string
		 */
		public static function generate_v5(string $namespace, string $name):string {
			return self::generateNamespaceUUID($namespace, $name, 5);
		}

		/**
		 * Generate an RFC 9562 compliant v7 (time-ordered) UUID.
		 *
		 * @api generate_v7
		 * @return string
		 */
		public static function generate_v7():string {
			$time = (int) (\microtime(true) * 1000);
			$bytes = \substr(\pack('J', $time), 2) . \random_bytes(10);
			$bytes[6] = \chr((\ord($bytes[6]) & 0x0f) | 0x70);
			$bytes[8] = \chr((\ord($bytes[8]) & 0x3f) | 0x80);

			return self::format($bytes);
		}

		/**
		 * Generate an RFC 4122 compliant namespace UUID.
		 * Currently supports v3 and v5.
		 * Returns UUID::NIL when given an invalid namespace or version.
		 *
		 * @internal
		 * @param string $namespace RFC 4122 compliant namespace UUID.
		 * @param string $name Name for generation.
		 * @param int $version UUID version.
		 * @return string
		 */
		private static function generateNamespaceUUID(string $namespace, string $name, int $version):string {
			// Supports v3 or v5 namespace UUID generation.
			if ($version != 3 && $version != 5)
				return self::NIL;

			// Ensure valid namespace UUID.
			if (!self::isValid($namespace))
				return self::NIL;

			$bin = \hex2bin(\str_replace(['-', '{', '}'], '', $namespace));
			$hash = $version == 3 ? \md5($bin . $name) : \sha1($bin . $name); // Hash.
			$versionBit = $version == 3 ? 0x3000 : 0x5000;

			return \sprintf('%08s-%04s-%04x-%04x-%12s',
				\substr($hash, 0, 8), // 32 bits for "time_low"
				\substr($hash, 8, 4), // 16 bits for "time_mid"
				(\hexdec(\substr($hash, 12, 4)) & 0x0fff) | $versionBit, // 16 bits for "time_hi_and_version", four most significant bits holds version.
				(\hexdec(\substr($hash, 16, 4)) & 0x3fff) | 0x8000, // 16 bits, 8 bits for "clk_seq_hi_res", 8 bits for "clk_seq_low", two most significant bits holds zero and one for variant DCE1.1
				\substr($hash, 20, 12) // 48 bits for "node"
			);
		}

		/**
		 * Format 16 bytes as a hyphenated UUID string.
		 *
		 * @internal
		 * @param string $bytes 16 bytes of UUID data.
		 * @return string
		 */
		private static function format(string $bytes):string {
			return \vsprintf('%s%s-%s-%s-%s-%s%s%s', \str_split(\bin2hex($bytes), 4));
		}

		/**
		 * Check if the given UUID is RFC 4122 compliant.
		 *
		 * @api isValid
		 * @param string $uuid UUID to validate.
		 * @return bool
		 */
		public static function isValid(string $uuid):bool {
			if (\preg_match('/^(\{)?[0-9a-f]{8}(-?)[0-9a-f]{4}\2[0-9a-f]{4}\2[0-9a-f]{4}\2[0-9a-f]{12}(?(1)\})$/i', $uuid) !== 1)
				return false;

			$hex = \strtolower(\str_replace(['-', '{', '}'], '', $uuid));
			if ($hex === \str_repeat('0', 32) || $hex === \str_repeat('f', 32))
				return true;

			return \preg_match('/^.{12}[1-8].{3}[89ab]/', $hex) === 1;
		}
	}