<?php
	namespace KrameWork\Runtime\ErrorFormatters;

	use KrameWork\Runtime\ErrorReports\IErrorReport;
	use KrameWork\Runtime\ErrorTypes\IError;

	/**
	 * Interface IErrorFormatter
	 * Represents classes that create error reports.
	 *
	 * @package KrameWork\Runtime\ErrorFormatters
	 * @author Kruithne <kruithne@gmail.com>
	 */
	interface IErrorFormatter
	{
		/**
		 * Called just before this report is used.
		 *
		 * @api beginReport
		 */
		public function beginReport();

		/**
		 * Format a stacktrace and add it to the report.
		 *
		 * @api reportStacktrace
		 * @param array $trace Stacktrace.
		 */
		public function reportStacktrace(array $trace);

		/**
		 * Format an error and add it to the report.
		 *
		 * @api handleError
		 * @param IError $error Error which occurred.
		 */
		public function reportError(IError $error);

		/**
		 * Format debug data and add it to the report.
		 *
		 * @param $debug array Key/Value pairs
		 */
		public function reportDebug(array $debug);

		/**
		 * Format an array and add it to the report.
		 *
		 * @api formatArray
		 * @param string $name Name for the array.
		 * @param array $arr Array of data.
		 */
		public function reportArray(string $name, array $arr);

		/**
		 * Format a data string and add it to the report.
		 *
		 * @api reportString
		 * @param string $name Name of the data string.
		 * @param string $str Data string.
		 */
		public function reportString(string $name, string $str);

		/**
		 * Generate a report.
		 *
		 * @api generate
		 * @return IErrorReport
		 */
		public function generate():IErrorReport;
	}