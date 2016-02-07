<?php
	interface IErrorHandler
	{
		/**
		 * Add a recipient to the mail template for error reporting.
		 *
		 * @param string $recipient Address of the recipient to add.
		 */
		public function addEmailOutputRecipient($recipient);

		/**
		 * Return the mail object being held by the error handler which is used as a template.
		 *
		 * @return KW_Mail ErrorHandler mail template.
		 */
		public function getMailObject();

		/**
		 * Turn on debug mode, dumping errors to the client
		 */
		public function debugMode();

		/**
		 * When debug mode is enabled, send errors in a json format
		 */
		public function debugJSON();

		/**
		 * Set the path which will be used for logging errors.
		 *
		 * @param string $log Path to a directory or file.
		 */
		public function setOutputLog($log);

		/**
		 * Handles a PHP runtime error.
		 *
		 * @param int $type ID of the error that occurred.
		 * @param string $string Message describing the error.
		 * @param string $file File where the error occurred.
		 * @param int $line The line number where the error occurred.
		 * @return bool True if the error was handled, else false.
		 */
		public function handleError($type, $string, $file, $line);

		/**
		 * Handles an exception in the PHP runtime.
		 *
		 * @param Exception $exception The uncaught exception.
		 */
		public function handleException($exception);

		/**
		 * Send an exception error report
		 *
		 * @param Exception $exception The exception.
		 */
		public function reportException($exception);

		/**
		 * Send an error report as per runtime configuration
		 *
		 * @param KW_ErrorReport $report An error report to send.
		 */
		public function sendErrorReport($report);
	}
?>
