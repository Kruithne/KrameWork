<?php
	class KW_ErrorHandler
	{
		/**
		 * Construct an error handler for the KrameWork system.
		 *
		 * @param bool $alterLevel Can we alter the runtime error level?
		 */
		public function __construct($alterLevel = true)
		{
			if ($alterLevel)
				error_reporting(E_ALL);

			set_error_handler(array($this, "handleError"));
		}

		/**
		 * Add a recipient to the mail template for error reporting.
		 *
		 * @param string $recipient Address of the recipient to add.
		 */
		public function addEmailOutputRecipient($recipient)
		{
			$this->getMailObject()->addRecipient($recipient);
		}

		/**
		 * Return the mail object being held by the error handler which is used as a template.
		 *
		 * @return KW_Mail ErrorHandler mail template.
		 */
		public function getMailObject()
		{
			if ($this->mail === NULL)
				$this->mail = new KW_Mail();

			return $this->mail;
		}

		public function setOutputLog($log)
		{
			// TODO: Implement me.
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
			if (!error_reporting() & $type)
				return true;

			switch ($type)
			{
				case E_USER_ERROR: $type = 'FATAL'; break;
				case E_USER_WARNING: $type = 'WARNING'; break;
				case E_USER_NOTICE: $type = 'NOTICE'; break;
				case E_STRICT: $type = 'STRICT'; break;
				case E_USER_DEPRECATED: $type = 'DEPRECATED'; break;
				default: $type = 'UNKNOWN'; break;
			}

			$report = new KW_ErrorReport();
			$report->Type = $type;
			$report->Line = $line;
			$report->File = $file;
			$report->Error = $string;
			$report->setSubject('Error (' . $type . ') - ' . date("Y-m-d H:i:s"));

			$this->sendErrorReport($report);

			return true;
		}

		/**
		 * Send an error report object using the handler mail template.
		 *
		 * @param KW_ErrorReport $report An error report to send.
		 */
		public function sendErrorReport($report)
		{
			$this->mail->clear();
			$this->mail->append((string) $report);

			if ($this->mail->getSubject() === NULL)
				$this->mail->setSubject($report->getSubject());

			if ($this->mail->getRecipientCount() > 0)
				$this->mail->send();
		}

		/**
		 * @var KW_Mail
		 */
		private $mail;
	}
?>