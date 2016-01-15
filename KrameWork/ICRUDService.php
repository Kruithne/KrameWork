<?php
	interface ICRUDService extends ICRUD
	{
		public function getOrigin();
		public function getMethod();
		public function execute();
		public function authorized($request);
		public function canCreate($object);
		public function canRead();
		public function canUpdate($object);
		public function canDelete($object);
		public function process($object);
	}
?>
