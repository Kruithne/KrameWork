<?php
	class MockCRUD extends KW_CRUD
	{
		public function getKey() { return 'id'; }
		public function hasAutoKey() { return true; }
		public function getValues() { return array('value'); }
		public function getName() { return '__mock__'; }
		public function getVersion() { return 1; }
		public function getQueries()
		{
			return array(1 => array('CREATE __mock__'));
		}
	}
?>
