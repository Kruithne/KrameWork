<?php
	class MockDecorator implements IMockDependency
	{
		public function __construct(IMockDependency $target)
		{
			$this->target = $target;
		}

		private $target;
	}
?>
