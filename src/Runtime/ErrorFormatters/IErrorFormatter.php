<?php
	namespace KrameWork\Runtime\ErrorFormatters;

	use KrameWork\Runtime\ErrorReports\IErrorReport;
	use KrameWork\Runtime\ErrorTypes\IError;

	/**
	 * Interface IErrorFormatter
	 * Represents classes that create error reports.
	 *
	 * @package KrameWork\Runtime\ErrorFormatters
	 * @phpstan-import-type StackFrame from IError
	 */
	interface IErrorFormatter
	{
		/**
		 * Called just before this report is used.
		 *
		 * @api beginReport
		 */
		public function beginReport(): void;

		/**
		 * Format a stacktrace and add it to the report.
		 *
		 * @api reportStacktrace
		 * @param array<int, StackFrame> $trace Stacktrace.
		 */
		public function reportStacktrace(array $trace): void;

		/**
		 * Format an error and add it to the report.
		 *
		 * @api reportError
		 * @param IError $error Error which occurred.
		 */
		public function reportError(IError $error): void;

		/**
		 * Format debug data and add it to the report.
		 *
		 * @param array<string, mixed> $debug Key/Value pairs
		 */
		public function reportDebug(array $debug): void;

		/**
		 * Format an array and add it to the report.
		 *
		 * @api reportArray
		 * @param string $name Name for the array.
		 * @param array<int|string, mixed> $arr Array of data.
		 */
		public function reportArray(string $name, array $arr): void;

		/**
		 * Format a data string and add it to the report.
		 *
		 * @api reportString
		 * @param string $name Name of the data string.
		 * @param string $str Data string.
		 */
		public function reportString(string $name, string $str): void;

		/**
		 * Generate a report.
		 *
		 * @api generate
		 * @return IErrorReport
		 */
		public function generate(): IErrorReport;
	}