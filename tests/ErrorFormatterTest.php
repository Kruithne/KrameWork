<?php
	use KrameWork\Runtime\ErrorFormatters\HTMLErrorFormatter;
	use KrameWork\Runtime\ErrorFormatters\IErrorFormatter;
	use KrameWork\Runtime\ErrorFormatters\JSONErrorFormatter;
	use KrameWork\Runtime\ErrorFormatters\MarkdownErrorFormatter;
	use KrameWork\Runtime\ErrorFormatters\PlainTextErrorFormatter;
	use PHPUnit\Framework\Attributes\DataProvider;

	require_once(__DIR__ . '/../src/Runtime/ErrorFormatters/HTMLErrorFormatter.php');
	require_once(__DIR__ . '/../src/Runtime/ErrorFormatters/JSONErrorFormatter.php');
	require_once(__DIR__ . '/../src/Runtime/ErrorFormatters/MarkdownErrorFormatter.php');
	require_once(__DIR__ . '/../src/Runtime/ErrorFormatters/PlainTextErrorFormatter.php');

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
	}
