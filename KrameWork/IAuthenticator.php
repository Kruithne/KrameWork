<?php
	// Based on the library PHPGangsta_GoogleAuthenticator licensed under BSD by Michael Kliewe
	interface IAuthenticator
	{
		/**
		 * Create new secret.
		 * 16 characters, randomly chosen from the allowed base32 characters.
		 *
		 * @param int $secretLength
		 * @return string
		 */
		public function createSecret($secretLength = 16);

		/**
		 * Calculate the code, with given secret and point in time
		 *
		 * @param string $secret
		 * @param int|null $timeSlice
		 * @return string
		 */
		public function getCode($secret, $timeSlice = null);

		/**
		 * Get QR-Code URL for image, from google charts
		 *
		 * @param string $name
		 * @param string $secret
		 * @param string $title
		 * @return string
		 */
		public function getQRCodeGoogleUrl($name, $secret, $title = null);

		/**
		 * Check if the code is correct. This will accept codes starting from $discrepancy*30sec ago to $discrepancy*30sec from now
		 *
		 * @param string $secret
		 * @param string $code
		 * @param int $discrepancy This is the allowed time drift in 30 second units (8 means 4 minutes before or after)
		 * @param int|null $currentTimeSlice time slice if we want use other that time()
		 * @return bool
		 */
		public function verifyCode($secret, $code, $discrepancy = 1, $currentTimeSlice = null);

		/**
		 * Set the code length, should be >=6
		 *
		 * @param int $length
		 * @return PHPGangsta_GoogleAuthenticator
		 */
		public function setCodeLength($length);
	}
