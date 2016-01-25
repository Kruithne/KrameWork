<?php
	class ExampleBenchmarkTest extends KW_BenchmarkTest
	{
		/**
		 * Execute a cycle.
		 */
		public function executeCycle()
		{
			// This is where you put code you wish to benchmark.
			// Here we just hash a timestamp using MD5; not exciting.
			md5(time());
		}
	}
?>