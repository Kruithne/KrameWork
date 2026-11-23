<?php
	namespace KrameWork\Database;

	/**
	 * Class for a encrypted database connection string
	 * @author docpify <morten@runsafe.no>
	 * @package KrameWork\Database
	 */
	class EncryptedConnection extends ConnectionString
	{
		/**
		 * @api __toString
		 * @return string The decrypted connection string
		 */
		public function __toString(): string {
			return self::decrypt($this->connection);
		}

		/**
		 * Encrypt a connection string
		 * @api encrypt
		 * @param string $dsn A connection string
		 * @param int $type Encryption format
		 * @return string An encrypted connection string
		 */
		public static function encrypt(string $dsn, int $type): string {
			$salt = \base64_encode(\random_bytes(16));
			if ($type == 1)
				return \base64_encode('H1$' . $salt . '$' . \openssl_encrypt($dsn, 'AES-256-ECB', self::getKey(1, $salt)));
			return $dsn;
		}

		/**
		 * Decrypt a connection string
		 * @param string $dsn An encrypted connection string
		 * @return string The decrypted connection string
		 * @throws \Exception If decryption fails
		 */
		public static function decrypt(string $dsn): string {
			$dsn = \base64_decode($dsn);
			if (\preg_match('/^H(\\d)\\$([^$]*)\\$(.*)$/', $dsn, $matches)) {
				$type = (int)$matches[1];
				$salt = $matches[2];
				$payload = $matches[3];
				$key = self::getKey($type, $salt);
				$decrypted = \openssl_decrypt($payload, 'AES-256-ECB', $key);
				if ($decrypted === false)
					throw new \Exception('Unable to decrypt connection string');

				return $decrypted;
			}
			return $dsn;
		}

		/**
		 * Generate the cypher key
		 * @param int $type Encryption type
		 * @param string $salt A generated salt
		 * @return string An encryption key in base 64 format
		 * @throws \Exception If type is not 1
		 */
		private static function getKey(int $type, string $salt): string {
			if ($type != 1)
				throw new \Exception("Unrecognized connection string encoding");

			$in = [4, 8, 6, 32, 99, 21, 2];
			$out = [];
			foreach ($in as $k => $v)
				$out[] = $k * sin($v);

			return \base64_encode(\sha1($salt . \json_encode($out), true));
		}
	}