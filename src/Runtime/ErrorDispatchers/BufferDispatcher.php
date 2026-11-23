<?php
	namespace KrameWork\Runtime\ErrorDispatchers;

	use KrameWork\Runtime\ErrorReports\IErrorReport;

	require_once(__DIR__ . '/IErrorDispatcher.php');
	require_once(__DIR__ . '/IBufferDispatcher.php');

	/**
	 * Class BufferDispatcher
	 * Outputs errors directly as PHP output.
	 * Intended for use during debugging only.
	 *
	 * @package KrameWork\Runtime\ErrorDispatchers
	 * @author Kruithne <kruithne@gmail.com>
	 */
	class BufferDispatcher implements IErrorDispatcher, IBufferDispatcher
	{
		/**
		 * Dispatch an error report.
		 *
		 * @api dispatch
		 * @param IErrorReport|string $report Report to dispatch.
		 * @return bool
		 */
		public function dispatch($report):bool {
			// Clear all output already sent.
			while (\ob_get_level())
				\ob_end_clean();

			if (!\headers_sent()) {
				\header('HTTP/1.0 500 Server error');

				if ($report instanceof IErrorReport)
					\header('Content-Type: ' . $report->getContentType());
			}

			echo $report;
			return true;
		}
	}