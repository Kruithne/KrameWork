<?php
	namespace KrameWork\Database\Schema;

	use \KrameWork\Database\Driver\Generic;

	class TableVersion
	{
		public function __construct(int $version, array $sql) {
			$this->version = $version;
			$this->sql = $sql;
		}

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

		/**
		 * @var int
		 */
		public int $version;

		/**
		 * @var string[]
		 */
		public array $sql;
	}
