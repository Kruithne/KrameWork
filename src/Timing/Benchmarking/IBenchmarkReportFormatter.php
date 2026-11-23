<?php
	namespace KrameWork\Timing\Benchmarking;

	/**
	 * Interface IBenchmarkReportFormatter
	 *
	 * @package KrameWork\Timing\Benchmarking
	 * @author Kruithne (kruithne@gmail.com)
	 */
	interface IBenchmarkReportFormatter
	{
		/**
		 * Format the given results.
		 *
		 * @api
		 * @param BenchmarkResult[] $results
		 * @return string
		 */
		public function format(array $results):string;
	}