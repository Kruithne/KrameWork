<?php
	class KW_QueryBuilder
	{
		public function __construct($db, $column, $anchor, $crud, $level = 1)
		{
			$this->db = $db;
			$this->column = $column;
			$this->anchor = $anchor;
			$this->crud = $crud;
			$this->level = $level;
		}

		public function build()
		{
			return
				($this->anchor ? $this->anchor->build().' ' : 'SELECT * FROM '.$this->crud->getName().' WHERE ')
				. sprintf($this->format, $this->column)
				. ' ' . $this->glue;
		}

		public function bind($statement)
		{
			if(is_array($this->value))
				foreach($this->value as $pf => $value)
				{
					$key = $this->column.$this->level.'_'.$pf;
					$statemet->$key = $value;
				}
			else
			{
				$key = $this->column.$this->level;
				$statement->$key = $this->value;
			}
			if($this->anchor)
				$this->anchor->bind($statement);
		}

		public function and($column)
		{
			$this->glue = 'AND';
			return new self($this->db, $column, $this, $this->crud, $this->level + 1);
		}

		public function or($column)
		{
			$this->glue = 'OR';
			return new self($this->db, $column, $this, $this->crud, $this->level + 1);
		}

		public function like($value)
		{
			$this->format = '%1$s LIKE :%1$s%2$s';
			$this->value = $value;
			return $this;
		}

		public function notLike($value)
		{
			$this->format = '%1$s NOT LIKE :%1$s%2$s';
			$this->value = $value;
			return $this;
		}

		public function isNull()
		{
			$this->format = '%1$s IS NULL';
			$this->value = null;
			return $this;
		}

		public function notNull()
		{
			$this->format = '%1$s IS NOT NULL';
			$this->value = null;
			return $this;
		}

		public function lessThan($value)
		{
			$this->format = '%1$s < :%1$s%2$s';
			$this->value = $value;
			return $this;
		}

		public function greaterThan($value)
		{
			$this->format = '%1$s > :%1$s%2$s';
			$this->value = $value;
			return $this;
		}

		public function equals($value)
		{
			$this->format = '%1$s = :%1$s%2$s';
			$this->value = $value;
			return $this;
		}

		public function between($low, $high)
		{
			$this->format = '(%1$s > :%1%s%2$s_low AND %1$s < :%1$s%2$s_high)';
			$this->value = array('low' => $low, 'high' => $high);
			return $this;
		}

		public function execute()
		{
			$sql = $this->anchor->build().' '.sprintf($this->format, $this->column);
			if(!$this->statement)
				$this->statement = $this->db->prepare($sql);
			$this->bind($statement);

			$result = array();
			foreach ($this->statement->getRows() as $data)
				$result[] = $this->crud->getNewObject($data);
			if(count($result) == 0)
				return null;
			if(count($result) == 1)
				return $result[0];
			return $result;
		}

		private $statement;
		private $glue;
		private $column;
		private $format;
		private $value;
		private $db;
		private $level;
		private $anchor}
