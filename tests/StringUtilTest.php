<?php
	require_once(__DIR__ . "/../src/Utils/StringUtil.php");

	use KrameWork\Utils\StringUtil;

	class StringUtilTest extends PHPUnit\Framework\TestCase
	{
		/**
		 * Test the functionality of StringUtil::formatSlashes
		 */
		public function testFormatSlashes() {
			// Set-up test string.
			$sep = DIRECTORY_SEPARATOR == "/" ? "\\" : "/";
			$parts = ["The", "dog", "pooped", "on", "the", "lawn"];
			$str = join($sep, $parts);

			$cleaned = StringUtil::formatDirectorySlashes($str);
			$this->assertStringNotContainsString($sep, $cleaned, "Cleaned path still contains invalid slashes.");
		}

		/**
		 * Test functionality of StringUtil::namespaceBase
		 */
		public function testNamespaceBase() {
			$namespace = "KrameWork\\Storage\\Fish";
			$corrected = StringUtil::namespaceBase($namespace);
			$this->assertEquals("Fish", $corrected, "Base namespace does not match expected.");
		}

		/**
		 * Test trailing-trim functionality of StringUtil::formatDirectorySlashes.
		 */
		public function testFormatSlashesTrailing() {
			$fs = StringUtil::formatDirectorySlashes("Some/Directory/String/", true);
			$bs = StringUtil::formatDirectorySlashes("Another\\Directory\\String\\", true);

			$this->assertEquals("g", $fs[strlen($fs) - 1], "FS path was not trimmed.");
			$this->assertEquals("g", $bs[strlen($bs) - 1], "BS path was not trimmed.");
		}
	}