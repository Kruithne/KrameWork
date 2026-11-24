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
	}
