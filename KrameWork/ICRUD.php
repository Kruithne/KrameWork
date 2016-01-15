<?php
	interface ICRUD
	{
		public function getKey();
		public function hasAutoKey();
		public function getValues();
		public function getKeyType($key);
		public function getNewObject($data);
		public function prepare();

		public function create($object);
		public function read($key = null);
		public function update($object);
		public function delete($object);
	}
?>
