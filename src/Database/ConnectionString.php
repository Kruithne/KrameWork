<?php
	namespace KrameWork\Database;

	/**
	 * Base class for a database connection string
	 * @author docpify <morten@runsafe.no>
	 * @package KrameWork\Database
	 */
	class ConnectionString
	{
		/**
		 * ConnectionString constructor.
		 * @api __construct
		 * @param $connection string A connection string
		 * @param string $username A username to log on
		 * @param string $password A password to log on
		 */
		public function __construct(string $connection, ?string $username = null, ?string $password = null) {
			$this->connection = $connection;
			$this->username = $username;
			$this->password = $password;
		}

		/**
		 * Returns the connection string used to connect to the database
		 * @api __toString
		 * @return string The connection string
		 */
		function __toString() {
			return $this->connection;
		}

		/**
		 * Return the password used to connect to the database
		 * @api getUsername
		 * @return string|null
		 */
		public function getUsername() {
			return $this->username;
		}

		/**
		 * Return the password used to connect to the database
		 * @api getPassword
		 * @return string|null
		 */
		public function getPassword() {
			return $this->password;
		}

		/**
		 * @var string The connection string
		 */
		protected $connection;
		protected $username;
		protected $password;
	}