<?php
	class MockCRUD extends KW_CRUD
	{
		public function __construct(
			ISchemaManager $schema,
			$key = 'id', $auto = true,
			$values = array('value'),
			$name = '__mock__', $version = 1,
			$queries = array(1 => array('CREATE __mock__'))
		)
		{
			$this->key = $key;
			$this->auto = $auto;
			$this->values = $values;
			$this->name = $name;
			$this->version = $version;
			$this->queries = $queries;
			parent::__construct($schema);
		}

		public function getKey() { return $this->key; }
		public function hasAutoKey() { return $this->auto; }
		public function getValues() { return $this->values; }
		public function getName() { return $this->name; }
		public function getVersion() { return $this->version; }
		public function getQueries() { return $this->queries; }

		private $key;
		private $auto;
		private $values;
		private $name;
		private $version;
		private $queries;
	}
?>
