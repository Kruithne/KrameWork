<?php
	class MockManyInjector implements IManyInject
	{
		public function __construct($data)
		{
			$this->data = $data;
		}

		public function set($data)
		{
			$this->data = $data;
		}

		public function getComponents($type)
		{
			return $this->data;
		}

		private $data;
	}
