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
		 * @param string|array $name File name. Arrays are treated as callbacks.
		 */
		public function __construct(string $directory, $name = 'error') {
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
		public function dispatch($report):bool {
			$file = $this->name;
			if (\is_array($file)) // Execute callback.
				$file = \call_user_func(\count($file) == 1 ? $file[0] : $file, $report);
			elseif (is_callable($file))
				$file = $file($report);

			$ext = ($report instanceof IErrorReport) ? $report->getExtension() : '.txt';
			$full = $file . $ext;

			// Obtain unique file name.
			if (\is_file($full)) {
				$attemptIndex = 1;
				while (\is_file($full))
					$full = $file . '_' . $attemptIndex++ . $ext;
			}

			\file_put_contents($this->path . \DIRECTORY_SEPARATOR . $full, $report);
			return false;
		}

		/**
		 * @var string|array
		 */
		protected $name;

		/**
		 * @var string
		 */
		protected $path;
	}