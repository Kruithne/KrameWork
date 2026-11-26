<?php
	namespace KrameWork\Users\Schema;

	use KrameWork\Database\Schema\TableVersion;

	require_once(__DIR__ . '/UserManagedTable.php');
	require_once(__DIR__ . '/../../Database/Schema/TableVersion.php');

	/**
	 * Class OAuthAccountsTable
	 * Managed table for links between user accounts and OAuth identities.
	 *
	 * @package KrameWork\Users\Schema
	 */
	class OAuthAccountsTable extends UserManagedTable
	{
		/**
		 * Returns the name of the table.
		 *
		 * @api getName
		 * @return string
		 */
		public function getName(): string {
			return 'oauth_accounts';
		}

		/**
		 * Returns the revisions of the table.
		 *
		 * @api versionLog
		 * @return TableVersion[]
		 */
		public function versionLog(): array {
			return [
				new TableVersion(1, ['CREATE TABLE oauth_accounts (provider VARCHAR(50) NOT NULL, provider_user_id VARCHAR(255) NOT NULL, user_id BIGINT NOT NULL, linked BIGINT NOT NULL, PRIMARY KEY (provider, provider_user_id))']),
				new TableVersion(2, ['CREATE INDEX oauth_accounts_user_id ON oauth_accounts (user_id)'])
			];
		}
	}
