<?php
	namespace KrameWork\Users\Schema;

	use KrameWork\Database\Schema\TableVersion;

	require_once(__DIR__ . '/UserManagedTable.php');
	require_once(__DIR__ . '/../../Database/Schema/TableVersion.php');

	/**
	 * Class UserPermissionsTable
	 * Managed table for the permissions of user accounts.
	 *
	 * @package KrameWork\Users\Schema
	 */
	class UserPermissionsTable extends UserManagedTable
	{
		/**
		 * Returns the name of the table.
		 *
		 * @api getName
		 * @return string
		 */
		public function getName(): string {
			return 'user_permissions';
		}

		/**
		 * Returns the revisions of the table.
		 *
		 * @api versionLog
		 * @return TableVersion[]
		 */
		public function versionLog(): array {
			return [
				new TableVersion(1, ['CREATE TABLE user_permissions (user_id BIGINT NOT NULL, permission INTEGER NOT NULL, PRIMARY KEY (user_id, permission))'])
			];
		}
	}
