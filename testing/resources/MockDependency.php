<?php
	class MockDependency implements IMockDependency
	{
		public function __construct()
		{
		}

		public function test()
		{
			return $this->test;
		}

		public function set($value)
		{
			$this->test = $value;
		}

		private $test;
	}
?>
