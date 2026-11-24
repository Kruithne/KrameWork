<?php
	require_once(__DIR__ . '/../src/Mailing/Mail.php');

	use KrameWork\Mailing\AttachmentFile;
	use KrameWork\Mailing\Mail;

	class MailTest extends PHPUnit\Framework\TestCase
	{
		/**
		 * Test that attaching a file without an inline argument keeps its inline setting.
		 */
		public function testAttachFileKeepsInlineSetting() {
			$file = new AttachmentFile('tests/resources/test_text_file.txt', false);
			$file->setInline(true);

			$mail = new Mail();
			$mail->attachFile($file);

			$this->assertTrue($file->isInline(), 'Attaching the file reset its inline setting.');
		}
	}
