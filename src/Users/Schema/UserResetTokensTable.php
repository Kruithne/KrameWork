<?php
	namespace KrameWork\Users\Schema;

	use KrameWork\Database\Schema\TableVersion;

	require_once(__DIR__ . '/UserManagedTable.php');
	require_once(__DIR__ . '/../../Database/Schema/TableVersion.php');

	/**
	 * Class UserResetTokensTable
	 * Managed table for password reset tokens.
	 *
	 * @package KrameWork\Users\Schema
	 */
	class UserResetTokensTable extends UserManagedTable
	{
		/**
		 * Returns the name of the table.
		 *
		 * @api getName
		 * @return string
		 */
		public function getName(): string {
			return 'user_reset_tokens';
		}

		/**
		 * Returns the revisions of the table.
		 *
		 * @api versionLog
		 * @return TableVersion[]
		 */
		public function versionLog(): array {
			return [
				new TableVersion(1, ['CREATE TABLE user_reset_tokens (user_id BIGINT NOT NULL PRIMARY KEY, reset_token VARCHAR(32) NOT NULL UNIQUE, reset_sent BIGINT NOT NULL)'])
			];
		}
	}
