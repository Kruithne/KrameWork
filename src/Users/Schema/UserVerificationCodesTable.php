<?php
	namespace KrameWork\Users\Schema;

	use KrameWork\Database\Schema\TableVersion;

	require_once(__DIR__ . '/UserManagedTable.php');
	require_once(__DIR__ . '/../../Database/Schema/TableVersion.php');

	/**
	 * Class UserVerificationCodesTable
	 * Managed table for open account verifications.
	 *
	 * @package KrameWork\Users\Schema
	 */
	class UserVerificationCodesTable extends UserManagedTable
	{
		/**
		 * Returns the name of the table.
		 *
		 * @api getName
		 * @return string
		 */
		public function getName(): string {
			return 'user_verification_codes';
		}

		/**
		 * Returns the revisions of the table.
		 *
		 * @api versionLog
		 * @return TableVersion[]
		 */
		public function versionLog(): array {
			return [
				new TableVersion(1, ['CREATE TABLE user_verification_codes (token VARCHAR(32) NOT NULL PRIMARY KEY, user_id BIGINT NOT NULL UNIQUE, code VARCHAR(5) NOT NULL, attempts INTEGER NOT NULL, last_sent BIGINT NOT NULL)']),
				new TableVersion(2, ['DROP TABLE user_verification_codes', 'CREATE TABLE user_verification_codes (token VARCHAR(64) NOT NULL PRIMARY KEY, user_id BIGINT NOT NULL UNIQUE, code VARCHAR(64) NOT NULL, attempts INTEGER NOT NULL, last_sent BIGINT NOT NULL)'])
			];
		}
	}
