<?php
	namespace KrameWork\Users\Schema;

	use KrameWork\Database\Schema\TableVersion;

	require_once(__DIR__ . '/UserManagedTable.php');
	require_once(__DIR__ . '/../../Database/Schema/TableVersion.php');

	/**
	 * Class OAuthStatesTable
	 * Managed table for the state tokens of OAuth logins.
	 *
	 * @package KrameWork\Users\Schema
	 */
	class OAuthStatesTable extends UserManagedTable
	{
		/**
		 * Returns the name of the table.
		 *
		 * @api getName
		 * @return string
		 */
		public function getName(): string {
			return 'oauth_states';
		}

		/**
		 * Returns the revisions of the table.
		 *
		 * @api versionLog
		 * @return TableVersion[]
		 */
		public function versionLog(): array {
			return [
				new TableVersion(1, ['CREATE TABLE oauth_states (state VARCHAR(64) NOT NULL PRIMARY KEY, provider VARCHAR(50) NOT NULL, redirect_uri VARCHAR(500) NOT NULL, created BIGINT NOT NULL)']),
				new TableVersion(2, ['CREATE INDEX oauth_states_created ON oauth_states (created)']),
				new TableVersion(3, ['ALTER TABLE oauth_states ADD code_verifier VARCHAR(64) NULL'])
			];
		}
	}
