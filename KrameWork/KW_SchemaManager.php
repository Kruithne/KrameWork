<?php
	class KW_SchemaManager implements ISchemaManager
	{
		/**
		 * KW_SchemaManager constructor.
		 * @param IDatabaseConnection $db
		 */
		public function __construct(IDatabaseConnection $db, IManyInject $repositories)
		{
			$this->repositories = $repositories;
			$this->db = $db;
			$this->addTable(new KW_MetaTable());
		}

		/**
		 * Add a new table to be managed.
		 *
		 * @param ISchemaTable $spec A table specification.
		 * @throws Exception
		 */
		public function addTable(ISchemaTable $spec)
		{
			if (isset($this->tables[$spec->getName()]))
				throw new Exception('Duplicate table specification "' . $spec->getName() . '"');

			$this->tables[$spec->getName()] = $spec;

			if ($spec instanceof IRepository)
			{
				$spec->setDB($this->db);
				$spec->prepare();
			}
		}

		/**
		 * Fetch a table by name
		 *
		 * @param string $name Name of table to return the specification of.
		 * @return ISchemaTable
		 */
		public function __get($name)
		{
			if (isset($this->tables[$name]))
				return $this->tables[$name];

			return null;
		}

		/**
		 * Called to execute schema management once all tables have been defined.
		 * @var bool $verbose Print messages
		 */
		public function update($verbose = false)
		{
			$this->loadVersionTable();

			foreach ($this->repositories->getComponents('IRepository') as $spec)
			{
				if ($verbose)
					printf("Repository %s is at version %d\n", $spec->getName(), $this->getCurrentVersion($spec->getName()));
				if ($spec->getVersion() > $this->getCurrentVersion($spec->getName()))
				{
					printf("Upgrading to version %d\n", $spec->getVersion());
					$this->upgrade($spec);
				}
			}
		}

		/**
		 * Auto-update a table according to the specification.
		 *
		 * @param ISchemaTable $spec The table specification to act upon.
		 */
		public function upgrade(ISchemaTable $spec)
		{
			if ($this->db->getType() == 'pgsql')
			{
				$this->_metatable->create->table = $spec->getName();
				$this->_metatable->create->execute();
			}

			$save = $this->_metatable->save;
			$sql = $spec->getQueries();
			$from = $this->getCurrentVersion($spec->getName());
			$to = $spec->getVersion();

			error_log('Updating ' . $spec->getName() . ' from ' . $from . ' to ' . $to);

			for ($i = $from + 1; $i <= $to; ++$i)
			{
				if (isset($sql[$i]))
					foreach ($sql[$i] as $step)
						$this->db->execute($step);

				$save->table = $spec->getName();
				$save->version = $i;
				$save->execute();
				$this->version[$spec->getName()] = $to;
			}
		}

		/**
		 * Read the current table version.
		 *
		 * @param string $table Name of table whose version is wanted.
		 * @return int
		 */
		public function getCurrentVersion($table)
		{
			if (!isset($this->version[$table]))
				return 0;

			return $this->version[$table];
		}

		/**
		 * Load current version information from the database.
		 */
		public function loadVersionTable()
		{
			if (!$this->_metatable->exists->execute()->getRows())
				return;

			foreach ($this->_metatable->load->execute()->getRows() as $row)
				$this->version[$row->table] = $row->version;
		}

		/**
		 * @var array
		 */
		private $version = array();

		/**
		 * @var ISchemaTable[]
		 */
		private $tables;

		/**
		 * @var IDatabaseConnection
		 */
		private $db;

		/**
		 * @var IManyInject
		 */
		private $repositories;
	}
?>
