<?php
	abstract class KW_CRUD extends KW_Repository implements ICRUD
	{
		/**
		 * KW_CRUD constructor.
		 * @param ISchemaManager $schema The system schema manager
		 */
		public function __construct(ISchemaManager $schema)
		{
			parent::__construct();
			$schema->addTable($this);
		}

		/**
		 * Type hint for column types
		 * @param string $key The name of the column
		 * @return int One of the PDO::PARAM_* constants
		 */
		public function getKeyType($key)
		{
			return PDO::PARAM_INT;
		}

		/**
		 * Override this method to return a custom type from the database layer
		 * @param IDataContainer $data 
		 * @return mixed The data you want your row represented as
		 */
		public function getNewObject($data)
		{
			return $data;
		}

		/**
		 * Generates and prepares SQL statements for accessing your table, override this if you want to add some custom queries.
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
		 * Persist an object to the underlying table by calling INSERT on the database
		 * @param object $object An object with properties matching the values, and if not an autokey, keys, of the table spec.
		 * @return mixed An object as returned by getNewObject.
		 */
		public function create($object)
		{
			if(!is_object($object))
				throw new KW_CRUDException('Create operation requires an object');

			$auto = $this->hasAutoKey();

			if ($auto)
				$this->bindValues($this->createRecord, $this->getValues(), $object);
			else
				$this->bind($this->createRecord, $object);

			$this->createRecord->execute();
			if ($auto)
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
				if (!$result || count($result) != 1)
					return null;

				switch ($this->db->getType())
				{
					case 'pgsql':
						return $this->read($result[0]->currval);

					default:
						return $this->read($result[0]->id);
				}
			}

			$key = $this->getKey();
			if (!$key)

				return $this->read();
			if (!is_array($key))
				return $this->read($object->$key);

			$k = array();
			foreach ($key as $col)
				$k[$col] = $object->$col;

			return $this->read($k);
		}

		/**
		 * Fetch an object, or a list of objects.
		 *
		 * Non-relational tables do not have keys, call without a key and always get the entire table.
		 *
		 * Composite key tables expect an array with properties matching the names return in the array from getKey().
		 * To do a partial key match, pass an asterix for a key component to fetch any value of that column.
		 *
		 * Simple key tables expect an int or string with the value of the key column. 
		 * @param mixed $key Descriptor of what to fetch, see description
		 * @return mixed The result set or single object matching the key
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
		 * Fetches a list of objects based on a partial key match
		 * @param string[] $key The key array
		 * @return object[]
		 */
		private function fetchSubSet($key)
		{
			// Wildcard searches get encoded to (@param_null = 1 OR @param = key) in SQL
			foreach ($key as $col => $val)
			{
				$this->readSet->$col = empty($val) || $val == '*' ? null : $val;
				$col_null = $col . '_null';
				$this->readSet->$col_null = empty($val) || $val == '*' ? 1 : 0;
			}
			return $this->fetchRowSet($this->readSet);
		}

		/**
		 * Persist an existing object to the table with an UPDATE query.
		 * @param object $object An object with properties matching all entries of the key and values specifications of the table
		 */
		public function update($object)
		{
			if(!is_object($object))
				throw new KW_CRUDException('Update operation requires an object');
			$this->bind($this->updateRecord, $object);
			$this->updateRecord->execute();
		}

		/**
		 * Remove an existing object from the table with a DELETE query.
		 * @param object $object An object with properties matching all entries of the key specification of the table
		 */
		public function delete($object)
		{
			if(!is_object($object))
				throw new KW_CRUDException('Update operation requires an object');
			$this->bindValues($this->deleteRecord, $this->getKey(), $object);
			$this->deleteRecord->execute();
		}

		/**
		 * Build a search query to load objects from the table.
		 * @param string $column The column to filter by
		 * @return IQueryColumn
		 */
		public function search($column)
		{
			return new KW_QueryBuilder($this->db, $column, null, $this);
		}

		/**
		 * Fetches a set of rows
		 * @param IDatabaseStatement $query An SQL statement to execute.
		 * @return object[] A list of objects as defined by getNewObject.
		 */
		private function fetchRowSet($query)
		{
			$result = array();

			foreach ($query->getRows() as $data)
				$result[] = $this->getNewObject($data);

			return $result;
		}

		/**
		 * Fetches a single object
		 * @param IDatabaseStatement $query An SQL statement to execute.
		 * @return null|object An object as created by getNewObject or null if the query returned no hits.
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
		 * Bind the query to an object for execcution
		 * @param IDatabaseStatement $query An SQL statement to bind values to
		 * @param object $object An object to bind values from
		 */
		private function bind($query, $object)
		{
			$this->bindValues($query, $this->getKey(), $object);
			$this->bindValues($query, $this->getValues(), $object);
		}

		/**
		 * Bind the query to one or more fields from an object
		 * @param IDatabaseStatement $query A statement to bind values to
		 * @param mixed $field The name of a property, or an array of names of properties
		 * @param object $object An object containing the named properties.
		 */
		private function bindValues($query, $field, $object)
		{
			if (is_array($field))
			{
				foreach ($field as $col)
					$this->bindValue($query, $col, $object);
			}
			else
				$this->bindValue($query, $field, $object);
		}

		/**
		 * Bind the query to a property from an object
		 *
		 * @param IDatabaseStatement $query A statement to bind values to
		 * @param string $field The name of a property
		 * @param object $object An object containing the named properties.
		 */
		private function bindValue($query, $field, $object)
		{
			$value = null;
			if(method_exists($object, '__get'))
				$value = $object->$field;
			else if(property_exists($object, $field)
				$value = $object->$field;
			if($value === null)
				throw new KW_CRUDException('Object is missing an expected property "'.$field.'"');
			$query->$field = $object->$field;
		}

		/**
		 * Generate SQL statements to handle a table with an composite key.
		 * @param string $table The name of the table
		 * @param string[] $key The names of the primary key columns
		 * @param string[] $values The names of the data columns
		 * @param bool $serial Whether the table has an automatic id
		 */
		private function prepareComposite($table, $key, $values, $serial)
		{
			// Create
			// ToDo: serial is probably not going to work with composite keys - maybe we should just ignore it?
			$fields = array_merge($serial ? array() : (is_array($key) ? $key : array($key)), $values);
			$this->createRecord = $this->db->prepare('INSERT INTO ' . $table . ' (' . join(',', $fields) . ') VALUES (:' . join(', :',$fields) . ')');

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
			$this->readSet = $this->db->prepare('SELECT * FROM ' . $table . ' WHERE ' . $filter);

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
			$this->readAll = $this->db->prepare('SELECT * FROM ' . $table);
			$this->readOne = $this->db->prepare('SELECT * FROM ' . $table. ' WHERE ' . $filter);

			// Update
			$this->updateRecord = $this->db->prepare('UPDATE ' . $table . ' SET ' . join(', ', $fields) . ' WHERE ' . $filter);

			// Delete
			$this->deleteRecord = $this->db->prepare('DELETE FROM ' . $table . ' WHERE ' . $filter);
		}

		/**
		 * Generate SQL statements for handling a table with a simple key
		 * @param string $table The name of the table
		 * @param string[] $key The names of the primary key columns
		 * @param string[] $values The names of the data columns
		 * @param bool $serial Whether the table has an automatic id
		 */
		private function prepareIdentity($table, $key, $values, $serial)
		{
			// Create
			$fields = array_merge($serial ? array() : array($key), $values);
			$this->createRecord = $this->db->prepare('INSERT INTO ' . $table . ' ('.join(',', $fields) . ') VALUES (:' . join(', :',$fields) . ')');

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
					$this->readAll = $this->db->prepare('SELECT rowid, * FROM ' . $table);
					$this->readOne = $this->db->prepare('SELECT rowid, * FROM ' . $table . ' WHERE ' . $filter);
					break;

				default:
					$this->readAll = $this->db->prepare('SELECT * FROM ' . $table);
					$this->readOne = $this->db->prepare('SELECT * FROM ' . $table . ' WHERE ' . $filter);
					break;
			}

			// Update
			$this->updateRecord = $this->db->prepare('UPDATE ' . $table . ' SET ' . join(', ', $fields) . ' WHERE ' . $filter);

			// Delete
			$this->deleteRecord = $this->db->prepare('DELETE FROM ' . $table . ' WHERE ' . $filter);
		}

		/**
		 * Generate SQL statements for handling a table without a key
		 * @param string $table The name of the table
		 * @param string[] $values The names of the data columns
		 */
		private function prepareNonRelational($table, $values)
		{
			// Create
			$this->createRecord = $this->db->prepare('INSERT INTO ' . $table . ' (' . join(',', $values) . ') VALUES (:' . join(', :',$values) . ')');

			// Read
			$this->readAll = $this->db->prepare('SELECT * FROM ' . $table);

			// Update
			$fields = array();

			foreach ($values as $col)
				$fields[] = sprintf('%1$s = :%1$s', $col);

			$this->updateRecord = $this->db->prepare('UPDATE ' . $table . ' SET ' . join(', ', $fields));

			// Delete
			$this->deleteRecord = $this->db->prepare('DELETE FROM ' . $table);
		}
	}
?>
