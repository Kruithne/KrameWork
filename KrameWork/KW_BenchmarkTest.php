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
		 * Results are given in microseconds (µs).
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

			return [
				"execution_time" => microtime(true) - $startTime,
				"average_cycle_time" => array_sum($cycleTimes) / count($cycleTimes),
				"shortest_cycle" => $shortTime,
				"longest_cycle" => $longTime
			];
		}

		/**
		 * How many times will the code be executed for this test.
		 * @var int
		 */
		private $executeCycles;
	}
?>