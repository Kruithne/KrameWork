<?php
	namespace KrameWork\Reporting\HTMLReport;

	require_once(__DIR__ . '/HTMLReportSection.php');

	/**
	 * Class HTMLReportTemplate
	 * Represents a chunk of HTML for templating.
	 *
	 * @package KrameWork\Reporting\HTMLReport
	 * @author Kruithne <kruithne@gmail.com>
	 */
	class HTMLReportTemplate
	{
		/**
		 * HTMLReportTemplate constructor.
		 *
		 * @api __construct
		 * @param string $content Content of the template.
		 * @link http://php.net/manual/en/language.oop5.decon.php
		 */
		public function __construct(protected string $content) {
			$this->replacements = [];
			$this->sections = [];
		}

		/**
		 * Get a section of this template.
		 * Returned section will be retained by this template for compilation.
		 *
		 * @api getSection
		 * @param string $tag Enclosing tags for the section.
		 * @return HTMLReportSection
		 */
		public function getSection(string $tag):HTMLReportSection {
			$section = new HTMLReportSection($tag, $this->content);
			$this->sections[] = $section;
			return $section;
		}

		/**
		 * Set a simple replacement for this template.
		 *
		 * @api __set
		 * @param string $tag Tag to search for.
		 * @param string $value Replacement value.
		 * @link http://php.net/manual/en/language.oop5.overloading.php#language.oop5.overloading.members
		 */
		public function __set(string $tag, string $value): void {
			$this->replacements[$tag] = $value;
		}

		/**
		 * Compile this template and return it as a string.
		 *
		 * @api __toString
		 * @return string
		 * @link http://php.net/manual/en/language.oop5.magic.php#language.oop5.magic.tostring
		 */
		function __toString():string {
			$content = $this->content;

			// Section frame replacements.
			foreach ($this->sections as $section) {
				$section->validate($content);
				if ($section->isValid()) {
					$contentStart = \substr($content, 0, $section->getSectionStart());
					$contentEnd = \substr($content, $section->getSectionStart() + $section->getSectionLength());

					$content = $contentStart . $section . $contentEnd;
				}
			}

			// Compile basic replacements.
			$patterns = [];
			$replacements = [];

			foreach ($this->replacements as $tag => $replacement) {
				$patterns[] = '/<!--' . $tag . '-->/is';
				$replacements[] = $replacement;
			}

			$content = \preg_replace($patterns, $replacements, $content);
			return $content;
		}

		/**
		 * @var HTMLReportSection[]
		 */
		protected array $sections;

		/**
		 * @var array<string, string>
		 */
		protected array $replacements;
	}