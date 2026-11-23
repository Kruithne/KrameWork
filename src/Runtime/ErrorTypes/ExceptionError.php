<?php
	namespace KrameWork\Runtime\ErrorTypes;

	use KrameWork\Runtime\DebugException;

	require_once(__DIR__ . '/IError.php');

	/**
	 * Class ExceptionError
	 * Represents an exception that occurred.
	 *
	 * @package KrameWork\Runtime
	 * @author Kruithne <kruithne@gmail.com>
	 */
	class ExceptionError implements IError
	{
		/**
		 * ExceptionError constructor.
		 *
		 * @api __construct
		 * @param \Throwable $ex
		 */
		public function __construct(\Throwable $ex) {
			$this->type = \get_class($ex);
			$this->message = $ex->getMessage();
			$this->file = $ex->getFile();
			$this->line = $ex->getLine();
			$this->trace = $ex->getTrace();

			if ($ex instanceof DebugException)
				$this->data = $ex->getDebugData();
		}

		/**
		 * Get a prefix for this error type.
		 *
		 * @api getPrefix
		 * @return string
		 */
		public function getPrefix():string {
			return 'EXCEPTION';
		}

		/**
		 * Get the type of exception that occurred.
		 *
		 * @api getType
		 * @return string
		 */
		public function getType():string {
			return $this->type;
		}

		/**
		 * Get the message provided by this exception.
		 *
		 * @api getMessage
		 * @return string
		 */
		public function getMessage():string {
			return $this->message;
		}

		/**
		 * Get the file in which this exception occurred.
		 *
		 * @api getFile
		 * @return string
		 */
		public function getFile():string {
			return $this->file;
		}

		/**
		 * Get the line of code which this exception occurred.
		 *
		 * @api getLine
		 * @return int
		 */
		public function getLine():int {
			return $this->line;
		}

		/**
		 * Get the trace of this exception.
		 *
		 * @api getTrace
		 * @return array
		 */
		public function getTrace():array {
			return $this->trace;
		}

		/**
		 * Get a pretty name for this error.
		 *
		 * @api getName
		 * @return string
		 */
		public function getName(): string {
			return $this->type;
		}

		/**
		 * Get additional debugging data from the error source.
		 * 
		 * @api getDebugData
		 * @return mixed
		 */
		public function getDebugData() {
			return $this->data ?? null;
		}

		/**
		 * @var string
		 */
		protected $type;

		/**
		 * @var string
		 */
		protected $message;

		/**
		 * @var string
		 */
		protected $file;

		/**
		 * @var int
		 */
		protected $line;

		/**
		 * @var array
		 */
		protected $trace;

		/**
		 * @var mixed
		 */
		protected $data;
	}