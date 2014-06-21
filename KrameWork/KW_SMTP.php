<?php
	class KW_SMTP
	{
		/**
		 * Create an object for talking to an SMTP server.
		 * @param string $host
		 * @param int $port
		 */
		public function __construct($host, $port)
		{
			$this->connection = fsockopen($host, $port);
		}

		/**
		 * Send a message to the server.
		 * @param string $msg Message to send.
		 */
		public function talk($msg)
		{
			fwrite($this->connection, $msg . "\r\n");
		}

		/**
		 * Listen for a response.
		 * @return int|null Response code.
		 */
		public function listen()
		{
			$return = substr(fgets($this->connection, 256), 3, 1);
			return $return == ' ' ? NULL : (int) $return;
		}

		/**
		 * Check to see if we're connected or not.
		 * @return bool
		 */
		public function isConnected()
		{
			return $this->connection !== FALSE;
		}

		private $connection;
	}
?>