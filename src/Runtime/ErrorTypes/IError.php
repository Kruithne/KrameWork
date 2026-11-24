<?php
	namespace KrameWork\Runtime\ErrorTypes;

	/**
	 * Interface IError
	 * Represents classes that contain error data.
	 *
	 * @package KrameWork\Runtime\ErrorTypes
	 * @author Kruithne <kruithne@gmail.com>
	 * @phpstan-type StackFrame array{function: string, line?: int, file?: string, class?: string, type?: string, args?: array<mixed>, object?: object}
	 */
	interface IError
	{
		/**
		 * Get the message associated with this error.
		 *
		 * @api getMessage
		 * @return string
		 */
		public function getMessage():string;

		/**
		 * Get the line of code that produced this error.
		 *
		 * @api getLine
		 * @return int
		 */
		public function getLine():int;

		/**
		 * Get the file which produced this error,
		 *
		 * @api getFile
		 * @return string
		 */
		public function getFile():string;

		/**
		 * Get the stacktrace for this error.
		 *
		 * @api getTrace
		 * @return array<int, StackFrame>
		 */
		public function getTrace():array;

		/**
		 * Get a pretty name for this error.
		 *
		 * @api getName
		 * @return string
		 */
		public function getName():string;

		/**
		 * Get a prefix for this error type.
		 *
		 * @api getPrefix
		 * @return string
		 */
		public function getPrefix():string;

		/**
		 * Get additional debugging data from the error source.
		 * 
		 * @api getDebugData
		 * @return mixed
		 */
		public function getDebugData(): mixed;
	}