<?php
	use KrameWork\Mailing\EncodedContent;
	require_once(__DIR__ . '/../src/Mailing/EncodedContent.php');

	class EncodedContentTest extends PHPUnit\Framework\TestCase
	{
		/**
		 * Test that an EncodedContent with no content set casts to an empty string.
		 */
		public function testEmptyContentCastsToEmptyString() {
			$content = new EncodedContent(EncodedContent::E_BASE64);
			$this->assertEquals('', (string) $content);
		}
	}
