<?php
	class MockSchemaManager implements ISchemaManager
	{
		public function __construct(IDatabaseConnection $db)
		{
			$this->db = $db;
		}

		public function addTable($spec)
		{
			$this->tables[$spec->getName()] = $spec;
			if($spec instanceof IRepository)
			{
				$spec->setDB($this->db);
				$spec->prepare();
			}
		}

		public function __get($name)
		{
			if(isset($this->tables[$name]))
				return $this->tables[$name];
		}

		public function update()
		{
			$this->loadVersionTable();
			foreach($this->tables as $spec)
				if($spec->getVersion() > $this->getCurrentVersion($spec->getName()))
					$this->upgrade($spec);
		}

		public function upgrade($spec)
		{
			$from = $this->getCurrentVersion($spec->getName());
			$to = $spec->getVersion();
			error_log('Updating '.$spec->getName().' from '.$from.' to '.$to);
			$this->version[$spec->getName()] = $to;
		}

		public function getCurrentVersion($table)
		{
			if(!isset($this->version[$table]))
				return 0;
			return $this->version[$table];
		}

		public function loadVersionTable()
		{
		}

		private $version = array();
		private $tables;
		private $db;
	}
?>
