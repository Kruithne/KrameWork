<?php
	use KrameWork\Runtime\ErrorFormatters\HTMLErrorFormatter;
	use KrameWork\Runtime\ErrorFormatters\IErrorFormatter;
	use KrameWork\Runtime\ErrorFormatters\JSONErrorFormatter;
	use KrameWork\Runtime\ErrorFormatters\MarkdownErrorFormatter;
	use KrameWork\Runtime\ErrorFormatters\PlainTextErrorFormatter;
	use KrameWork\Runtime\ErrorTypes\ExceptionError;
	use PHPUnit\Framework\Attributes\DataProvider;

	require_once(__DIR__ . '/../src/Runtime/ErrorFormatters/HTMLErrorFormatter.php');
	require_once(__DIR__ . '/../src/Runtime/ErrorFormatters/JSONErrorFormatter.php');
	require_once(__DIR__ . '/../src/Runtime/ErrorFormatters/MarkdownErrorFormatter.php');
	require_once(__DIR__ . '/../src/Runtime/ErrorFormatters/PlainTextErrorFormatter.php');
	require_once(__DIR__ . '/../src/Runtime/ErrorTypes/ExceptionError.php');

	class ErrorFormatterTest extends PHPUnit\Framework\TestCase
	{
		/**
		 * Provide one instance of each error formatter.
		 *
		 * @return array<string, array{IErrorFormatter}>
		 */
		public static function formatterProvider(): array {
			return [
				'html' => [new HTMLErrorFormatter()],
				'json' => [new JSONErrorFormatter()],
				'markdown' => [new MarkdownErrorFormatter()],
				'plaintext' => [new PlainTextErrorFormatter()]
			];
		}

		/**
		 * Test that generate() throws a LogicException when no error was reported.
		 */
		#[DataProvider('formatterProvider')]
		public function testGenerateWithoutErrorThrows(IErrorFormatter $formatter): void {
			$this->expectException(\LogicException::class);
			$formatter->beginReport();
			$formatter->generate();
		}

		/**
		 * Test that the HTML formatter converts non-string debug values to strings.
		 */
		public function testHTMLFormatterConvertsDebugValuesToString(): void {
			$formatter = new HTMLErrorFormatter();
			$formatter->beginReport();
			$formatter->reportError(new ExceptionError(new \Exception('test')));
			$formatter->reportDebug(['list' => [1, 2]]);

			$this->assertStringContainsString('(array) 2 items', (string)$formatter->generate());
		}

		/**
		 * Test that the HTML formatter does not keep debug data from a previous report.
		 */
		public function testHTMLFormatterClearsDebugDataBetweenReports(): void {
			$formatter = new HTMLErrorFormatter();
			$formatter->beginReport();
			$formatter->reportError(new ExceptionError(new \Exception('first')));
			$formatter->reportDebug(['staleKey' => 'staleValue']);
			$formatter->generate();

			$formatter->beginReport();
			$formatter->reportError(new ExceptionError(new \Exception('second')));

			$this->assertStringNotContainsString('staleKey', (string)$formatter->generate());
		}
	}
