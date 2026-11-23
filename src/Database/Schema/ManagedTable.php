<?php
	namespace KrameWork\Database\Schema;

	require_once(__DIR__ . '/IManagedTable.php');
	require_once(__DIR__ . '/IMetaTable.php');
	require_once(__DIR__ . '/../Driver/Generic.php');

	use \KrameWork\Database\Driver\Generic;

	/**
	 * Class to handle a managed table hosted by a Microsoft SQL Server
	 * @author docpify <morten@runsafe.no>
	 * @package KrameWork\Database
	 */
	abstract class ManagedTable implements IManagedTable
	{
		public function __construct(protected Generic $db, protected IMetaTable $metaTable) {}

		/**
		 * @api update
		 * Updates the table in the database
		 * @return string Status
		 */
		public function update(): string {
			$current = $this->metaTable->getVersion($this->GetName());
			if($current < 1)
				return $this->create();

			$log = $this->versionLog();
			$error = $this->validateLog($log);
			if ($error !== null)
				return $error;

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
					$this->metaTable->setVersion($this->getName(), $v);
					$version->AfterExecution($this->db);
					$this->db->commitTransaction();
				}
			}
			catch (\Throwable $e)
			{
				$this->db->rollbackTransaction();
				$current = $this->metaTable->getVersion($this->GetName());
				return 'Table update failed with message "'.$e->getMessage().'". Current revision is '.$current;
			}
			return 'Table updated to revision '.$v;
		}

		/**
		 * @api drop
		 * Delete the table from the database
		 */
		public function drop(): void {
			$this->db->execute('DROP TABLE '.$this->GetFullName(), []);
		}

		/**
		 * @api create
		 * Create the table in the database
		 * @return string Status
		 */
		public function create(): string {
			$log = $this->versionLog();
			$error = $this->validateLog($log);
			if ($error !== null)
				return $error;

			$v = \count($log);
			try {
				$this->db->beginTransaction();
				foreach ($this->latestVersion() as $sql)
					$this->db->execute($sql, []);
				$this->metaTable->setVersion($this->getName(), $v);
				$this->db->commitTransaction();
			}
			catch (\Throwable $e)
			{
				$this->db->rollbackTransaction();
				return 'Table creation failed with message "' . $e->getMessage() . '"';
			}
			return "Table created with revision $v";
		}

		public function getFullName(): string {
			return $this->quoteIdentifier($this->getSchema()).'.'.$this->quoteIdentifier($this->getName());
		}

		/**
		 * Quotes the identifier for use in an SQL statement
		 *
		 * @param string $identifier
		 * @return string The quoted identifier
		 */
		public function quoteIdentifier(string $identifier): string {
			return "\"{$identifier}\"";
		}

		/**
		 * Checks that each revision version matches its position in the log, starting at 1
		 * @param TableVersion[] $log Revision log
		 * @return string|null Error status, or null if the log is valid
		 */
		private function validateLog(array $log): ?string {
			foreach (\array_values($log) as $i => $version) {
				if ($version->version !== $i + 1)
					return 'Revision log is invalid at position ' . ($i + 1) . ' (version ' . $version->version . ')';
			}
			return null;
		}
	}
