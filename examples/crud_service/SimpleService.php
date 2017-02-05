<?php
	class SimpleService extends KW_CRUDService
	{
		public function __construct(KW_SchemaManager $schema)
		{
			parent::__construct($schema);
		}

		public function getKey()
		{
			return 'id';
		}

		public function hasAutoKey()
		{
			return true;
		}

		public function getValues()
		{
			return array('name');
		}

		public function getName()
		{
			return '_test';
		}

		public function getVersion()
		{
			return 1;
		}

		public function getQueries()
		{
			return array(
				1 => array('
CREATE TABLE _test (
  id BIGSERIAL NOT NULL,
	name VARCHAR(50) NOT NULL,
	PRIMARY KEY (id)
)'
				)
			);
		}
	}
