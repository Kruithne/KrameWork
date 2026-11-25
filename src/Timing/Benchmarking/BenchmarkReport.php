<?php
	namespace KrameWork\Timing\Benchmarking;

	require_once(__DIR__ . '/Benchmark.php');
	require_once(__DIR__ . '/HTMLReportFormatter.php');
	require_once(__DIR__ . '/MarkdownReportFormatter.php');
	require_once(__DIR__ . '/PlainReportFormatter.php');

	/**
	 * Class BenchmarkReport
	 * Reporting tool to combine results of benchmarks.
	 *
	 * @package KrameWork\Timing\Benchmarking
	 */
	class BenchmarkReport
	{
		const FORMAT_PLAIN = 0x1;
		const FORMAT_MARKDOWN = 0x2;
		const FORMAT_HTML = 0x4;

		/**
		 * BenchmarkReport constructor.
		 *
		 * @api __construct
		 * @param int $format Formatter to use, defaults to FORMAT_HTML.
		 */
		public function __construct(int $format = self::FORMAT_HTML) {
			$this->benchmarks = [];

			$this->formatter = match ($format) {
				self::FORMAT_MARKDOWN => new MarkdownReportFormatter(),
				self::FORMAT_HTML => new HTMLReportFormatter(),
				default => new PlainReportFormatter()
			};
		}

		/**
		 * Add one or more benchmarks to this report.
		 *
		 * @api add
		 * @param Benchmark[] ...$benchmarks Variable amount of benchmarks to add.
		 * @return BenchmarkReport
		 */
		public function add(Benchmark ...$benchmarks):BenchmarkReport {
			foreach ($benchmarks as $benchmark)
				$this->benchmarks[] = $benchmark;

			return $this;
		}

		/**
		 * Remove a benchmark from the report.
		 *
		 * @api remove
		 * @param Benchmark $benchmark Benchmark to remove from the report.
		 * @return BenchmarkReport
		 */
		public function remove(Benchmark $benchmark):BenchmarkReport {
			if (($index = \array_search($benchmark, $this->benchmarks)) !== false)
				unset($this->benchmarks[$index]);

			return $this;
		}

		/**
		 * Clear all benchmarks from the report.
		 *
		 * @api clear
		 * @return BenchmarkReport
		 */
		public function clear():BenchmarkReport {
			$this->benchmarks = [];
			return $this;
		}

		/**
		 * Run all tests contained in the report and return a formatted output.
		 *
		 * @api run
		 * @return string
		 */
		public function run():string {
			$results = [];
			foreach ($this->benchmarks as $benchmark)
				$results[] = $benchmark->runTest();

			\usort($results, [$this, 'sortResults']);

			return $this->formatter->format($results);
		}

		/**
		 * Comparator for result sorting.
		 *
		 * @internal
		 * @param BenchmarkResult $a
		 * @param BenchmarkResult $b
		 * @return int
		 */
		public function sortResults(BenchmarkResult $a, BenchmarkResult $b): int {
			if ($a->getAverage() == $b->getAverage()) {
				return 0;
			}
			return ($a->getAverage() < $b->getAverage()) ? -1 : 1;
		}

		/**
		 * Runs all of the tests and returns the output.
		 *
		 * @api __toString
		 * @return string
		 * @link http://php.net/manual/en/language.oop5.magic.php#language.oop5.magic.tostring
		 */
		public function __toString(): string {
			return $this->run();
		}

		/**
		 * @var Benchmark[]
		 */
		private array $benchmarks;

		/**
		 * @var IBenchmarkReportFormatter
		 */
		private IBenchmarkReportFormatter $formatter;
	}