<?php
	namespace KrameWork\Runtime\ErrorTypes;

	require_once(__DIR__ . '/IError.php');

	/**
	 * Class RuntimeError
	 * Represents a runtime error that occurred.
	 *
	 * @package KrameWork\Runtime
	 * @phpstan-import-type StackFrame from IError
	 */
	class RuntimeError implements IError
	{
		/**
		 * RuntimeError constructor.
		 *
		 * @api __construct
		 * @param int $type Type of error that occurred.
		 * @param string $message Description of the error.
		 * @param string $file File where the error occurred.
		 * @param int $line Line of code the error occurred.
		 */
		public function __construct(protected int $type, protected string $message, protected string $file, protected int $line) {}

		/**
		 * Get a prefix for this error type.
		 *
		 * @api getPrefix
		 * @return string
		 */
		public function getPrefix():string {
			return 'RUNTIME ERROR';
		}

		/**
		 * Get the type of error which occurred.
		 *
		 * @api getType
		 * @return int
		 */
		public function getType():int {
			return $this->type;
		}

		/**
		 * Get the type of error that occurred as a string.
		 *
		 * @api getTypeName
		 * @return string
		 */
		public function getName():string {
			return match ($this->type) {
				E_ERROR => 'ERROR',
				E_WARNING => 'WARNING',
				E_PARSE => 'PARSE',
				E_NOTICE => 'NOTICE',
				E_CORE_ERROR => 'CORE ERROR',
				E_CORE_WARNING => 'CORE WARNING',
				E_COMPILE_ERROR => 'COMPILE ERROR',
				E_COMPILE_WARNING => 'COMPILE WARNING',
				E_USER_ERROR => 'USER ERROR',
				E_USER_WARNING => 'USER WARNING',
				E_USER_NOTICE => 'USER NOTICE',
				E_USER_DEPRECATED, E_DEPRECATED => 'DEPRECATED',
				E_STRICT => 'STRICT',
				E_RECOVERABLE_ERROR => 'RECOVERABLE',
				default => 'UNKNOWN'
			};
		}

		/**
		 * Get the message provided by this error.
		 *
		 * @api getMessage
		 * @return string
		 */
		public function getMessage():string {
			return $this->message;
		}

		/**
		 * Get the file which this error occurred in.
		 *
		 * @api getFile
		 * @return string
		 */
		public function getFile():string {
			return $this->file;
		}

		/**
		 * Get the line of code this error occurred on.
		 *
		 * @api getLine
		 * @return int
		 */
		public function getLine():int {
			return $this->line;
		}

		/**
		 * Get the stacktrace for this error.
		 *
		 * @api getTrace
		 * @return array<int, StackFrame>
		 */
		public function getTrace(): array {
			return \debug_backtrace();
		}

		/**
		 * Get additional debugging data from the error source.
		 * 
		 * @api getDebugData
		 * @return mixed
		 */
		public function getDebugData(): mixed {
			return null;
		}
	}