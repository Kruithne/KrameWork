<?php
	namespace KrameWork\Runtime\ErrorDispatchers;

	use KrameWork\Runtime\ErrorReports\IErrorReport;

	require_once(__DIR__ . '/IErrorDispatcher.php');

	/**
	 * Class FileDispatcher
	 * Dispatches errors into flat-files.
	 *
	 * @package KrameWork\Runtime\ErrorDispatchers
	 * @author Kruithne <kruithne@gmail.com>
	 */
	class FileDispatcher implements IErrorDispatcher
	{
		/**
		 * FileDispatcher constructor.
		 *
		 * @api __construct
		 * @param string $directory Directory to store files in.
		 * @param string|(\Closure(IErrorReport|string): string) $name File name or name generator.
		 */
		public function __construct(string $directory, string|\Closure $name = 'error') {
			$check = \realpath($directory);
			if ($check === false || !\is_dir($check))
				$check = __DIR__;

			$this->path = $check;
			$this->name = $name;
		}

		/**
		 * Dispatch an error report.
		 *
		 * @api dispatch
		 * @param IErrorReport|string $report Report to dispatch.
		 * @return bool
		 */
		public function dispatch(IErrorReport|string $report): bool {
			$file = $this->name instanceof \Closure ? ($this->name)($report) : $this->name;
			$ext = ($report instanceof IErrorReport) ? $report->getExtension() : '.txt';
			$base = $this->path . \DIRECTORY_SEPARATOR . $file;
			$full = $base . $ext;

			$attemptIndex = 1;
			while (\is_file($full))
				$full = $base . '_' . $attemptIndex++ . $ext;

			\file_put_contents($full, $report);
			return false;
		}

		/**
		 * @var string|(\Closure(IErrorReport|string): string)
		 */
		protected string|\Closure $name;

		/**
		 * @var string
		 */
		protected string $path;
	}