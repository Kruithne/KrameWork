<?php
	use KrameWork\MVC\Module;
	require_once(__DIR__ . '/../src/MVC/Module.php');

	class ModuleTest extends PHPUnit\Framework\TestCase
	{
		/**
		 * Test module renders its template with values set by the subclass.
		 */
		public function testModuleRendersTemplate() {
			$module = new class (__DIR__ . '/resources/TemplateTest/template.php') extends Module {
				public function __construct(string $file) {
					parent::__construct($file);
					$this->template->content = 'Hello, world!';
				}
			};

			$this->assertEquals('<b>Hello, world!</b>', (string) $module);
		}
	}
