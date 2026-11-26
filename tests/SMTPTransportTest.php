<?php
	require_once(__DIR__ . '/../src/Mailing/Mail.php');
	require_once(__DIR__ . '/../src/Mailing/SMTPTransport.php');

	use KrameWork\Mailing\Mail;
	use KrameWork\Mailing\SMTPException;
	use KrameWork\Mailing\SMTPTransport;

	class SMTPTransportTest extends PHPUnit\Framework\TestCase
	{
		/**
		 * Test that a Bcc recipient receives the mail but does not appear in the message headers.
		 */
		public function testBccRecipientIsNotWrittenToHeaders() {
			$transport = $this->createTransport();

			$mail = $this->createMail();
			$mail->bcc->add('hidden@bar.net');
			$mail->send($transport);
			$transport->close();

			$transcript = $this->readTranscript();
			$this->assertContains('RCPT TO:<hidden@bar.net>', $transcript, 'Bcc recipient was not sent to the server.');
			$this->assertEmpty(\preg_grep('/^Bcc:/i', $transcript), 'Bcc header was written to the message.');
		}

		/**
		 * Test that a body line that starts with a dot is escaped with a second dot.
		 */
		public function testBodyLineStartingWithDotIsEscaped() {
			$transport = $this->createTransport();

			$mail = $this->createMail();
			$mail->plainContent->setContent("first\n.second");
			$mail->send($transport);
			$transport->close();

			$this->assertContains('..second', $this->readTranscript(), 'Body line starting with a dot was not escaped.');
		}

		/**
		 * Test that a rejected recipient is reported and the mail is still sent to the other recipients.
		 */
		public function testRejectedRecipientIsReported() {
			$transport = $this->createTransport(rejected: 'missing@bar.net');

			$mail = $this->createMail();
			$mail->to->add('missing@bar.net');
			$mail->send($transport);
			$transport->close();

			$this->assertEquals(['missing@bar.net'], $transport->getRejectedRecipients(), 'Rejected recipient was not reported.');
			$this->assertContains('DATA', $this->readTranscript(), 'Mail was not sent to the accepted recipient.');
		}

		/**
		 * Test that sending throws an exception when the server rejects all recipients.
		 */
		public function testAllRecipientsRejectedThrows() {
			$transport = $this->createTransport(rejected: 'foo@bar.net');

			$this->expectException(SMTPException::class);
			$this->createMail()->send($transport);
		}

		/**
		 * Test that the transport authenticates with AUTH PLAIN when the server offers it.
		 */
		public function testAuthenticatesWithPlain() {
			$transport = $this->createTransport('PLAIN LOGIN', 'user', 'secret');

			$this->createMail()->send($transport);
			$transport->close();

			$this->assertContains('AUTH PLAIN ' . \base64_encode("\0user\0secret"), $this->readTranscript(), 'Transport did not authenticate with AUTH PLAIN.');
		}

		/**
		 * Test that the transport authenticates with AUTH LOGIN when the server does not offer AUTH PLAIN.
		 */
		public function testAuthenticatesWithLoginWhenPlainNotOffered() {
			$transport = $this->createTransport('LOGIN', 'user', 'secret');

			$this->createMail()->send($transport);
			$transport->close();

			$transcript = $this->readTranscript();
			$this->assertContains('AUTH LOGIN', $transcript, 'Transport did not authenticate with AUTH LOGIN.');
			$this->assertContains(\base64_encode('user'), $transcript, 'Transport did not send the user name.');
		}

		/**
		 * Test that sending throws an exception when the server rejects the credentials.
		 */
		public function testFailedAuthenticationThrows() {
			$transport = $this->createTransport('PLAIN', 'user', 'wrong');

			$this->expectException(SMTPException::class);
			$this->createMail()->send($transport);
		}

		/**
		 * Test that sending throws an exception when STARTTLS is required but the server does not offer it.
		 */
		public function testMissingStartTLSThrows() {
			$transport = $this->createTransport(security: SMTPTransport::SECURITY_STARTTLS);

			$this->expectException(SMTPException::class);
			$this->createMail()->send($transport);
		}

		/**
		 * Stop the fake SMTP server and remove the transcript.
		 */
		protected function tearDown(): void {
			if ($this->process !== null)
				\proc_close($this->process);

			if (\is_file($this->transcript))
				\unlink($this->transcript);
		}

		/**
		 * Start a fake SMTP server and create a transport connected to it.
		 *
		 * @param string $mechanisms Authentication mechanisms the server offers.
		 * @param string|null $username User name for the transport.
		 * @param string $password Password for the transport. The server accepts only 'secret'.
		 * @param string $rejected Address the server rejects.
		 * @param string $security Connection security of the transport.
		 * @return SMTPTransport
		 */
		private function createTransport(string $mechanisms = '', ?string $username = null, string $password = '', string $rejected = '', string $security = SMTPTransport::SECURITY_NONE): SMTPTransport {
			$this->transcript = \tempnam(\sys_get_temp_dir(), 'smtp');
			$config = \base64_encode(\json_encode(['mechanisms' => $mechanisms, 'password' => 'secret', 'rejected' => $rejected]));
			$command = [\PHP_BINARY, __DIR__ . '/resources/smtp_server.php', $this->transcript, $config];

			$this->process = \proc_open($command, [1 => ['pipe', 'w']], $pipes);
			$port = (int) \fgets($pipes[1]);
			\fclose($pipes[1]);
			$this->assertGreaterThan(0, $port, 'Fake SMTP server did not start.');

			return new SMTPTransport('127.0.0.1', $port, $username, $password, $security, 5, 'test.local');
		}

		/**
		 * Create a mail with a sender and one recipient.
		 *
		 * @return Mail
		 */
		private function createMail(): Mail {
			$mail = new Mail();
			$mail->setSender('sender@bar.net');
			$mail->to->add('foo@bar.net');
			return $mail;
		}

		/**
		 * Wait for the fake SMTP server to exit and read the lines it received.
		 *
		 * @return string[]
		 */
		private function readTranscript(): array {
			\proc_close($this->process);
			$this->process = null;

			return \file($this->transcript, \FILE_IGNORE_NEW_LINES);
		}

		/**
		 * @var resource|null
		 */
		private $process = null;

		/**
		 * @var string
		 */
		private string $transcript = '';
	}
