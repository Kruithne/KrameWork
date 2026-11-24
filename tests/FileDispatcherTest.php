<?php
	use KrameWork\Runtime\ErrorDispatchers\FileDispatcher;
	require_once(__DIR__ . '/../src/Runtime/ErrorDispatchers/FileDispatcher.php');

	class FileDispatcherTest extends PHPUnit\Framework\TestCase
	{
		/**
		 * Ensure a file name that matches a PHP function is used as the name and not called.
		 */
		public function testFunctionNameIsNotCalled() {
			$dir = \sys_get_temp_dir();
			$file = $dir . \DIRECTORY_SEPARATOR . 'log.txt';
			@unlink($file); // Delete from broken tests.

			$dispatcher = new FileDispatcher($dir, 'log');
			$dispatcher->dispatch('report');

			$this->assertFileExists($file);
			@unlink($file); // Clean-up.
		}

		/**
		 * Ensure a report does not overwrite an existing report with the same name in the target directory.
		 */
		public function testExistingReportIsNotOverwritten() {
			$dir = \sys_get_temp_dir() . \DIRECTORY_SEPARATOR . \uniqid('kw_file_dispatcher_');
			\mkdir($dir);

			$dispatcher = new FileDispatcher($dir, 'report');
			$dispatcher->dispatch('first');
			$dispatcher->dispatch('second');

			$this->assertStringEqualsFile($dir . \DIRECTORY_SEPARATOR . 'report.txt', 'first');
			$this->assertStringEqualsFile($dir . \DIRECTORY_SEPARATOR . 'report_1.txt', 'second');

			\array_map('unlink', \glob($dir . \DIRECTORY_SEPARATOR . '*'));
			\rmdir($dir); // Clean-up.
		}
	}
