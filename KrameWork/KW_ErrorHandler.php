<?php
	class KW_ErrorHandler
	{
		/**
		 * Construct an error handler for the KrameWork system.
		 *
		 * @param bool $alterLevel Can we alter the runtime error level?
		 * @param integer $maxErrors How many errors do we abort after, per execution?
		 */
		public function __construct($alterLevel = true, $maxErrors = 10)
		{
			$this->maxErrors = $maxErrors;
			if ($alterLevel)
				error_reporting(E_ALL);

			set_error_handler(array($this, 'handleError'));
			set_exception_handler(array($this, 'handleException'));
		}

		/**
		 * Add a recipient to the mail template for error reporting.
		 *
		 * @param string $recipient Address of the recipient to add.
		 */
		public function addEmailOutputRecipient($recipient)
		{
			$this->getMailObject()->addRecipients($recipient);
		}

		/**
		 * Return the mail object being held by the error handler which is used as a template.
		 *
		 * @return KW_Mail ErrorHandler mail template.
		 */
		public function getMailObject()
		{
			if ($this->mail === null)
			{
				$this->mail = new KW_Mail();
				$this->mail->setHeader('MIME-Version', '1.0');
			}

			return $this->mail;
		}

		/**
		 * Turn on debug mode, dumping errors to the client
		 */
		public function debugMode()
		{
			$this->debug = true;
		}

		/**
		 * When debug mode is enabled, send errors in a json format
		 */
		public function debugJSON()
		{
			$this->json = true;
		}

		/**
		 * Set the path which will be used for logging errors.
		 *
		 * @param string $log Path to a directory or file.
		 */
		public function setOutputLog($log)
		{
			$this->log = $log;
		}

		/**
		 * Handles a PHP runtime error.
		 *
		 * @param int $type ID of the error that occurred.
		 * @param string $string Message describing the error.
		 * @param string $file File where the error occurred.
		 * @param int $line The line number where the error occurred.
		 * @return bool True if the error was handled, else false.
		 */
		public function handleError($type, $string, $file, $line)
		{
			if ($this->errorCount++ > $this->maxErrors)
				die('Excessive errors, aborting');

			if (!error_reporting() & $type)
				return true;

			if ($type == E_USER_ERROR)
				header('HTTP/1.0 500 Internal Error');

			$this->sendErrorReport($this->generateErrorReport($this->getErrorType($type), $line, $file, $string, debug_backtrace()));
			return true;
		}

		/**
		 * Return a textual representation of the error type
		 * @param int $type An error type code
		 * @return string An error type
		 */
		private function getErrorType($type)
		{
			// List of textual representation of error codes
			switch($type)
			{
				case E_ERROR:   return 'ERROR';
				case E_WARNING: return 'WARNING';
				case E_PARSE:   return 'PARSE';
				case E_NOTICE:  return 'NOTICE';

				case E_CORE_ERROR:   return 'CORE ERROR';
				case E_CORE_WARNING: return 'CORE WARNING';

				case E_COMPILE_ERROR:   return 'COMPILE ERROR';
				case E_COMPILE_WARNING: return 'COMPILE WARNING';

				case E_USER_ERROR:   return 'USER ERROR';
				case E_USER_WARNING: return 'USER WARNING';
				case E_USER_NOTICE:  return 'USER NOTICE';
				case E_USER_DEPRECATED: return 'DEPRECATED';

				case E_STRICT:            return 'STRICT';
				case E_DEPRECATED:				return 'DEPRECATED';
				case E_RECOVERABLE_ERROR: return 'RECOVERABLE';

				default: return 'UNKNOWN';
			}
		}

		/**
		 * Handles an exception in the PHP runtime.
		 *
		 * @param Exception $exception The uncaught exception.
		 */
		public function handleException($exception)
		{
			header('HTTP/1.0 500 Internal Error');

			if ($this->errorCount++ > $this->maxErrors)
				die('Excessive errors, aborting');

			$this->sendErrorReport($this->generateErrorReport(
				'EXCEPTION', $exception->getLine(), $exception->getFile(), $exception->getMessage(), $exception->getTrace())
			);
		}

		/**
		 * Generate an error report.
		 *
		 * @param string $type Describe the error in roughly one word, such as EXCEPTION.
		 * @param int $line The line where this error occurred.
		 * @param string $file The file where this error occurred.
		 * @param string $error A description of the error.
		 * @param null|string $trace
		 * @return KW_ErrorReport An error report object ready for use.
		 */
		private function generateErrorReport($type, $line, $file, $error, $trace = null)
		{
			error_log(sprintf('%2$s:%3$d %1$s %4$s', $type, $file, $line, $error));
			$report = new KW_ErrorReport();
			$report->setSubject('Error (' . $type . ') - ' . date("Y-m-d H:i:s"));
			$report->Type = $type;
			$report->Line = $line;
			$report->File = $file;
			$report->Error = $error;
			$report->trace = $trace;

			return $report;
		}

		/**
		 * Send an error report as per runtime configuration
		 *
		 * @param KW_ErrorReport $report An error report to send.
		 */
		public function sendErrorReport($report)
		{
			if ($this->mail !== null)
				$this->sendEmail($report);

			if ($this->log !== null)
				$this->writeLog($report);

			if ($this->debug)
			{
				if ($this->json)
					$this->dumpJSON($report);
				else
					$this->dumpHTML($report);
			}
		}

		/**
		 * Send an error report as an email
		 *
		 * @param KW_ErrorReport $report An error report to send.
		 */
		private function sendEmail($report)
		{
			$this->mail->clear();
			$this->mail->append((string) $report);

			if ($this->mail->getSubject() === null)
				$this->mail->setSubject($report->getSubject());

			if ($this->mail->getRecipientCount() > 0)
				$this->mail->send();
		}

		/**
		 * Send an error report to the log file
		 *
		 * @param KW_ErrorReport $report An error report to send.
		 */
		private function writeLog($report)
		{
			if (@is_file($this->log))
			{
				file_put_contents($this->log, (string) $report, FILE_APPEND);
			}
			else if (@is_dir($this->log))
			{
				$log_file = $this->createLogFileName($this->log);
				file_put_contents($log_file, (string) $report);
			}
		}

		/**
		 * Send an error report to the client as HTML
		 *
		 * @param KW_ErrorReport $report An error report to send.
		 */
		private function dumpHTML($report)
		{
			echo $report->getHTMLReport();
		}

		/**
		 * Send an error report to the client as JSON and terminate execution
		 *
		 * @param KW_ErrorReport $report An error report to send.
		 */
		private function dumpJSON($report)
		{
			while(ob_get_level())
				ob_end_clean();
			header('HTTP/1.0 500 Server error');
			header('Content-Type: application/json; encoding=UTF-8');
			echo '{error:'.$report->getJSONReport().'}';
			die();
		}

		/**
		 * Generate a log name.
		 * @param int $number
		 * @return string
		 */
		private function getLogName($number)
		{
			return time() . '_' . $number . '.log';
		}

		/**
		 * Generate a new log file name.
		 * @param string $directory
		 * @return string
		 */
		private function createLogFileName($directory)
		{
			$number = 0;
			$file_name = $this->getLogName($number);
			while (file_exists($directory . DIRECTORY_SEPARATOR . $file_name))
			{
				$number++;
				$file_name = $this->getLogName($number);
			}

			return $directory . DIRECTORY_SEPARATOR . $file_name;
		}

		/**
		 * @var KW_Mail
		 */
		private $mail;

		/**
		 * @var string|null Will be null if not yet set.
		 */
		private $log;

		/**
		 * @var $maxErrors Number of errors to process before aborting execution.
		 */
		private $maxErrors = 10;

		/**
		 * @var integer Number of errors this execution.
		 */
		private $errorCount = 0;

		/**
		 * @var bool $debug Dump errors to the client
		 */
		private $debug;

		/**
		 * @var bool $json When dumping errors to the client, use json formatting
		 */
		private $json;
	}
?>
