<?php
	/*
	 * Copyright (c) 2017 Kruithne (kruithne@gmail.com)
	 * https://github.com/Kruithne/KrameWork7
	 *
	 * Permission is hereby granted, free of charge, to any person obtaining a copy
	 * of this software and associated documentation files (the "Software"), to deal
	 * in the Software without restriction, including without limitation the rights
	 * to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
	 * copies of the Software, and to permit persons to whom the Software is
	 * furnished to do so, subject to the following conditions:
	 *
	 * The above copyright notice and this permission notice shall be included in all
	 * copies or substantial portions of the Software.
	 *
	 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
	 * IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
	 * FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
	 * AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
	 * LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
	 * OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE
	 * SOFTWARE.
	 */

	namespace KrameWork\Runtime\ErrorFormatters;

	use KrameWork\Runtime\ErrorReports\ErrorReport;
	use KrameWork\Runtime\ErrorReports\IErrorReport;
	use KrameWork\Runtime\ErrorTypes\IError;
	use KrameWork\Utils\StringUtil;

	require_once(__DIR__ . '/../../Utils/StringUtil.php');
	require_once(__DIR__ . '/../ErrorReports/ErrorReport.php');
	require_once(__DIR__ . '/IErrorFormatter.php');

	class MarkdownErrorFormatter implements IErrorFormatter
	{
		public function beginReport() {
			$this->lines = [];
		}

		public function reportError(IError $error) {
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

		public function reportDebug(array $debug) {
			if (!$debug)
				return;

			$this->addTable('Application Debug Data', $debug);
		}

		public function reportStacktrace(array $trace) {
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
					$node['function'] ?? '',
					\implode(', ', $args)
				);
			}

			$this->addCodeBlock('Stack Trace (' . \count($trace) . ' steps)', \implode("\n", $frames));
		}

		public function reportArray(string $name, array $arr) {
			$this->addTable($name . ' (' . \count($arr) . ' items)', $arr);
		}

		public function reportString(string $name, string $str) {
			$this->addCodeBlock($name, $str);
		}

		public function generate():IErrorReport {
			$this->lines[] = '_Report generated automatically on ' . \date(\DATE_RFC2822) . '._';
			return new ErrorReport($this->error, 'text/markdown; charset=utf-8', '.md', \implode("\n", $this->lines));
		}

		private function addTable(string $title, array $arr) {
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

		private function addCodeBlock(string $title, string $content) {
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

		protected $error;

		protected $lines;
	}
