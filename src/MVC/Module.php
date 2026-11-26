<?php
	namespace KrameWork\MVC;

	require_once(__DIR__ . '/Template.php');

	/**
	 * Class Module
	 * Base class for a page component that renders one template.
	 *
	 * @package KrameWork\MVC
	 */
	abstract class Module
	{
		/**
		 * Module constructor.
		 *
		 * @api __construct
		 * @param string $file Path to the template file for this module.
		 * @throws InvalidTemplateException
		 */
		public function __construct(string $file) {
			$this->template = new Template($file);
		}

		/**
		 * Render the template of this module and return it as a string.
		 *
		 * @api __toString
		 * @return string
		 * @throws InvalidTemplateException
		 */
		public function __toString(): string {
			return (string) $this->template;
		}

		/**
		 * @var Template
		 */
		protected Template $template;
	}
