<?php
	namespace KrameWork\Timing\Benchmarking;

	use KrameWork\Utils\StringBuilder;

	require_once(__DIR__ . '/IBenchmarkReportFormatter.php');
	require_once(__DIR__ . '/../../Utils/StringBuilder.php');

	/**
	 * Class MarkdownReportFormatter
	 * Markdown formatter for benchmark reports.
	 *
	 * @package KrameWork\Timing\Benchmarking
	 * @author Kruithne (kruithne@gmail.com)
	 */
	class MarkdownReportFormatter implements IBenchmarkReportFormatter
	{
		/**
		 * Format the given results.
		 *
		 * @api
		 * @param BenchmarkResult[] $results
		 * @return string
		 */
		public function format(array $results): string {
			$builder = new StringBuilder();
			$builder->setSeparator(' | ');
			$builder->append('Benchmark', 'Average', 'Elapsed', 'Shortest');
			$builder->append('Longest', 'StdDev', 'Sets', 'Execs (p/set)');
			$builder->newLine()->repeat('---', 8);

			foreach ($results as $result) {
				$builder->newLine();
				$builder->append($result->getName(), $result->getAverageFormatted(), $result->getElapsedFormatted());
				$builder->append($result->getShortestFormatted(), $result->getLongestFormatted(), $result->getStandardDeviationFormatted());
				$builder->append($result->getSetCount(), $result->getExecutionsPerSet());
			}

			return $builder;
		}
	}