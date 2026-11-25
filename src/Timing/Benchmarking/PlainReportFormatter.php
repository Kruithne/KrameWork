<?php
	namespace KrameWork\Timing\Benchmarking;

	use KrameWork\Utils\StringBuilder;

	require_once(__DIR__ . '/IBenchmarkReportFormatter.php');
	require_once(__DIR__ . '/../../Utils/StringBuilder.php');

	/**
	 * Class PlainReportFormatter
	 * Plain-text formatter for benchmark reports.
	 *
	 * @package KrameWork\Timing\Benchmarking
	 */
	class PlainReportFormatter implements IBenchmarkReportFormatter
	{
		/**
		 * Format the given results.
		 *
		 * @api
		 * @param BenchmarkResult[] $results
		 * @return string
		 */
		public function format(array $results): string {
			$this->builder = new StringBuilder();

			$this->addField('Average');
			$this->addField('Elapsed');
			$this->addField('Shortest');
			$this->addField('Longest');
			$this->addField('StdDev');
			$this->addField('Sets');
			$this->addField('Execs (p/set)');
			$this->builder->newLine();

			foreach ($results as $result) {
				$this->addField($result->getAverageFormatted());
				$this->addField($result->getElapsedFormatted());
				$this->addField($result->getShortestFormatted());
				$this->addField($result->getLongestFormatted());
				$this->addField($result->getStandardDeviationFormatted());
				$this->addField($result->getSetCount());
				$this->addField($result->getExecutionsPerSet());
				$this->addField('// ' . $result->getName());
				$this->builder->newLine();
			}

			return $this->builder;
		}

		/**
		 * Add a formatted field to the target string builder.
		 *
		 * @internal
		 * @param string|int $content
		 */
		private function addField(string|int $content): void
		{
			$this->builder->append(\str_pad((string) $content, 10), "\t");
		}

		/**
		 * @var StringBuilder
		 */
		private StringBuilder $builder;
	}