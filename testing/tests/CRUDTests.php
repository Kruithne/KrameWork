<?php
	require_once("/home/travis/build/Kruithne/KrameWork/testing/resources/default_bootstrap.php");

	class CRUDTest extends PHPUnit_Framework_TestCase
	{
		public function testCreateReturnsNewObject()
		{
			$crud = $this->prepare();
			$id = time();
			$result = $crud->create((object)array('a' => $id, 'b' => '42', 'c' => '..'));
			error_log(serialize($id).serialize($result));
			$this->assertEquals($id, $result, 'Creating a new object did not return expected value.');
		}

		public function testReadByKeyReturnsNewObject()
		{
			$crud = $this->prepare();
			$id = time();
			$result = $crud->read(array('a' => $id, 'b' => 1));
			$this->assertEquals($id, $result, 'Reading an object using a key did not return expected value.');
		}

		public function testReadByPartialKeyReturnsNewObjects()
		{
			$crud = $this->prepare();
			$id = time();
			$result = $crud->read(array('a' => $id, 'b' => '*');
			$this->assertEquals(9, count($result), 'Reading an object using a partial key did not return expected value.');
		}

		public function testReadByUnknownKeyReturnsNull()
		{
			$crud = $this->prepare();
			$id = time();
			$result = $crud->read(array('0' => $id, 'b' => '0');
			$this->assertEquals(null, $result, 'Reading an object using an unknown key did not return expected value.');
		}

		private function prepare()
		{
			$db = MockDatabaseConnection::Get('mysql');
			$db->setFactroy(
				'SELECT * FROM __mock__ WHERE a = :a AND b = :b',
				create_function('$map', 'return $map["a"] == 0 ? Array() : Array(new KW_DataContainer($map));')
			);
			$db->setFactory(
				'SELECT * FROM __mock__ WHERE (:a_null = 1 OR a = :a) AND (:b_null = 1 OR b = :b)',
				create_function('$map', 'if($map["b"] == "*") return Array(new KW_DataContainer($map)); $set = array(); for(var $i = 1; $i < 10; ++$i) $set[] = new KW_DataContainer(Array("a" => $map["a"], "b" => $i)); return $set;')
			);
			$manager = new MockSchemaManager($db);
			$crud = new MockCRUD(
				$manager, array('a','b'), false, array('c'), '__mock__', 1, false,
				create_function('$data', 'return $data->a;')
			);
			return $crud;
		}
	}
?>
