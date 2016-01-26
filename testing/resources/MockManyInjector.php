<?php
	class MockManyInjector implements IManyInject
	{
		public function __construct($data)
		{
			$this->data = $data;
		}

		public function getComponents(string $type)
		{
			return $this->data;
		}

		private $data;
	}
?>
