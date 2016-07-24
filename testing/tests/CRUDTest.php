<?php
	require_once("/home/travis/build/Kruithne/KrameWork/testing/resources/default_bootstrap.php");

	class CRUDTest extends PHPUnit_Framework_TestCase
	{
		public function testCreateReturnsNewObject()
		{
			$crud = $this->prepare();
			$id = time();
			$result = $crud->create((object)array('a' => $id, 'b' => '42', 'c' => '..'));
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
			$result = $crud->read(array('a' => $id, 'b' => '*'));
			$this->assertEquals($id * 9, array_sum($result), 'Reading an object using a partial key did not return expected value.');
		}

		public function testReadByUnknownKeyReturnsNull()
		{
			$crud = $this->prepare();
			$id = time();
			$result = $crud->read(array('a' => $id, 'b' => '-2'));
			$this->assertEquals(null, $result, 'Reading an object using an unknown key did not return expected value.');
		}

		public function testQueryBuilder()
		{
			$db = MockDatabaseConnection::Get('mysql');
			$manager = new MockSchemaManager($db);
			$crud = new MockCRUD($manager);
			$db->begin();
			$result = $crud->search('value')->notNull()->andColumn('value')->lessThan(5)->execute();
			$sql = $db->end();
			$this->assertEquals('SELECT * FROM __mock__ WHERE value IS NOT NULL AND value < :value2', $sql, 'Generated SQL mismatch');
		}

		public function testExecute()
		{
			$db = new MockDatabaseConnection('mysql');
			$manager = new MockSchemaManager($db);
			$crud = new MockCRUD($manager);
			$db->begin();
			$crud->executeSQL('UPDATE foo SET x = 1 WHERE y > 4');
			$sql = $db->end();
			$this->assertEquals('UPDATE foo SET x = 1 WHERE y > 4', $sql, 'SQL execution mismatch');
		}

		private function prepare()
		{
			$db = new MockDatabaseConnection('mysql');
			$db->setFactory(
				'SELECT * FROM __mock__ WHERE a = :a AND b = :b',
				create_function('$map', 'return $map["b"] < 0 ? Array() : Array(new KW_DataContainer($map));')
			);
			$db->setFactory(
				'SELECT * FROM __mock__ WHERE (:a_null = 1 OR a = :a) AND (:b_null = 1 OR b = :b)',
				create_function('$map', 
					'if($map["b"] < 0) return Array();'.
					'if($map["b"] > 0 && !isset($map["b_null"])) return Array(new KW_DataContainer($map));'.
					'$set = array();'.
					'for($i = 1; $i < 10; ++$i)'.
						'$set[] = new KW_DataContainer(Array("a" => $map["a"], "b" => $i));'.
					'return $set;'
				)
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
