<?php
	class MockDecorator implements IMockDependency, IDecorator
	{
		public function __construct(IMockDependency $target)
		{
			$this->target = $target;
		}

		public function inject($target)
		{
			$this->target = $target;
		}

		public function set($value)
		{
			$this->value = $value;
		}

		public function test()
		{
			return $this->target->test() . $value;
		}

		private $target;
		private $value = '+';
	}
?>
