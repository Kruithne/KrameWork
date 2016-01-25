<?php
	abstract class KW_BenchmarkTest implements IBenchmarkTest
	{
		/**
		 * KW_BenchmarkTest constructor.
		 * @param int $executeCycles How many cycles should this test execute?
		 */
		public function __construct($executeCycles = 2000)
		{
			$this->executeCycles = $executeCycles;
		}

		/**
		 * Run the benchmark test and return results in an array.
		 * Results (raw) are given in microseconds (µs).
		 * @return array
		 */
		public function run()
		{
			$startTime = microtime(true);
			$shortTime = null;
			$longTime = null;
			$cycleTimes = [];

			for ($i = 0; $i < $this->executeCycles; $i++)
			{
				$cycleStartTime = microtime(true);
				$this->executeCycle();
				$cycleTime = microtime(true) - $cycleStartTime;

				if ($shortTime == null || $cycleTime < $shortTime)
					$shortTime = $cycleTime;

				if ($longTime == null || $cycleTime > $longTime)
					$longTime = $cycleTime;

				$cycleTimes[] = $cycleTime;
			}

			$execution_time = microtime(true) - $startTime;
			$avg_cycle_time = array_sum($cycleTimes) / count($cycleTimes);

			return [
				'raw' => [
					'execution_time' => $execution_time,
					'average_cycle_time' => $avg_cycle_time,
					'shortest_cycle' => $shortTime,
					'longest_cycle' => $longTime
				],

				'formatted' => [
					'execution_time' => $this->formatTime($execution_time),
					'average_cycle_time' => $this->formatTime($avg_cycle_time),
					'shortest_cycle' => $this->formatTime($shortTime),
					'longest_cycle' => $this->formatTime($longTime)
				]
			];
		}

		/**
		 * @param float $value Value in microseconds.
		 * @return string Milliseconds (rounded to .2 places).
		 */
		private function formatTime($value)
		{
			return sprintf('%.2f', $value / 1000) . "ms";
		}

		/**
		 * How many times will the code be executed for this test.
		 * @var int
		 */
		private $executeCycles;
	}
?>