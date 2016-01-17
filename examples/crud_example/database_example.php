<?php
	require_once('../../KrameWork/KrameSystem.php'); // We need this.

	$system = new KrameSystem(KW_DEFAULT_FLAGS & ~KW_ENABLE_SESSIONS); // Create a system without sessions.

	// Start a new database connection.
	$db = new KW_DatabaseConnection('sqlite:test_database.sq3', null, null);

	class TestTable extends KW_CRUD
	{
		public function __construct(ISchemaManager $schema)
		{
			parent::__construct($schema);
		}

		public function getName() { return 'demo'; }
		public function getVersion() { return 1; }
		public function getKey() { return 'rowid'; }
		public function hasAutoKey() { return true; }
		public function getValues() { return array('value'); }

		public function getQueries()
		{
			return array(
				1 => array('CREATE TABLE demo(value TEXT)')
			);
		}
	}

	$schema = new KW_SchemaManager($db);
	$test = new TestTable($schema);
	// You should only call update when you have changed the schema, for best performance.
	$schema->update();

	// Create
	$object = $test->create((object)array('value' => 'example data'));
	$test->create((object)array('value' => sprintf('%s test data', date('r'))));

	// Read
	foreach($test->read() as $row)
		printf("%s\n", $row->value);
	$data = $test->read($object->rowid);
	printf("Object %d : %s\n", $object->rowid, $data->value);

	// Update
	$object->value = 'Replaced data';
	$test->update($object);
	$data = $test->read($object->rowid);
	printf("Object %d : %s\n", $object->rowid, $data->value);

	// Delete
	$test->delete($object);
	// For some reason, sqlite still finds the row after it was deleted..
	//	$data = $test->read($object->rowid);
	//	printf($data ? "Object not deleted\n" : "Object deleted\n");
?>
