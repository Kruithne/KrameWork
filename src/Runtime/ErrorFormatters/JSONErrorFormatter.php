<?php
	namespace KrameWork\Runtime\ErrorFormatters;

	use KrameWork\Runtime\ErrorReports\ErrorReport;
	use KrameWork\Runtime\ErrorReports\IErrorReport;
	use KrameWork\Runtime\ErrorTypes\IError;

	require_once(__DIR__ . '/../ErrorReports/ErrorReport.php');
	require_once(__DIR__ . '/IErrorFormatter.php');
	/**
	 * Class JSONErrorFormatter
	 * Error report implementation: JSON
	 *
	 * @package KrameWork\Runtime\ErrorReports
	 * @phpstan-import-type StackFrame from IError
	 */
	class JSONErrorFormatter implements IErrorFormatter
	{
		/**
		 * Called just before this report is used.
		 *
		 * @api beginReport
		 */
		public function beginReport(): void {
			$this->node = [];
			$this->data = [];
		}

		/**
		 * Format an error and add it to the report.
		 *
		 * @api reportError
		 * @param IError $error Error which occurred.
		 */
		public function reportError(IError $error): void {
			$this->error = $error;
			$this->node['prefix'] = $error->getPrefix();
			$this->node['name'] = $error->getName();
			$this->node['server'] = \php_uname();
			$this->node['message'] = $error->getMessage();
			$this->node['timestamp'] = $t = \time();
			$this->node['occurred'] = \date(\DATE_RFC822, $t);
			$this->node['file'] = $error->getFile();
			$this->node['line'] = $error->getLine();

			$debug = $error->getDebugData();
			if ($debug !== null)
				$this->data['exceptionDebug'] = $debug;
		}

		/**
		 * Format debug data and add it to the report.
		 *
		 * @param array<string, mixed> $debug Key/Value pairs
		 */
		public function reportDebug(array $debug): void {
			$this->node['debug'] = $debug;
		}

		/**
		 * Format an array and add it to the report.
		 *
		 * @api reportArray
		 * @param string $name Name for the array.
		 * @param array<int|string, mixed> $arr Array of data.
		 */
		public function reportArray(string $name, array $arr): void {
			$this->data[$name] = $arr;
		}

		/**
		 * Format a data string and add it to the report.
		 *
		 * @api reportString
		 * @param string $name Name of the data string.
		 * @param string $str Data string.
		 */
		public function reportString(string $name, string $str): void {
			$this->data[$name] = $str;
		}

		/**
		 * Format a stacktrace and add it to the report.
		 *
		 * @api reportStacktrace
		 * @param array<int, StackFrame> $trace Stacktrace.
		 */
		public function reportStacktrace(array $trace): void {
			$this->node['trace'] = $trace;
		}

		/**
		 * Generate a report.
		 *
		 * @api generate
		 * @return IErrorReport
		 */
		public function generate(): IErrorReport {
			$error = $this->error;
			if ($error === null)
				throw new \LogicException('reportError() must be called before generate()');

			$json = \json_encode(['data' => $this->data] + $this->node, \JSON_INVALID_UTF8_SUBSTITUTE | \JSON_PARTIAL_OUTPUT_ON_ERROR);
			return new ErrorReport($error, 'application/json', '.json', $json ?: '{}');
		}

		/**
		 * @var array<string, mixed>
		 */
		protected array $node = [];

		/**
		 * @var array<string, mixed>
		 */
		protected array $data = [];

		/**
		 * @var IError|null
		 */
		protected ?IError $error = null;
	}