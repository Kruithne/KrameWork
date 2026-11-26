<?php
	namespace KrameWork\Mailing;

	require_once(__DIR__ . '/IMailTransport.php');

	/**
	 * Class NativeMailTransport
	 * Delivers e-mail with the PHP mail() function and a local mail agent.
	 *
	 * @package KrameWork\Mailing
	 */
	class NativeMailTransport implements IMailTransport
	{
		/**
		 * Deliver a compiled e-mail.
		 *
		 * @api send
		 * @param string $sender Envelope sender address.
		 * @param RecipientCollection $to Primary recipients.
		 * @param RecipientCollection $cc Carbon copy recipients.
		 * @param RecipientCollection $bcc Blind carbon copy recipients.
		 * @param string $subject Encoded subject.
		 * @param array<string, string> $headers Headers, without To, Cc, Bcc and Subject.
		 * @param string $body Compiled MIME body.
		 */
		public function send(string $sender, RecipientCollection $to, RecipientCollection $cc, RecipientCollection $bcc, string $subject, array $headers, string $body): void {
			$lines = [];
			foreach ($headers as $name => $value)
				$lines[] = $name . ': ' . $value;

			if (!$bcc->isEmpty())
				$lines[] = 'Bcc: ' . $bcc;

			if (!$cc->isEmpty())
				$lines[] = 'Cc: ' . $cc;

			\mail((string) $to, $subject, $body, \implode("\n", $lines), '-f ' . $sender);
		}
	}
