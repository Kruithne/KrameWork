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
			$log = $this->versionLog();
			$error = $this->validateLog($log);
			if ($error !== null)
				return $error;

			$current = $this->metaTable->getVersion($this->getName());
			$latest = \count($log);
			if ($current > $latest)
				return 'Table is ahead of code: ' . $current;

			if ($current == $latest)
				return 'Table is up to date.';

			return $this->applyRevisions($log, $current);
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
		 * Create the table in the database by applying all revisions
		 * @return string Status
		 */
		public function create(): string {
			$log = $this->versionLog();
			$error = $this->validateLog($log);
			if ($error !== null)
				return $error;

			return $this->applyRevisions($log, 0);
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
		 * Applies the revisions after the current revision, each in its own transaction
		 * @param TableVersion[] $log Revision log
		 * @param int $current Current revision of the table, 0 if the table does not exist
		 * @return string Status
		 */
		private function applyRevisions(array $log, int $current): string {
			$type = $current === 0 ? 'creation' : 'update';
			$v = $current;
			try {
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
				$revision = $this->metaTable->getVersion($this->getName());
				return 'Table ' . $type . ' failed with message "' . $e->getMessage() . '". Current revision is ' . $revision;
			}

			if ($current === 0)
				return 'Table created with revision ' . $v;

			return 'Table updated to revision ' . $v;
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
