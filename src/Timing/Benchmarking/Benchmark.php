<?php
	namespace KrameWork\Timing\Benchmarking;

	require_once(__DIR__ . '/BenchmarkResult.php');

	/**
	 * Interface IBenchmark
	 * @package KrameWork\Timing\Benchmarking
	 */
	interface IBenchmark
	{
		/**
		 * Initiate the benchmark test.
		 * @return BenchmarkResult
		 */
		public function runTest():BenchmarkResult;

		/**
		 * Overwrite and include the code to benchmark inside this function.
		 */
		public function execute(): void;

		/**
		 * Called just before the test is run.
		 */
		public function onStart(): void;

		/**
		 * Called just after the test is run.
		 */
		public function onEnd(): void;
	}

	/**
	 * Class Benchmark
	 * @package KrameWork\Timing\Benchmarking
	 */
	abstract class Benchmark implements IBenchmark
	{
		/**
		 * Benchmark constructor.
		 *
		 * @api __construct
		 * @param int $sets How many execution cycles?
		 * @param int $executionsPerCycle How many executions per cycle?
		 * @param string $name A name to identify this benchmark.
		 */
		public function __construct(private int $sets = 200, private int $executionsPerCycle = 1000, private ?string $name = null) {}

		/**
		 * Called just before the test is run.
		 * Overwrite to obtain functionality.
		 *
		 * @api onStart
		 */
		public function onStart(): void {
			// Overwrite to obtain functionality.
		}

		/**
		 * Called just after the test is run.
		 * Overwrite to obtain functionality.
		 *
		 * @api onEnd
		 */
		public function onEnd(): void {
			// Overwrite to obtain functionality.
		}

		/**
		 * Initiate the test.
		 *
		 * @api runTest
		 * @return BenchmarkResult
		 */
		public function runTest():BenchmarkResult {
			$this->onStart();

			$testStartTime = microtime(true); // Test start time.
			$cycleTimes = array_fill(0, $this->sets, 0); // Pre-allocate.

			// Execute the sets.
			for ($i = 0; $i < $this->sets; $i++) {
				$cycleStartTime = microtime(true);

				for ($e = 0; $e < $this->executionsPerCycle; $e++)
					$this->execute();

				$cycleTimes[$i] = microtime(true) - $cycleStartTime;
			}

			$testEndTime = microtime(true) - $testStartTime; // Grab the test end time.

			$result = new BenchmarkResult(
				array_sum($cycleTimes) / count($cycleTimes), // Average time
				min($cycleTimes), // Shortest cycle time
				max($cycleTimes), // Longest cycle time
				$testEndTime, // Elapsed time
				$this->std_arr($cycleTimes),
				$this->sets, // Set count
				$this->executionsPerCycle, // Executions per set.
				$this->name // Benchmark name
			);

			$this->onEnd();
			return $result;
		}

		/**
		 * Calculate the square of value-mean.
		 *
		 * @internal
		 * @param int|float $value
		 * @param int|float $mean
		 * @return int|float
		 */
		private function std_square(int|float $value, int|float $mean): int|float {
			return pow($value - $mean, 2);
		}

		/**
		 * Calculate the standard deviation of an array.
		 *
		 * @internal
		 * @param array $arr
		 * @return float
		 */
		private function std_arr(array $arr): float {
			$size = count($arr);
			return sqrt(array_sum(array_map([$this, 'std_square'], $arr, array_fill(0, $size,(array_sum($arr) / $size)))) / ($size - 1));
		}
	}