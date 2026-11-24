<?php
	require_once(__DIR__ . "/../src/Timing/Benchmarking/Benchmark.php");

	use KrameWork\Timing\Benchmarking\Benchmark;

	class BenchmarkTest extends PHPUnit\Framework\TestCase
	{
		public function testBenchmark() {
			$benchmark = new class (20, 100) extends Benchmark {
				/**
				 * Overwrite and include the code to benchmark inside this function.
				 */
				public function execute(): void {
					usleep(1000);
				}
			};

			$result = $benchmark->runTest();
			$this->assertGreaterThanOrEqual(2, $result->getElapsed(), "Benchmark didn't run for expected forced time.");
			$this->assertEquals('Benchmark1', $result->getName());
		}

		/**
		 * Constructing a benchmark with fewer than two sets throws an exception.
		 */
		public function testConstructRejectsSetCountBelowTwo() {
			$this->expectException(InvalidArgumentException::class);
			new class (1) extends Benchmark {
				/**
				 * Overwrite and include the code to benchmark inside this function.
				 */
				public function execute(): void {}
			};
		}
	}