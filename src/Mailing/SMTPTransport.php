<?php
	namespace KrameWork\Mailing;

	require_once(__DIR__ . '/IMailTransport.php');

	class SMTPException extends \Exception {}

	/**
	 * Class SMTPTransport
	 * Delivers e-mail to an SMTP server.
	 * Keeps the connection open between sends until close() is called.
	 *
	 * @package KrameWork\Mailing
	 */
	class SMTPTransport implements IMailTransport
	{
		const SECURITY_TLS = 'tls';
		const SECURITY_STARTTLS = 'starttls';
		const SECURITY_NONE = 'none';

		/**
		 * SMTPTransport constructor.
		 *
		 * @api __construct
		 * @param string $host SMTP server host name.
		 * @param int $port SMTP server port.
		 * @param string|null $username Authentication user name. Null to skip authentication.
		 * @param string|null $password Authentication password.
		 * @param string $security Connection security, one of the SECURITY_ constants.
		 * @param int $timeout Connection and response timeout in seconds.
		 * @param string|null $clientName Host name sent with EHLO. Null to use the local host name.
		 */
		public function __construct(
			private string $host,
			private int $port = 465,
			private ?string $username = null,
			private ?string $password = null,
			private string $security = self::SECURITY_TLS,
			private int $timeout = 30,
			private ?string $clientName = null
		) {}

		/**
		 * Close the connection when the transport is destroyed.
		 *
		 * @api __destruct
		 */
		public function __destruct() {
			$this->close();
		}

		/**
		 * Deliver a compiled e-mail.
		 * Rejected recipients do not stop delivery to accepted recipients.
		 *
		 * @api send
		 * @param string $sender Envelope sender address.
		 * @param RecipientCollection $to Primary recipients.
		 * @param RecipientCollection $cc Carbon copy recipients.
		 * @param RecipientCollection $bcc Blind carbon copy recipients.
		 * @param string $subject Encoded subject.
		 * @param array<string, string> $headers Headers, without To, Cc, Bcc and Subject.
		 * @param string $body Compiled MIME body.
		 * @throws SMTPException
		 */
		public function send(string $sender, RecipientCollection $to, RecipientCollection $cc, RecipientCollection $bcc, string $subject, array $headers, string $body): void {
			$this->rejectedRecipients = [];

			try {
				if ($this->socket === null)
					$this->connect();

				$this->command('MAIL FROM:<' . $sender . '>', 250);

				$recipients = \array_unique([...$to->getAddresses(), ...$cc->getAddresses(), ...$bcc->getAddresses()]);
				foreach ($recipients as $recipient) {
					$this->write('RCPT TO:<' . $recipient . '>');
					[$code] = $this->readResponse();

					if ($code !== 250 && $code !== 251)
						$this->rejectedRecipients[] = $recipient;
				}

				if (\count($this->rejectedRecipients) === \count($recipients))
					throw new SMTPException('SMTP server rejected all recipients.');

				$this->command('DATA', 354);
				$this->command($this->compileMessage($to, $cc, $subject, $headers, $body) . "\r\n.", 250);
			} catch (SMTPException $e) {
				$this->disconnect();
				throw $e;
			}
		}

		/**
		 * Get the recipients that the SMTP server rejected in the last send.
		 *
		 * @api getRejectedRecipients
		 * @return string[]
		 */
		public function getRejectedRecipients():array {
			return $this->rejectedRecipients;
		}

		/**
		 * Send QUIT to the SMTP server and close the connection.
		 *
		 * @api close
		 */
		public function close(): void {
			if ($this->socket === null)
				return;

			try {
				$this->write('QUIT');
				$this->readResponse();
			} catch (SMTPException) {}

			$this->disconnect();
		}

		/**
		 * Connect, read the greeting, enable encryption and authenticate.
		 *
		 * @internal
		 * @throws SMTPException
		 */
		private function connect(): void {
			$scheme = $this->security === self::SECURITY_TLS ? 'tls://' : 'tcp://';
			$context = \stream_context_create(['ssl' => ['peer_name' => $this->host, 'verify_peer' => true, 'verify_peer_name' => true]]);

			$socket = @\stream_socket_client($scheme . $this->host . ':' . $this->port, $errorCode, $errorMessage, $this->timeout, \STREAM_CLIENT_CONNECT, $context);
			if ($socket === false)
				throw new SMTPException('Unable to connect to SMTP server: ' . $errorMessage);

			\stream_set_timeout($socket, $this->timeout);
			$this->socket = $socket;

			$this->expect(220);
			$capabilities = $this->hello();

			if ($this->security === self::SECURITY_STARTTLS) {
				if (!\array_key_exists('STARTTLS', $capabilities))
					throw new SMTPException('SMTP server does not support STARTTLS.');

				$this->command('STARTTLS', 220);
				if (@\stream_socket_enable_crypto($socket, true, \STREAM_CRYPTO_METHOD_TLS_CLIENT) !== true)
					throw new SMTPException('Unable to enable TLS on SMTP connection.');

				$capabilities = $this->hello();
			}

			if ($this->username !== null)
				$this->authenticate($capabilities);
		}

		/**
		 * Send EHLO and return the capabilities of the SMTP server.
		 *
		 * @internal
		 * @return array<string, string> Capability keyword => parameters.
		 * @throws SMTPException
		 */
		private function hello():array {
			$clientName = $this->clientName ?? (\gethostname() ?: 'localhost');
			$lines = \explode("\n", $this->command('EHLO ' . $clientName, 250));

			$capabilities = [];
			foreach (\array_slice($lines, 1) as $line) {
				$parts = \explode(' ', \strtoupper(\substr($line, 4)), 2);
				$capabilities[$parts[0]] = $parts[1] ?? '';
			}

			return $capabilities;
		}

		/**
		 * Authenticate with AUTH PLAIN, or AUTH LOGIN if PLAIN is not offered.
		 *
		 * @internal
		 * @param array<string, string> $capabilities Capabilities of the SMTP server.
		 * @throws SMTPException
		 */
		private function authenticate(array $capabilities): void {
			$mechanisms = \explode(' ', $capabilities['AUTH'] ?? '');
			$username = $this->username ?? '';
			$password = $this->password ?? '';

			if (\in_array('PLAIN', $mechanisms, true)) {
				$this->command('AUTH PLAIN ' . \base64_encode("\0" . $username . "\0" . $password), 235);
			} elseif (\in_array('LOGIN', $mechanisms, true)) {
				$this->command('AUTH LOGIN', 334);
				$this->command(\base64_encode($username), 334);
				$this->command(\base64_encode($password), 235);
			} else {
				throw new SMTPException('SMTP server does not offer AUTH PLAIN or AUTH LOGIN.');
			}
		}

		/**
		 * Compile the message headers and body for the DATA command.
		 * Bcc recipients are not written to the headers.
		 *
		 * @internal
		 * @param RecipientCollection $to Primary recipients.
		 * @param RecipientCollection $cc Carbon copy recipients.
		 * @param string $subject Encoded subject.
		 * @param array<string, string> $headers Headers, without To, Cc, Bcc and Subject.
		 * @param string $body Compiled MIME body.
		 * @return string
		 */
		private function compileMessage(RecipientCollection $to, RecipientCollection $cc, string $subject, array $headers, string $body):string {
			$lines = ['To: ' . $to];
			if (!$cc->isEmpty())
				$lines[] = 'Cc: ' . $cc;

			$lines[] = 'Subject: ' . $subject;

			if (!\array_key_exists('Date', $headers))
				$lines[] = 'Date: ' . \date(\DATE_RFC2822);

			foreach ($headers as $name => $value)
				$lines[] = $name . ': ' . $value;

			$message = \implode("\r\n", $lines) . "\r\n\r\n" . $body;
			$message = \preg_replace('/\r?\n/', "\r\n", $message) ?? $message;

			return \preg_replace('/^\./m', '..', $message) ?? $message;
		}

		/**
		 * Send a command and check the response code.
		 *
		 * @internal
		 * @param string $command Command to send.
		 * @param int $expected Expected response code.
		 * @return string Response text.
		 * @throws SMTPException
		 */
		private function command(string $command, int $expected):string {
			$this->write($command);
			return $this->expect($expected);
		}

		/**
		 * Read a response and check the response code.
		 *
		 * @internal
		 * @param int $expected Expected response code.
		 * @return string Response text.
		 * @throws SMTPException
		 */
		private function expect(int $expected):string {
			[$code, $response] = $this->readResponse();
			if ($code !== $expected)
				throw new SMTPException('Unexpected SMTP response: ' . $response);

			return $response;
		}

		/**
		 * Write a line to the SMTP server.
		 *
		 * @internal
		 * @param string $line Line to write, without line ending.
		 * @throws SMTPException
		 */
		private function write(string $line): void {
			if ($this->socket === null)
				throw new SMTPException('SMTP connection is not open.');

			$data = $line . "\r\n";
			while ($data !== '') {
				$written = @\fwrite($this->socket, $data);
				if ($written === false || $written === 0)
					throw new SMTPException('Unable to write to SMTP server.');

				$data = \substr($data, $written);
			}
		}

		/**
		 * Read a single or multi-line response from the SMTP server.
		 *
		 * @internal
		 * @return array{int, string} Response code and response text.
		 * @throws SMTPException
		 */
		private function readResponse():array {
			if ($this->socket === null)
				throw new SMTPException('SMTP connection is not open.');

			$lines = [];
			do {
				$line = \fgets($this->socket);
				if ($line === false)
					throw new SMTPException('SMTP server closed the connection or did not respond.');

				$line = \rtrim($line, "\r\n");
				$lines[] = $line;
			} while (\strlen($line) > 3 && $line[3] === '-');

			return [(int) \substr($line, 0, 3), \implode("\n", $lines)];
		}

		/**
		 * Close the socket without sending QUIT.
		 *
		 * @internal
		 */
		private function disconnect(): void {
			if ($this->socket !== null)
				\fclose($this->socket);

			$this->socket = null;
		}

		/**
		 * @var resource|null
		 */
		private $socket = null;

		/**
		 * @var string[]
		 */
		private array $rejectedRecipients = [];
	}
