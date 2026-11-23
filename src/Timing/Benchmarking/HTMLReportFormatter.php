<?php
	namespace KrameWork\Timing\Benchmarking;

	use KrameWork\Utils\StringBuilder;

	require_once(__DIR__ . '/IBenchmarkReportFormatter.php');
	require_once(__DIR__ . '/../../Utils/StringBuilder.php');

	class HTMLReportFormatter implements IBenchmarkReportFormatter
	{
		/**
		 * Format the given results.
		 *
		 * @api
		 * @param BenchmarkResult[] $results
		 * @return string
		 */
		public function format(array $results): string {
			/*
			 * <table>
			 * 		<tr>
			 * 			<td></td>
			 * 		</tr>
			 * </table>
			 */
			$builder = new StringBuilder();
			$builder->append('<table><tr>');
			$builder->append('<th>Benchmark</th>', '<th>Average</th>', '<th>Elapsed</th>');
			$builder->append('<th>Shortest</th>', '<th>Longest</th>', '<th>StdDev</th>');
			$builder->append('<th>Sets</th>', '<th>Execs (p/set)</th>');
			$builder->append('</tr>');

			foreach ($results as $result) {
				$builder->append('<tr>');
				$builder->append('<td>', $result->getName(), '</td>');
				$builder->append('<td>', $result->getAverageFormatted(), '</td>');
				$builder->append('<td>', $result->getElapsedFormatted(), '</td>');
				$builder->append('<td>', $result->getShortestFormatted(), '</td>');
				$builder->append('<td>', $result->getLongestFormatted(), '</td>');
				$builder->append('<td>', $result->getStandardDeviationFormatted(), '</td>');
				$builder->append('<td>', $result->getSetCount(), '</td>');
				$builder->append('<td>', $result->getExecutionsPerSet(), '</td>');
				$builder->append('</tr>');
			}

			$builder->append('</table');
			return $builder;
		}
	}