<?php
	use KrameWork\Reporting\HTMLReport\HTMLReportSection;

	require_once(__DIR__ . '/../src/Reporting/HTMLReport/HTMLReportSection.php');

	class HTMLReportSectionTest extends \PHPUnit\Framework\TestCase
	{
		/**
		 * A section tag that contains regex characters is found literally.
		 */
		public function testSectionTagWithRegexCharacters() {
			$section = new HTMLReportSection('A+B', '<!--A+B-->x<!--/A+B-->');
			$this->assertTrue($section->isValid());
		}
	}
