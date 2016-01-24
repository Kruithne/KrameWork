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
			return self::getReportingMailObject();
		}

		/**
		 * Return the mail object being held by the error handler which is used as a template.
		 *
		 * @return KW_Mail ErrorHandler mail template.
		 */
		public static function getReportingMailObject()
		{
			if (self::$mail === null)
			{
				self::$mail = new KW_Mail();
				self::$mail->setHeader('MIME-Version', '1.0');
			}

			return self::$mail;
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
		 * Handles a PHP runtime error that normally cannot be caught.
		 * To use this, you need to set these PHP configuration options:
		 *	error_prepend_string = "<!--[INTERNAL_ERROR]"
		 *	error_append_string = "-->"
		 *	html_errors = Off
		 *	auto_prepend_file = /path/to/error.php ; see errorCatcher example
		 *
		 * @param string $buffer Script output passed by PHP output buffer
		 * @return string Content to send to the client
		 */
		public static function errorCatcher($buffer)
		{
			// Detect error
			if (preg_match('/<!--\[INTERNAL_ERROR\](.*)-->/Us', $buffer, $match))
				return self::handleFatalError($buffer, $match);

			if (self::$startup)
				self::timeScript();

			// No error to handle
			return $buffer;
		}

		/**
		 * Handle a fatal PHP error caught via output buffer
		 *
		 * @param string $buffer The contents of the output buffer
		 * @param string[] $match The matching error
		 * @return string Resulting output to client
		 */
		private static function handleFatalError($buffer, $match)
		{
			// The internal PHP message
			$error = $match[1];

			// Parse error
			preg_match('/(.*) error: (.*) in (.*) on line (.*)/', $error, $matches);

			// Something is bad here..
			if (count($matches) != 5)
				return 'Internal error ('.count($matches).') : ' . $error;

			$report = self::generateErrorReport($matches[1], $matches[4], $matches[3], $matches[2], debug_backtrace());
			if(self::$errorDocument)
				return sprintf(self::$errorDocument, $report->getHTMLReport());

			return str_replace($match[0], $report->getHTMLReport(), $buffer);
		}

		/**
		 * Check script run time and send alert if it is taking too long
		 */
		private static function timeScript()
		{
			$responseTime = microtime(true) - self::$startup;

			// Report if time exceeds warning limit, unless called from CLI
			if ($responseTime < self::$slowWarn || isset($_SERVER['REQUEST_URI']))
				return;

			// If there is nowhere to send the warning, just log it
			if (self::$mail->getRecipientCount() > 0)
			{
				error_log(sprintf('Slow request: %s/%s (%.3fs)', $_SERVER['HTTP_HOST'], $_SERVER['REQUEST_URI'], $responseTime));
				return;
			}
			$msg = sprintf('Request took %.3fs', $responseTime);
			if (class_exists('KW_DatabaseConnection') && KW_DatabaseConnection::$trace)
			{
				$msg .= "\n\n=== DB stats ===\n\n";
				$seen = array();
				$timing = array();
				$lt = 0;
				foreach (KW_DatabaseConnection::$traceLog as $log)
				{
					if (!in_array($log['sql'], $seen))
					{
						$seen[] = $log['sql'];
						$timing[] = 0;
					}
					$k = array_search($log['sql'], $seen);
					$timing[$k] += $log['time'];
					$o = $log['timestamp'] - self::$startup;
					$params = array();
					foreach ($log['param'] as $key => $value)
						$params[] = sprintf('[%s] = [%s]', $key, $value);
					$msg .= sprintf("%.3f +%.3f [%d] {%s} %.3fs\n", $o, $o - $lt, $k, join(', ', $params), $log['time']);
					$lt = $o + $log['time'];
				}
				$msg .= "\n\n=== DB queries ===\n\n";
				foreach ($seen as $k => $sql)
					$msg .= sprintf("[%d] %.3fs\n%s\n-------------\n", $k, $timing[$k], $sql);
			}
			self::$mail->clear();
			self::$mail->append($msg);

			if (self::$mail->getSubject() === null)
				self::$mail->setSubject('Slow request: ' . $_SERVER['HTTP_HOST'] . '/' . $_SERVER['REQUEST_URI']);

			self::$mail->send();
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

			$this->sendErrorReport(self::generateErrorReport($this->getErrorType($type), $line, $file, $string, debug_backtrace()));
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
			switch ($type)
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

			$this->sendErrorReport(self::generateErrorReport(
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
		private static function generateErrorReport($type, $line, $file, $error, $trace = null)
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
			if (self::$mail !== null)
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
			if (self::$mail->getRecipientCount() < 1)
				return;

			self::$mail->clear();
			self::$mail->append((string) $report);

			if (self::$mail->getSubject() === null)
				self::$mail->setSubject($report->getSubject());

			self::$mail->send();
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
			if(self::$errorDocument)
			{
				while(ob_get_level())
					ob_end_clean();
				echo sprintf(self::$errorDocument, $report->getHTMLReport());
				return;
			}
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
		 * @var float $startup set to microtime(true) to run a timing check
		 */
		public static $startup;

		/**
		 * @var float $slowWarn Time in seconds before script slow warnings are triggered
		 */
		public static $slowWarn;

		/**
		 * @var string $errorDocument HTML to use for reporting errors. Error report will be injected via sprintf
		 */
		public static $errorDocument;

		/**
		 * @var KW_Mail
		 */
		private static $mail;

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

		/**
		 * @var float $startup set to microtime(true) to run a timing check
		 */
		private $startup;

		/**
		 * @var float $slowWarn Time in seconds before script slow warnings are triggered
		 */
		private $slowWarn;
	}
?>
