<?php
	use KrameWork\Reporting\HTMLReport\HTMLReportTemplate;

	require_once(__DIR__ . '/../src/Reporting/HTMLReport/HTMLReportTemplate.php');

	class HTMLReportTemplateTest extends \PHPUnit\Framework\TestCase
	{
		/**
		 * A replacement tag that contains regex characters is replaced literally.
		 */
		public function testReplacementTagWithRegexCharacters() {
			$template = new HTMLReportTemplate('<p><!--A(B--></p>');
			$template->{'A(B'} = 'value';
			$this->assertEquals('<p>value</p>', (string)$template);
		}
	}
