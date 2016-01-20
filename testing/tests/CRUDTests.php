<?php
	require_once("/home/travis/build/Kruithne/KrameWork/testing/resources/default_bootstrap.php");

	class CRUDTest extends PHPUnit_Framework_TestCase
	{
		public function testCreateReturnsNewObject()
		{
		}

		public function testReadByKeyReturnsNewObject()
		{
			$crud = $this->prepare();
			$id = time();
			$result = $crud->read($id);
			$this->assertEquals($id, $result, 'Reading an object using a key did not return expected value.');
		}

		public function testReadByPartialKeyReturnsNewObjects()
		{
		}

		public function testReadByUnknownKeyReturnsNull()
		{
		}

		private function prepare()
		{
			$db = MockDatabaseConnection::Get('mysql');
			$db->setFactroy(
				'SELECT * FROM __mock__ WHERE id = :id',
				create_function('$map', 'return Array(new KW_DataContainer($map));')
			);
			$manager = new MockSchemaManager($db);
			$crud = new MockCRUD(
				$manager, 'id', true, array('value'), '__mock__', 1, false,
				create_function('$data', 'return $data->id;')
			);
			return $crud;
		}
	}
?>
