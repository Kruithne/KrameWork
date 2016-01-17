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
	$schema->update();
	$test->create((object)array('value' => sprintf('%s test data', date('r'))));
	foreach($test->read() as $row)
		printf("%s\n", $row->value);
?>
