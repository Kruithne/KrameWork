<?php
	abstract class KW_CRUD extends KW_Repository implements ICRUD
	{
		public abstract function getKey();
		public abstract function hasAutoKey();
		public abstract function getValues();

		/**
		 * DESCRIPTION
		 * @param TYPE $key
		 * @return int
		 */
		public function getKeyType($key)
		{
			return PDO::PARAM_INT;
		}

		/**
		 * KW_CRUD constructor.
		 * @param ISchemaManager $schema
		 */
		public function __construct(ISchemaManager $schema)
		{
			parent::__construct();
			$schema->addTable($this);
		}

		/**
		 * DESCRIPTION
		 * @param TYPE $data
		 * @return TYPE
		 */
		public function getNewObject($data)
		{
			return $data;
		}

		/**
		 * DESCRIPTION
		 */
		public function prepare()
		{
			$table = $this->getName();
			$key = $this->getKey();
			$values = $this->getValues();
			$serial = $this->hasAutoKey();

			if (is_array($key))
				$this->prepareComposite($table, $key, $values, $serial);
			else if ($key)
				$this->prepareIdentity($table, $key, $values, $serial);
			else
				$this->prepareNonRelational($table, $values);
		}

		/**
		 * DESCRIPTION
		 * @param TYPE $object
		 * @return array|null|TYPE
		 */
		public function create($object)
		{
			$auto = $this->hasAutoKey();

			if($auto)
				$this->bindValues($this->createRecord, $this->getValues(), $object);
			else
				$this->bind($this->createRecord, $object);

			$inserted = $this->createRecord->execute();

			if($auto)
			{
				switch($this->db->getType())
				{
					case 'pgsql':
						$this->getLastID->table = $this->getName();
						$key = $this->getKey();
						$this->getLastID->key = is_array($key) ? $key[0] : $key;
					break;
				}

				$result = $this->getLastID->getRows();
				if ($result && count($result) == 1)
				{
					switch ($this->db->getType())
					{
						case 'pgsql':
							return $this->read($result[0]->currval);

						default:
							return $this->read($result[0]->id);
					}
				}
			}
			return $inserted;
		}

		/**
		 * DESCRIPTION
		 * @param TYPE|null $key
		 * @return array|null|TYPE
		 */
		public function read($key = null)
		{
			// Fetch everything
			if ($key === null)
				return $this->fetchRowSet($this->readAll);

			// Composite key
			if (is_array($key))
			{
				foreach ($key as $col => $val)
				{
					// Wildcard encountered, return a set.
					if (empty($val) || $val == '*')
						return $this->fetchSubSet($key);

					$this->readOne->$col = $val;
				}
			}
			else if ($key) // Fetch a single entry by a simple key
			{
				$kv = $this->getKey();
				$this->readOne->$kv = $key;
			}
			return $this->fetchSingleObject($this->readOne);
		}

		/**
		 * DESCRIPTION
		 * @param $key
		 * @return array
		 */
		private function fetchSubSet($key)
		{
			// Wildcard searches get encoded to (@param_null = 1 OR @param = key) in SQL
			foreach ($key as $col => $val)
			{
				$this->readSet->$col = empty($val) || $val == '*' ? null : $val;
				$col_null = $col.'_null';
				$this->readSet->$col_null = empty($val) || $val == '*' ? 1 : 0;
			}
			return $this->fetchRowSet($this->readSet);
		}

		/**
		 * DESCRIPTION
		 * @param TYPE $object
		 */
		public function update($object)
		{
			$this->bind($this->updateRecord, $object);
			$this->updateRecord->execute();
		}

		/**
		 * DESCRIPTION
		 * @param TYPE $object
		 */
		public function delete($object)
		{
			$this->bindValues($this->deleteRecord, $this->getKey(), $object);
			$this->deleteRecord->execute();
		}

		/**
		 * DESCRIPTION
		 * @param string $query
		 * @return TYPE
		 */
		private function fetchRowSet($query)
		{
			$result = array();

			foreach ($query->getRows() as $data)
				$result[] = $this->getNewObject($data);

			return $result;
		}

		/**
		 * DESCRIPTION
		 * @param string $query
		 * @return null|TYPE
		 */
		private function fetchSingleObject($query)
		{
			$result = $query->getRows();
			if (!$result || count($result) == 0)
				return null;

			if (count($result) == 1)
				return $this->getNewObject($result[0]);

			trigger_error('Multiple rows returned for specified key', E_USER_ERROR);
			return null;
		}

		/**
		 * DESCRIPTION
		 * @param string $query
		 * @param TYPE $object
		 */
		private function bind($query, $object)
		{
			$this->bindValues($query, $this->getKey(), $object);
			$this->bindValues($query, $this->getValues(), $object);
		}

		/**
		 * DESCRIPTION
		 * @param string $query
		 * @param string $field
		 * @param TYPE $object
		 */
		private function bindValues($query, $field, $object)
		{
			if (is_array($field))
			{
				foreach ($field as $col)
					$query->$col = $object->$col;
			}
			else if($field)
			{
				$query->$field = $object->$field;
			}
		}

		/**
		 * DESCRIPTION
		 * @param TYPE $table
		 * @param TYPE $key
		 * @param TYPE $values
		 * @param TYPE $serial
		 */
		private function prepareComposite($table, $key, $values, $serial)
		{
			// Create
			$fields = array_merge($serial ? array() : (is_array($key) ? $key : array($key)), $values);
			$this->createRecord = $this->db->prepare('INSERT INTO '.$table.' ('.join(',', $fields).') VALUES (:'.join(', :',$fields).')');

			switch ($this->db->getType())
			{
				case 'pgsql':
					$this->getLastID = $this->db->prepare('SELECT currval(pg_get_serial_sequence(:table, :key))');
					break;

				case 'sqlite':
					$this->getLastID = $this->db->prepare('SELECT LAST_INSERT_ROWID() AS id');
					break;

				default:
					$this->getLastID = $this->db->prepare('SELECT LAST_INSERT_ID() AS id');
			}

			$filter = array();

			foreach ($key as $col)
				$filter[] = sprintf('(:%1$s_null = 1 OR %1$s = :%1$s)', $col);

			$filter = join(' AND ', $filter);
			$this->readSet = $this->db->prepare('SELECT * FROM '.$table.' WHERE '.$filter);

			foreach ($key as $col)
				$this->readSet->setType($col, $this->getKeyType($col));

			$filter = array();

			foreach ($key as $col)
				$filter[] = sprintf('%1$s = :%1$s', $col);

			$filter = join(' AND ', $filter);
			$fields = array();

			foreach ($values as $col)
				$fields[] = sprintf('%1$s = :%1$s', $col);

			// Read
			$this->readAll = $this->db->prepare('SELECT * FROM '.$table);
			$this->readOne = $this->db->prepare('SELECT * FROM '.$table.' WHERE '.$filter);

			// Update
			$this->updateRecord = $this->db->prepare('UPDATE '.$table.' SET '.join(', ', $fields).' WHERE '.$filter);

			// Delete
			$this->deleteRecord = $this->db->prepare('DELETE FROM '.$table.' WHERE '.$filter);
		}

		/**
		 * DESCRIPTION
		 * @param TYPE $table
		 * @param TYPE $key
		 * @param TYPE $values
		 * @param TYPE $serial
		 */
		private function prepareIdentity($table, $key, $values, $serial)
		{
			// Create
			$fields = array_merge($serial ? array() : array($key), $values);
			$this->createRecord = $this->db->prepare('INSERT INTO '.$table.' ('.join(',', $fields).') VALUES (:'.join(', :',$fields).')');

			switch ($this->db->getType())
			{
				case 'pgsql':
					$this->getLastID = $this->db->prepare('SELECT currval(pg_get_serial_sequence(:table, :key))');
					break;

				case 'sqlite':
					$this->getLastID = $this->db->prepare('SELECT LAST_INSERT_ROWID() AS id');
					break;

				default:
					$this->getLastID = $this->db->prepare('SELECT LAST_INSERT_ID() AS id');
			}

			$filter = sprintf('%1$s = :%1$s', $key);
			$fields = array();

			foreach ($values as $col)
				$fields[] = sprintf('%1$s = :%1$s', $col);

			// Read
			switch ($this->db->getType())
			{
				case 'sqlite':
					$this->readAll = $this->db->prepare('SELECT rowid, * FROM '.$table);
					$this->readOne = $this->db->prepare('SELECT rowid, * FROM '.$table.' WHERE '.$filter);
					break;

				default:
					$this->readAll = $this->db->prepare('SELECT * FROM '.$table);
					$this->readOne = $this->db->prepare('SELECT * FROM '.$table.' WHERE '.$filter);
					break;
			}

			// Update
			$this->updateRecord = $this->db->prepare('UPDATE '.$table.' SET '.join(', ', $fields).' WHERE '.$filter);

			// Delete
			$this->deleteRecord = $this->db->prepare('DELETE FROM '.$table.' WHERE '.$filter);
		}

		/**
		 * DESCRIPTION
		 * @param TYPE $table
		 * @param TYPE $values
		 */
		private function prepareNonRelational($table, $values)
		{
			// Create
			$this->createRecord = $this->db->prepare('INSERT INTO '.$table.' ('.join(',', $values).') VALUES (:'.join(', :',$values).')');

			// Read
			$this->readAll = $this->db->prepare('SELECT * FROM '.$table);

			// Update
			$fields = array();

			foreach ($values as $col)
				$fields[] = sprintf('%1$s = :%1$s', $col);

			$this->updateRecord = $this->db->prepare('UPDATE '.$table.' SET '.join(', ', $fields));

			// Delete
			$this->deleteRecord = $this->db->prepare('DELETE FROM '.$table);
		}
	}
?>
