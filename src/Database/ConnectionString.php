<?php
	namespace KrameWork\Database;

	/**
	 * Base class for a database connection string
	 * @package KrameWork\Database
	 */
	class ConnectionString
	{
		/**
		 * ConnectionString constructor.
		 * @api __construct
		 * @param string $connection A connection string
		 * @param string|null $username A username to log on
		 * @param string|null $password A password to log on
		 */
		public function __construct(protected string $connection, protected ?string $username = null, protected ?string $password = null) {}

		/**
		 * Returns the connection string used to connect to the database
		 * @api __toString
		 * @return string The connection string
		 */
		function __toString(): string {
			return $this->connection;
		}

		/**
		 * Return the password used to connect to the database
		 * @api getUsername
		 * @return string|null
		 */
		public function getUsername(): ?string {
			return $this->username;
		}

		/**
		 * Return the password used to connect to the database
		 * @api getPassword
		 * @return string|null
		 */
		public function getPassword(): ?string {
			return $this->password;
		}
	}