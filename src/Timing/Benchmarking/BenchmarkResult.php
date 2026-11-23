<?php
	namespace KrameWork\Timing\Benchmarking;

	/**
	 * Class BenchmarkResult
	 * Contains results from a benchmark.
	 *
	 * @package KrameWork\Timing\Benchmarking
	 * @author Kruithne (kruithne@gmail.com)
	 */
	class BenchmarkResult
	{
		/**
		 * BenchmarkResult constructor.
		 *
		 * @api
		 * @param float $average Average cycle time.
		 * @param float $shortest Shortest cycle time.
		 * @param float $longest Longest cycle time.
		 * @param float $elapsed Elapsed benchmark time.
		 * @param float $stddev Standard deviation.
		 * @param int $sets Amount of sets to execute.
		 * @param int $execs Executions per set.
		 * @param string|null $name Name of the benchmark.
		 */
		public function __construct(float $average, float $shortest, float $longest, float $elapsed, float $stddev, int $sets, int $execs, ?string $name = null) {
			$this->average = $average;
			$this->shortest = $shortest;
			$this->longest = $longest;
			$this->elapsed = $elapsed;
			$this->stddev = $stddev;
			$this->sets = $sets;
			$this->executions = $execs;
			$this->name = $name ?? 'Benchmark' . self::$index++;

			$this->format = '%.5e';
		}

		/**
		 * Set the format used for formatted methods.
		 *
		 * @api
		 * @param string $format String format.
		 */
		public function setFormat(string $format): void {
			$this->format = $format;
		}

		/**
		 * Get the benchmark name.
		 *
		 * @api
		 * @return string
		 */
		public function getName():string {
			return $this->name;
		}

		/**
		 * Get the average cycle time.
		 *
		 * @api
		 * @return float
		 */
		public function getAverage():float {
			return $this->average;
		}

		/**
		 * Get the average cycle time (formatted).
		 *
		 * @api
		 * @return string
		 */
		public function getAverageFormatted():string {
			return \sprintf($this->format, $this->average);
		}

		/**
		 * Get the shortest cycle time.
		 *
		 * @api
		 * @return float
		 */
		public function getShortest():float {
			return $this->shortest;
		}

		/**
		 * Get the shortest cycle time (formatted).
		 *
		 * @api
		 * @return string
		 */
		public function getShortestFormatted():string {
			return \sprintf($this->format, $this->shortest);
		}

		/**
		 * Get the longest cycle time.
		 *
		 * @api
		 * @return float
		 */
		public function getLongest():float {
			return $this->longest;
		}

		/**
		 * Get the longest cycle time (formatted).
		 *
		 * @api
		 * @return string
		 */
		public function getLongestFormatted():string {
			return \sprintf($this->format, $this->longest);
		}

		/**
		 * Get the total elapsed time.
		 *
		 * @api
		 * @return float
		 */
		public function getElapsed():float {
			return $this->elapsed;
		}

		/**
		 * Get the total elapsed time (formatted).
		 *
		 * @api
		 * @return string
		 */
		public function getElapsedFormatted():string {
			return \sprintf($this->format, $this->elapsed);
		}

		/**
		 * Get the standard deviation.
		 *
		 * @api
		 * @return float
		 */
		public function getStandardDeviation():float {
			return $this->stddev;
		}

		/**
		 * Get the standard deviation (formatted).
		 *
		 * @api
		 * @return string
		 */
		public function getStandardDeviationFormatted():string {
			return \sprintf($this->format, $this->stddev);
		}

		/**
		 * Get the set count.
		 *
		 * @api
		 * @return int
		 */
		public function getSetCount():int {
			return $this->sets;
		}

		/**
		 * Get how many executions occur per set.
		 *
		 * @api
		 * @return int
		 */
		public function getExecutionsPerSet():int {
			return $this->executions;
		}

		/**
		 * @var string
		 */
		private string $name;

		/**
		 * @var float
		 */
		private float $average;

		/**
		 * @var float
		 */
		private float $shortest;

		/**
		 * @var float
		 */
		private float $longest;

		/**
		 * @var float
		 */
		private float $elapsed;

		/**
		 * @var int
		 */
		private int $sets;

		/**
		 * @var float
		 */
		private float $stddev;

		/**
		 * @var int
		 */
		private int $executions;

		/**
		 * @var string
		 */
		private string $format;

		/**
		 * @var int
		 */
		private static int $index = 1;
	}