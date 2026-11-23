<?php
	namespace KrameWork\Database;
	use KrameWork\Database\Driver\Generic;
	use KrameWork\Database\Schema\IManagedTable;
	use KrameWork\Database\Schema\MSSQLManagedTable;
	use KrameWork\Database\Schema\MySQLManagedTable;
	use KrameWork\Database\Schema\PostgreSQLManagedTable;
	use KrameWork\Database\Schema\sqliteManagedTable;

	/**
	 * Class for building SQL queries - Subject to change
	 * @author docpify <morten@runsafe.no>
	 * @package KrameWork\Database
	 */
	class QueryBuilder
	{
		/**
		 * QueryBuilder constructor.
		 * @api __construct
		 * @param Generic $db The connection we want to run our query against
		 * @param string $column The name of the column we are searching
		 * @param QueryBuilder|null $anchor The previous step in the chain
		 * @param IManagedTable $table The table we are querying
		 * @param int $level The nth column in the where statement
		 * @throws \Exception For tables that belong to an unsupported database
		 */
		public function __construct(Generic $db, string $column, ?QueryBuilder $anchor, IManagedTable $table, int $level = 1)
		{
			$this->db = $db;
			$this->column = $column;
			$this->anchor = $anchor;
			$this->table = $table;
			$this->level = $level;
			$this->isMSSQL = $table instanceof MSSQLManagedTable;
			$this->isMySQL = $table instanceof MySQLManagedTable;
			$this->isPostgreSQL = $table instanceof PostgreSQLManagedTable;
			$this->isSqlite = $table instanceof sqliteManagedTable;

			if(!$this->isMSSQL && !$this->isMySQL && !$this->isPostgreSQL && !$this->isSqlite)
				throw new \Exception('Unsupported database type');
		}

		/**
		 * Builds the SQL statement
		 * @api build
		 * @param bool $glue Whether or not more columns will be added later
		 * @return string An SQL fragment
		 */
		public function build(bool $glue = true): string
		{
			$base = ($this->anchor ? $this->anchor->build() . ' ' : 'SELECT * FROM ' . $this->table->getFullName() . ' WHERE ')
				. \sprintf($this->format, $this->column, $this->level)
				. ($glue ? ' ' . $this->glue : '');

			if ($glue)
				return $base;

			if ($this->query_limit && $this->isMSSQL)
				$base = \str_replace('SELECT *', 'SELECT TOP ' . $this->query_limit . ' *', $base);

			if ($this->orderBy)
			{
				$cols = [];
				foreach ($this->orderBy as $col => $asc)
					$cols[] = $col.' '.($asc?'ASC':'DESC');
				$base .= ' ORDER BY '.\join(', ',$cols);
			}

			if ($this->query_limit && ($this->isSqlite || $this->isPostgreSQL || $this->isMySQL))
				$base .= \sprintf(' LIMIT %d', $this->query_limit);

			if ($this->query_offset && ($this->isSqlite || $this->isPostgreSQL || $this->isMySQL))
				$base .= \sprintf(' OFFSET %d', $this->query_offset);

			return $base;
		}

		/**
		 * Resolves the arguments to pass to the query executor.
		 *
		 * @api arguments
		 * @param array $params
		 * @return array Query parameters
		 */
		public function arguments(array $params = []): array
		{
			if ($this->value !== null)
			{
				if (\is_array($this->value))
				{
					foreach ($this->value as $pf => $value)
					{
						$key = $this->column . $this->level . '_' . $pf;
						$params[$key] = $value;
					}
				}
				else
				{
					$key = $this->column . $this->level;
					$params[$key] = $this->value;
				}
			}
			return $this->anchor ? $this->anchor->arguments($params) : $params;
		}

		/**
		 * @api andColumn
		 * @param string $column
		 * @return QueryBuilder
		 */
		public function andColumn(string $column): self
		{
			$this->glue = 'AND';
			return new self($this->db, $column, $this, $this->table, $this->level + 1);
		}

		public function orColumn(string $column): self
		{
			$this->glue = 'OR';
			return new self($this->db, $column, $this, $this->table, $this->level + 1);
		}

		public function like(mixed $value): static
		{
			$this->format = '%1$s LIKE :%1$s%2$s';
			$this->value = $value;
			return $this;
		}

		public function notLike(mixed $value): static
		{
			$this->format = '%1$s NOT LIKE :%1$s%2$s';
			$this->value = $value;
			return $this;
		}

		public function isNull(): static
		{
			$this->format = '%1$s IS NULL';
			$this->value = null;
			return $this;
		}

		public function maximum(): static
		{
			$this->format = '%1$s = (SELECT MAX(%1$s) FROM '.$this->table->getFullName().')';
			$this->value = null;
			return $this;
		}

		public function minimum(): static
		{
			$this->format = '%1$s = (SELECT MIN(%1$s) FROM '.$this->table->getFullName().')';
			$this->value = null;
			return $this;
		}

		public function notNull(): static
		{
			$this->format = '%1$s IS NOT NULL';
			$this->value = null;
			return $this;
		}

		public function lessThan(mixed $value): static
		{
			$this->format = '%1$s < :%1$s%2$s';
			$this->value = $value;
			return $this;
		}

		public function greaterThan(mixed $value): static
		{
			$this->format = '%1$s > :%1$s%2$s';
			$this->value = $value;
			return $this;
		}

		public function equals(mixed $value): static
		{
			$this->format = '%1$s = :%1$s%2$s';
			$this->value = $value;
			return $this;
		}

		public function equalsCaseInsensitive(string $value): static
		{
			$this->format = 'LOWER(%1$s) = :%1$s%2$s';
			$this->value = \strtolower($value);
			return $this;
		}

		public function between(mixed $low, mixed $high): static
		{
			$this->format = '(%1$s > :%1$s%2$s_low AND %1$s < :%1$s%2$s_high)';
			$this->value = array('low' => $low, 'high' => $high);
			return $this;
		}

		public function offset(int $offset): static
		{
			if($this->isMSSQL)
				throw new \Exception('Unsupported database type');

			$this->query_offset = $offset;
			return $this;
		}

		public function limit(int $count): static
		{
			$this->query_limit = $count;
			return $this;
		}

		public function descending(string $column): static
		{
			$this->orderBy[$column] = false;
			return $this;
		}

		public function ascending(string $column): static
		{
			$this->orderBy[$column] = true;
			return $this;
		}

		public function execute(): array
		{
			$sql = $this->build(false);
			return $this->db->getAll($sql, $this->arguments());
		}

		/**
		 * @var string|null AND/OR
		 */
		private ?string $glue = null;

		/**
		 * @var string column name
		 */
		private string $column;

		/**
		 * @var string|null SQL Fragment
		 */
		private ?string $format = null;

		/**
		 * @var mixed Search value
		 */
		private mixed $value = null;

		/**
		 * @var Generic
		 */
		private Generic $db;

		/**
		 * @var int
		 */
		private int $level;

		/**
		 * @var QueryBuilder|null
		 */
		private ?QueryBuilder $anchor = null;

		/**
		 * @var int|null
		 */
		private ?int $query_limit = null;

		/**
		 * @var int|null
		 */
		private ?int $query_offset = null;

		/**
		 * @var IManagedTable
		 */
		private IManagedTable $table;

		/**
		 * @var array
		 * Key is column name, value is bool. False = descending, True = ascending
		 */
		private array $orderBy = [];

		/**
		 * @var bool
		 */
		private bool $isMSSQL;

		/**
		 * @var bool
		 */
		private bool $isMySQL;

		/**
		 * @var bool
		 */
		private bool $isPostgreSQL;

		/**
		 * @var bool
		 */
		private bool $isSqlite;
	}
