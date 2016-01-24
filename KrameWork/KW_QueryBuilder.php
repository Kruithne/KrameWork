<?php
	class KW_QueryBuilder
	{
		/**
		 * KW_QueryBuilder constructor.
		 * @param IDatabaseConnection $db The connection we want to run our query against
		 * @param string $column The name of the column we are searching
		 * @param KW_QueryBuilder $anchor The previous step in the chain
		 * @param ICRUD $crud The table we are querying
		 * @param int $level The nth column in the where statement
		 */
		public function __construct($db, $column, $anchor, $crud, $level = 1)
		{
			$this->db = $db;
			$this->column = $column;
			$this->anchor = $anchor;
			$this->crud = $crud;
			$this->level = $level;
		}

		/**
		 * Builds the SQL statement
		 * @param bool $glue Whether or not more columns will be added later
		 * @return string An SQL fragment
		 */
		public function build($glue = true)
		{
			return
				($this->anchor ? $this->anchor->build() . ' ' : 'SELECT * FROM ' . $this->crud->getName() . ' WHERE ')
				. sprintf($this->format, $this->column, $this->level)
				. ($glue ? ' ' . $this->glue : ''); 
		}

		/**
		 * Binds the parameters of the prepared statement to the values supplied by the user
		 */
		public function bind($statement)
		{
			if (is_array($this->value))
			{
				foreach ($this->value as $pf => $value)
				{
					$key = $this->column . $this->level . '_' . $pf;
					$statement->$key = $value;
				}
			}
			else
			{
				$key = $this->column . $this->level;
				$statement->$key = $this->value;
			}

			if ($this->anchor)
				$this->anchor->bind($statement);
		}

		public function andColumn($column)
		{
			$this->glue = 'AND';
			return new self($this->db, $column, $this, $this->crud, $this->level + 1);
		}

		public function orColumn($column)
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
			$sql = $this->build(false);
			if (!$this->statement)
				$this->statement = $this->db->prepare($sql);

			$this->bind($this->statement);

			$result = array();
			foreach ($this->statement->getRows() as $data)
				$result[] = $this->crud->getNewObject($data);

			return $result;
		}

		/**
		 * @var IDatabaseStatement
		 */
		private $statement;

		/**
		 * @var string AND/OR
		 */
		private $glue;

		/**
		 * @var string column name
		 */
		private $column;

		/**
		 * @var string SQL Fragment
		 */
		private $format;

		/**
		 * @var mixed Search value
		 */
		private $value;

		/**
		 * @var IDatabaseConnection
		 */
		private $db;

		/**
		 * @var int
		 */
		private $level;

		/**
		 * @var KW_QueryBuilder
		 */
		private $anchor;
	}
?>
