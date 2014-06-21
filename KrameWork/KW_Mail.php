<?php
	class KW_Mail extends StringBuilder
	{
		/**
		 * Add a recipient who will receive this mail.
		 *
		 * @param string|array $recipients The address of the recipient.
		 * @return KW_Mail $this Instance of the object mail.
		 */
		public function addRecipient($recipients)
		{
			if (is_array($recipients))
				$this->recipients = array_merge($this->recipients, $recipients);
			else
				$this->recipients[] = $recipients;

			return $this;
		}

		/**
		 * Load the text from a file.
		 * @param string $file File to load.
		 */
		public function loadFromFile($file)
		{
			$this->append(file_get_contents($file));
		}

		/**
		 * Get the amount of recipients this mail will be sent to.
		 *
		 * @return int Amount of recipients.
		 */
		public function getRecipientCount()
		{
			return count($this->recipients);
		}

		/**
		 * Set the subject of the mail to be sent.
		 *
		 * @param string $subject The subject for the mail.
		 * @return KW_Mail $this Instance of the object mail.
		 */
		public function setSubject($subject)
		{
			$this->setHeader('Subject', $subject);
			return $this;
		}

		/**
		 * Set the address for which this mail originated from.
		 *
		 * @param string $sender The address of the sender.
		 * @return KW_Mail $this Instance of the object mail.
		 */
		public function setSender($sender)
		{
			$this->setHeader("From", $sender);
			return $this;
		}

		/**
		 * Set a header to be used when sending this mail.
		 *
		 * @param string $header The header to set.
		 * @param string $value The value of the header.
		 * @return KW_Mail $this Instance of the object mail.
		 */
		public function setHeader($header, $value)
		{
			$this->headers[$header] = $value;
			return $this;
		}

		/**
		 * Send this mail.
		 *
		 * @throws KW_Exception
		 */
		public function send()
		{
			if (!count($this->recipients))
				throw new KW_Exception("Mail cannot be sent without recipients");

			if (!array_key_exists('From', $this->headers))
				throw new KW_Exception("You must set a sender.");

			$headers = Array();
			foreach ($this->headers as $header => $value)
				$headers[] = $header . ': ' . $value;

			$headers[] = 'To: ' . implode(';', $this->recipients);

			$conn = new KW_SMTP(self::$host, self::$port);

			if ($conn === FALSE)
				throw new KW_Exception("Unable to connect to SMTP mailer.");

			$commands = Array();

			$commands[] = 0; // Poke
			$commands[] = 'EHLO ' . self::$host; // Greeting

			if (self::$auth_user !== NULL)
			{
				$commands[] = 'AUTH LOGIN';
				$commands[] = base64_encode(self::$auth_user);
				$commands[] = base64_encode(self::$auth_pass);
			}

			$commands[] = 'MAIL FROM: ' . $this->headers['From'];

			foreach ($this->recipients as $recipient)
				$commands[] = 'RCPT TO: ' . $recipient;

			$commands[] = 'DATA';
			$commands[] = implode("\r\n" . $headers);
			$commands[] = 'QUIT';

			$conn->listen(); // Poke.

			foreach ($commands as $command)
			{
				$command && $conn->talk($command);
				while ($conn->listen() !== NULL){};
			}
		}

		/**
		 * Set which host to use for SMTP mailing.
		 * @param string $host
		 */
		public static function setHost($host)
		{
			self::$host = $host;
		}

		/**
		 * Set which port to use for SMTP mailing.
		 * @param int $port
		 */
		public static function setPort($port)
		{
			self::$port = $port;
		}

		/**
		 * Set which user to authentication with for SMTP mailing.
		 * @param string $user
		 */
		public static function setAuthUser($user)
		{
			self::$auth_user = $user;
		}

		/**
		 * Set the password to use for SMTP mailing authentication.
		 * @param $pass
		 */
		public static function setAuthPass($pass)
		{
			self::$auth_pass = $pass;
		}

		/**
		 * @var string[]
		 */
		private $recipients = Array();

		/**
		 * @var array
		 */
		private $headers = Array();

		/**
		 * @var array
		 */

		/**
		 * @var int
		 */
		private static $port = 25;

		/**
		 * @var string
		 */
		private static $host = '127.0.0.1';

		/**
		 * @var string|null
		 */
		private static $auth_user;

		/**
		 * @var string|null
		 */
		private static $auth_pass;
	}
?>