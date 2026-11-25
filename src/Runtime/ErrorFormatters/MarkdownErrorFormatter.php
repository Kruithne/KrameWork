<?php
	namespace KrameWork\Runtime\ErrorFormatters;

	use KrameWork\Runtime\ErrorReports\ErrorReport;
	use KrameWork\Runtime\ErrorReports\IErrorReport;
	use KrameWork\Runtime\ErrorTypes\IError;
	use KrameWork\Utils\StringUtil;

	require_once(__DIR__ . '/../../Utils/StringUtil.php');
	require_once(__DIR__ . '/../ErrorReports/ErrorReport.php');
	require_once(__DIR__ . '/IErrorFormatter.php');

	/**
	 * Class MarkdownErrorFormatter
	 * Error report implementation: Markdown
	 *
	 * @package KrameWork\Runtime\ErrorFormatters
	 * @phpstan-import-type StackFrame from IError
	 */
	class MarkdownErrorFormatter implements IErrorFormatter
	{
		public function beginReport(): void {
			$this->lines = [];
		}

		public function reportError(IError $error): void {
			$this->error = $error;
			$this->lines[] = '## ' . $error->getPrefix() . ': ' . $error->getName();
			$this->lines[] = '';
			$this->lines[] = '| | |';
			$this->lines[] = '| --- | --- |';
			$this->lines[] = '| **Message** | ' . $this->cell($error->getMessage()) . ' |';
			$this->lines[] = '| **Server** | ' . $this->cell(\php_uname()) . ' |';
			$this->lines[] = '| **Occurred** | ' . $this->cell(\date(\DATE_RFC2822)) . ' |';
			$this->lines[] = '| **Script** | ' . $this->cell($error->getFile() . ':' . $error->getLine()) . ' |';
			$this->lines[] = '';

			$debug = $error->getDebugData();
			if ($debug !== null)
				$this->addCodeBlock('Exception Debug Data', \var_export($debug, true));
		}

		/**
		 * Format debug data and add it to the report.
		 *
		 * @api reportDebug
		 * @param array<string, mixed> $debug Key/Value pairs
		 */
		public function reportDebug(array $debug): void {
			if (!$debug)
				return;

			$this->addTable('Application Debug Data', $debug);
		}

		/**
		 * Format a stacktrace and add it to the report.
		 *
		 * @api reportStacktrace
		 * @param array<int, StackFrame> $trace Stacktrace.
		 */
		public function reportStacktrace(array $trace): void {
			$frames = [];
			foreach ($trace as $node) {
				$args = [];
				foreach ($node['args'] ?? [] as $key => $arg)
					$args[$key] = StringUtil::variableAsString($arg);

				$frames[] = \sprintf(
					'%s:%s - %s%s%s(%s)',
					$node['file'] ?? 'interpreter',
					$node['line'] ?? '?',
					$node['class'] ?? '',
					$node['type'] ?? '',
					$node['function'],
					\implode(', ', $args)
				);
			}

			$this->addCodeBlock('Stack Trace (' . \count($trace) . ' steps)', \implode("\n", $frames));
		}

		/**
		 * Format an array and add it to the report.
		 *
		 * @api reportArray
		 * @param string $name Name for the array.
		 * @param array<int|string, mixed> $arr Array of data.
		 */
		public function reportArray(string $name, array $arr): void {
			$this->addTable($name . ' (' . \count($arr) . ' items)', $arr);
		}

		public function reportString(string $name, string $str): void {
			$this->addCodeBlock($name, $str);
		}

		public function generate(): IErrorReport {
			$error = $this->error;
			if ($error === null)
				throw new \LogicException('reportError() must be called before generate()');

			$this->lines[] = '_Report generated automatically on ' . \date(\DATE_RFC2822) . '._';
			return new ErrorReport($error, 'text/markdown; charset=utf-8', '.md', \implode("\n", $this->lines));
		}

		/**
		 * Add a key/value table to the report.
		 *
		 * @param string $title Table title.
		 * @param array<int|string, mixed> $arr Array of data.
		 */
		private function addTable(string $title, array $arr): void {
			$this->lines[] = '### ' . $title;
			$this->lines[] = '';

			if (!$arr) {
				$this->lines[] = '_No data to display._';
				$this->lines[] = '';
				return;
			}

			$this->lines[] = '| Key | Value |';
			$this->lines[] = '| --- | --- |';

			foreach ($arr as $key => $value)
				$this->lines[] = '| ' . $this->cell((string) $key) . ' | ' . $this->cell(StringUtil::variableAsString($value)) . ' |';

			$this->lines[] = '';
		}

		private function addCodeBlock(string $title, string $content): void {
			$fence = $this->fence($content, 3);
			$this->lines[] = '### ' . $title;
			$this->lines[] = '';
			$this->lines[] = $fence;
			$this->lines[] = $content;
			$this->lines[] = $fence;
			$this->lines[] = '';
		}

		private function cell(string $value): string {
			$value = \str_replace(['|', "\r", "\n"], ['\|', ' ', ' '], $value);
			$fence = $this->fence($value, 1);
			return $fence . ' ' . $value . ' ' . $fence;
		}

		private function fence(string $content, int $length): string {
			$fence = \str_repeat('`', $length);
			while (\str_contains($content, $fence))
				$fence .= '`';

			return $fence;
		}

		protected ?IError $error = null;

		/**
		 * @var string[]|null
		 */
		protected ?array $lines = null;
	}
