<?php
	abstract class KW_CRUD extends KW_Repository
	{
		public abstract function getKey();
		public abstract function hasAutoKey();
		public abstract function getValues();

		public function __construct(KW_SchemaManager $schema)
		{
			$schema->addTable($this);
		}

		public function getNewObject($data)
		{
			return $data;
		}

		public function prepare()
		{
			$table = $this->getName();
			$key = $this->getKey();
			$values = $this->getValues();
			$serial = $this->hasAutoKey();
			if(is_array($key))
				$this->prepareComposite($table, $key, $values, $serial);
			else if($key)
				$this->prepareIdentity($table, $key, $values, $serial);
			else
				$this->prepareNonRelational($table, $values);
		}

		public function create($object)
		{
			$auto = $this->hasAutoKey();
			if($auto)
				$this->bindValues($this->createRecord, $this->getValues(), $object);
			else
				$this->bind($this->createRecord, $object);

			$this->createRecord->execute();
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
				if($result && count($result) == 1)
					switch($this->db->getType())
					{
						case 'pgsql':
							return $this->read($result[0]->currval);
					}
			}
		}

		public function read($key = null)
		{
			if($key === null)
			{
				$result = array();
				foreach($this->readAll->getRows() as $data)
					$result[] = $this->getNewObject($data);
				return $result;
			}
			if(is_array($key))
				foreach($key as $col => $val)
					$this->readOne->$col = $val;
			else if($key)
			{
				$kv = $this->getKey();
				$this->readOne->$kv = $key;
			}
			$result = $this->readOne->getRows();
			if($result)
			{
				if(count($result) == 1)
					return $this->getNewObject($result[0]);

				trigger_error('Multiple rows returned for specified key', E_USER_ERROR);
			}
			return null;
		}

		public function update($object)
		{
			$this->bind($this->updateRecord, $object);
			$this->updateRecord->execute();
		}

		public function delete($object)
		{
			$this->bind($this->deleteRecord, $object);
			$this->deleteRecord->execute();
		}

		private function bind($query, $object)
		{
			$this->bindValues($query, $this->getKey(), $object);
			$this->bindValues($query, $this->getValues(), $object);
		}

		private function bindValues($query, $field, $object)
		{
			if(is_array($field))
				foreach($field as $col)
					$query->$col = $object->$col;
			else if($field)
				$query->$field = $object->$field;
		}

		private function prepareComposite($table, $key, $values, $serial)
		{
			$filter = array();
			foreach($key as $col)
				$filter[] = sprintf('%1$s = :%1$s', $col);
			$filter = join(' AND ', $filter);
			$fields = array();
			foreach($values as $col)
				$fields[] = sprintf('%1$s = :%1$s', $col);

			// Create
			if($serial)
				$fields = array_values($values);
			else
				$fields = array_merge($keys, $values);
			$this->createRecord = $this->db->prepare('INSERT INTO '.$table.' ('.join(',', $fields).') VALUES (:'.join(', :',$fields).')');

			switch($this->db->getType())
			{
				case 'pgsql':
					$this->getLastID = $this->db->prepare('SELECT currval(pg_get_serial_sequence(:table, :key))');
					break;
				default:
					$this->getLastID = $this->db->prepare('SELECT LAST_INSERT_ID()');
			}

			// Read
			$this->readAll = $this->db->prepare('SELECT * FROM '.$table);
			$this->readOne = $this->db->prepare('SELECT * FROM '.$table.' WHERE '.$filter);

			// Update
			$this->updateRecord = $this->db->prepare('UPDATE '.$table.' SET '.join(', ', $fields).' WHERE '.$filter);

			// Delete
			$this->deleteRecord = $this->db->prepare('DELETE FROM '.$table.' WHERE '.$filter);
		}

		private function prepareIdentity($table, $key, $values, $serial)
		{
			$filter = sprintf('%1$s = :%1$s', $key);
			$fields = array();
			foreach($values as $col)
				$fields[] = sprintf('%1$s = :%1$s', $col);

			// Create
			$fields = array_merge($serial ? array() : array($key), $values);
			$this->createRecord = $this->db->prepare('INSERT INTO '.$table.' ('.join(',', $fields).') VALUES (:'.join(', :',$fields).')');

			switch($this->db->getType())
			{
				case 'pgsql':
					$this->getLastID = $this->db->prepare('SELECT currval(pg_get_serial_sequence(:table, :key))');
					break;
				default:
					$this->getLastID = $this->db->prepare('SELECT LAST_INSERT_ID()');
			}

			// Read
			$this->readAll = $this->db->prepare('SELECT * FROM '.$table);
			$this->readOne = $this->db->prepare('SELECT * FROM '.$table.' WHERE '.$filter);

			// Update
			$this->updateRecord = $this->db->prepare('UPDATE '.$table.' SET '.join(', ', $fields).' WHERE '.$filter);

			// Delete
			$this->deleteRecord = $this->db->prepare('DELETE FROM '.$table.' WHERE '.$filter);
		}

		private function prepareNonRelational($table, $values)
		{
			$this->createRecord = $this->db->prepare('INSERT INTO '.$table.' ('.join(',', $values).') VALUES (:'.join(', :',$values).')');

			// Read
			$this->readAll = $this->db->prepare('SELECT * FROM '.$table);

			// Update
			$this->updateRecord = $this->db->prepare('UPDATE '.$table.' SET '.join(', ', $fields));

			// Delete
			$this->deleteRecord = $this->db->prepare('DELETE FROM '.$table);
		}
	}
?>
