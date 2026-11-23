<?php
	namespace KrameWork\Database\Schema;

	require_once(__DIR__ . '/IManagedTable.php');
	require_once(__DIR__ . '/IMetaTable.php');
	require_once(__DIR__ . '/../Driver/Generic.php');
	require_once(__DIR__ . '/../QueryBuilder.php');

	use \KrameWork\Database\Driver\Generic;
	use KrameWork\Database\QueryBuilder;

	/**
	 * Class to handle a managed table hosted by a Microsoft SQL Server
	 * @author docpify <morten@runsafe.no>
	 * @package KrameWork\Database
	 */
	abstract class ManagedTable implements IManagedTable
	{
		public function __construct(Generic $db, IMetaTable $metaTable) {
			$this->db = $db;
			$this->meta = $metaTable;
		}

		/**
		 * @param string $column
		 * @return IQueryColumn
		 */
		public function where(string $column) {
			return new QueryBuilder($this->db, $column, null, $this);
		}

		/**
		 * @api update
		 * Updates the table in the database
		 * @return string Status
		 */
		public function update() {
			$current = $this->meta->getVersion($this->GetName());
			if($current < 1)
				return $this->create();

			$log = $this->versionLog();
			$latest = \count($log);
			if($current > $latest)
				return 'Table is ahead of code: '.$current;

			if($current == $latest)
				return 'Table is up to date.';

			$v = $current;
			try {
				/** @var TableVersion $version */
				foreach (\array_slice($log, $current) as $version) {
					$this->db->beginTransaction();
					$version->BeforeExecution($this->db);
					foreach ($version->sql as $sql)
						$this->db->execute($sql, []);
					$v++;
					$this->meta->setVersion($this->getName(), $v);
					$version->AfterExecution($this->db);
					$this->db->commitTransaction();
				}
			}
			catch (\Throwable $e)
			{
				$this->db->rollbackTransaction();
				$current = $this->meta->getVersion($this->GetName());
				return 'Table update failed with message "'.$e->getMessage().'". Current revision is '.$current;
			}
			return 'Table updated to revision '.$v;
		}

		/**
		 * @api drop
		 * Delete the table from the database
		 * @return bool Success
		 */
		public function drop() {
			$this->db->execute('DROP TABLE '.$this->GetFullName(), []);
		}

		/**
		 * @api create
		 * Create the table in the database
		 * @return bool Success
		 */
		public function create() {
			$v = \count($this->versionLog());
			foreach ($this->latestVersion() as $sql)
				$this->db->execute($sql, []);
			$this->meta->setVersion($this->getName(), $v);
			return "Table created with revision $v";
		}

		public function getFullName() {
			return $this->quoteIdentifier($this->getSchema()).'.'.$this->quoteIdentifier($this->getName());
		}

		/**
		 * Quotes the identifier for use in an SQL statement
		 *
		 * @param $identifier
		 * @return string The quoted identifier
		 */
		public function quoteIdentifier($identifier) {
			return "\"{$identifier}\"";
		}


		/**
		 * @var \KrameWork\Database\Driver\Generic
		 */
		protected $db;

		/**
		 * @var IMetaTable
		 */
		protected $meta;
	}
