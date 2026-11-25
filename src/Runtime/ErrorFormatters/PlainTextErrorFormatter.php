<?php
	namespace KrameWork\Runtime\ErrorFormatters;

	use KrameWork\Runtime\ErrorReports\ErrorReport;
	use KrameWork\Runtime\ErrorReports\IErrorReport;
	use KrameWork\Runtime\ErrorTypes\IError;
	use KrameWork\Timing\Timer;
	use KrameWork\Utils\StringBuilder;
	use KrameWork\Utils\StringUtil;

	require_once(__DIR__ . '/../../Utils/StringBuilder.php');
	require_once(__DIR__ . '/../../Utils/StringUtil.php');
	require_once(__DIR__ . '/../ErrorReports/ErrorReport.php');
	require_once(__DIR__ . '/IErrorFormatter.php');
	require_once(__DIR__ . '/../../Timing/Timer.php');

	/**
	 * Class PlainTextErrorFormatter
	 * Error report implementation: Plain Text
	 *
	 * @package KrameWork\Runtime\ErrorReports
	 * @author Kruithne <kruithne@gmail.com>
	 * @phpstan-import-type StackFrame from IError
	 */
	class PlainTextErrorFormatter implements IErrorFormatter
	{
		/**
		 * PlainTextErrorFormatter constructor.
		 *
		 * @api __construct
		 * @param string $lineEnd Line-endings to use.
		 * @param bool $wrapPreTags Wrap the report in HTML <pre/> tags.
		 */
		public function __construct(protected string $lineEnd = StringBuilder::LE_UNIX, protected bool $wrapPreTags = false) {
			$this->timer = new Timer(Timer::FORMAT_MICROSECONDS, false);
			$this->report = new StringBuilder();
			$this->report->setLineEnd($this->lineEnd);
		}

		/**
		 * Called just before this report is used.
		 *
		 * @api beginReport
		 */
		public function beginReport(): void {
			$this->report = new StringBuilder();
			$this->report->setLineEnd($this->lineEnd);
			$this->timer->start();
		}

		/**
		 * Format a data string and add it to the report.
		 *
		 * @api reportString
		 * @param string $name Name of the data string.
		 * @param string $str Data string.
		 */
		public function reportString(string $name, string $str): void {
			$this->report->appendf('> %s => %s', $name, StringUtil::variableAsString($str));
			$this->report->newLine()->newLine();
		}

		/**
		 * Format an array and add it to the report.
		 *
		 * @api reportArray
		 * @param string $name Name for the array.
		 * @param array<int|string, mixed> $arr Array of data.
		 */
		public function reportArray(string $name, array $arr): void {
			if ($arr) {
				$this->report->appendf('> %s [%s items]', $name, \count($arr))->newLine()->indent();
				foreach ($arr as $key => $value)
					$this->report->appendf('%s => %s', $key, StringUtil::variableAsString($value))->newLine();
			} else {
				$this->report->appendf('> %s [empty]', $name)->newLine();
				$this->report->indent()->appendLine('No data to display');
			}

			$this->report->outdent()->newLine();
		}

		/**
		 * Format a report for a runtime error.
		 *
		 * @api reportError
		 * @param IError $error Error which occurred.
		 */
		public function reportError(IError $error): void {
			$this->error = $error;
			$this->report->appendLine($error->getPrefix() . ' : ' . $error->getName())->indent();
			$this->report->appendLine('> Server: ' . \php_uname());
			$this->report->appendLine('> Message: ' . $error->getMessage());
			$this->report->appendf('> Occurred: %$2s (%$1s)', $t = \time(), \date(\DATE_RFC2822, $t))->newLine();
			$this->report->appendf('> Script: %s (Line %s)', $error->getFile(), $error->getLine());

			$debug = $error->getDebugData();
			if ($debug !== null) {
				$this->report->newline()->newline();
				$this->report->appendf('> Exception debug data:')->indent()->newline();
				$this->report->appendf('> Debug: %s', \var_export($debug, true));
			}
		}

		/**
		 * Format debug data and add it to the report.
		 *
		 * @param array<string, mixed> $debug Key/Value pairs
		 */
		public function reportDebug(array $debug): void
		{
			$this->report->newLine()->newLine();
			$this->report->appendf('> Application debug data:')->indent()->newLine();
			foreach ($debug as $key => $value) {
				$this->report->appendf('%s = %s', $key, $value)->newLine();
			}
			$this->report->outdent()->newLine();
		}

		/**
		 * Format a stacktrace and add it to the report.
		 *
		 * @api reportStacktrace
		 * @param array<int, StackFrame> $trace Stacktrace.
		 */
		public function reportStacktrace(array $trace): void {
			$this->report->newLine()->newLine();
			$this->report->appendf('> Stack trace [%s steps]:', \count($trace))->indent()->newLine();
			foreach($trace as $node) {
				$args = [];
				foreach ($node['args'] ?? [] as $key => $arg)
					$args[$key] = StringUtil::variableAsString($arg);

				$this->report->appendf(
					'%s:%s - %s%s%s(%s)',
					$node['file'] ?? 'interpreter',
					$node['line'] ?? '?',
					$node['class'] ?? '',
					$node['type'] ?? '',
					$node['function'],
					\implode(', ', $args)
				)->newLine();
			}
			$this->report->outdent()->newline();
		}

		/**
		 * Get the content-type of this error report.
		 *
		 * @api getContentType
		 * @return string
		 */
		public function getContentType(): string {
			return ($this->wrapPreTags ? 'text/html' : 'text/plain') . '; charset=utf-8';
		}

		/**
		 * Get the extension to use when this report is stored to a file.
		 *
		 * @api getExtension
		 * @return string
		 */
		public function getExtension(): string {
			return $this->wrapPreTags ? '.html' : '.log';
		}

		/**
		 * Compile the report into a string.
		 *
		 * @api __toString
		 * @return string
		 */
		public function __toString(): string {
			$this->report->outdent()->newLine();
			$this->report->appendf('Report generated automatically on %$2s (%$1s).', $t = \time(), \date(\DATE_RFC2822, $t));

			if ($this->wrapPreTags)
				$this->report->prepend('<pre>')->append('</pre>');

			return (string) $this->report;
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

			$this->report->outdent()->newLine();
			$this->report->appendf('Report generated automatically on %$2s (%$1s).', $t = \time(), \date(\DATE_RFC822, $t));

			if ($this->wrapPreTags)
				$this->report->prepend('<pre>')->append('</pre>');

			$contentType = ($this->wrapPreTags ? 'text/html' : 'text/plain') . '; charset=utf-8';
			$extension = $this->wrapPreTags ? '.html' : '.log';

			$this->timer->stop();
			$report = $this->report . $this->timer->format($this->lineEnd . 'Generated in %.4fs.');

			return new ErrorReport($error, $contentType, $extension, $report);
		}

		/**
		 * @var Timer
		 */
		protected Timer $timer;

		/**
		 * @var IError|null
		 */
		protected ?IError $error = null;

		/**
		 * @var StringBuilder
		 */
		protected StringBuilder $report;
	}