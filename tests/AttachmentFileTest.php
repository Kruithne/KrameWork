<?php
	require_once(__DIR__ . '/../src/Mailing/AttachmentFile.php');

	use KrameWork\Mailing\AttachmentFile;

	class AttachmentFileTest extends PHPUnit\Framework\TestCase
	{
		/**
		 * Test that a new attachment is not inline by default.
		 */
		public function testIsInlineDefaultsToFalse() {
			$file = new AttachmentFile('tests/resources/test_text_file.txt', false);
			$this->assertFalse($file->isInline(), 'New attachment did not default to not inline.');
		}
	}
