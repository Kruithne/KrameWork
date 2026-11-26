<?php
	namespace KrameWork\Mailing;

	require_once(__DIR__ . '/RecipientCollection.php');

	/**
	 * Interface IMailTransport
	 * Delivers a compiled e-mail.
	 *
	 * @package KrameWork\Mailing
	 */
	interface IMailTransport
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
		public function send(string $sender, RecipientCollection $to, RecipientCollection $cc, RecipientCollection $bcc, string $subject, array $headers, string $body): void;
	}
