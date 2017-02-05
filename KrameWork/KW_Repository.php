<?php
	abstract class KW_Repository extends KW_DataContainer implements IRepository
	{
		/**
		 * Set the database connection for this repository.
		 * @param IDatabaseConnection $db
		 */
		public function setDB($db)
		{
			$this->db = $db;
		}

		/**
		 * @var IDatabaseConnection
		 */
		protected $db;
	}
