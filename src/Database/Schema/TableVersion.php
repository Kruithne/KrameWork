<?php
	namespace KrameWork\Database\Schema;

	use \KrameWork\Database\Driver\Generic;

	class TableVersion
	{
		/**
		 * TableVersion constructor.
		 * @api __construct
		 * @param int $version Revision number of the table
		 * @param string[] $sql SQL statements to apply for this revision
		 */
		public function __construct(public int $version, public array $sql) {}

		/**
		 * @api BeforeExecution
		 * Override this function to execute custom code before SQL application.
		 */
		public function BeforeExecution(Generic $db): void {
		}

		/**
		 * @api BeforeExecution
		 * Override this function to execute custom code after SQL application.
		 */
		public function AfterExecution(Generic $db): void {
		}
	}
