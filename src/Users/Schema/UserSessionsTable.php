<?php
	namespace KrameWork\Users\Schema;

	use KrameWork\Database\Schema\TableVersion;

	require_once(__DIR__ . '/UserManagedTable.php');
	require_once(__DIR__ . '/../../Database/Schema/TableVersion.php');

	/**
	 * Class UserSessionsTable
	 * Managed table for user sessions.
	 *
	 * @package KrameWork\Users\Schema
	 */
	class UserSessionsTable extends UserManagedTable
	{
		/**
		 * Returns the name of the table.
		 *
		 * @api getName
		 * @return string
		 */
		public function getName(): string {
			return 'user_sessions';
		}

		/**
		 * Returns the revisions of the table.
		 *
		 * @api versionLog
		 * @return TableVersion[]
		 */
		public function versionLog(): array {
			return [
				new TableVersion(1, ['CREATE TABLE user_sessions (session_id VARCHAR(64) NOT NULL PRIMARY KEY, user_id BIGINT NOT NULL, created BIGINT NOT NULL, updated BIGINT NOT NULL)']),
				new TableVersion(2, ['CREATE INDEX user_sessions_user_id ON user_sessions (user_id)'])
			];
		}
	}
