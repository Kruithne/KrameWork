<?php
	interface IUserSystem extends ICRUD
	{
		/**
		 * Help a user recover access when a password or username is forgotten
		 * @param string $email The email entered by the user requesting assistance
		 * @return object|false If the email is unknown or recovery was not possible for some reason, false, otherwise the user objecct
		 */
		public function recover($email)
		/**
		 * Log user login success
		 * @param int $id The ID of the user whom successfully authenticated
		 */
		public function setLoginSuccess($id);
		/**
		 * Log user login failure
		 * @param int $id The ID of the user whom unsuccessfully authenticated
		 */
		public function setLoginFailed($id);
		/**
		 * Lock out a user due to repeated login failures
		 * @param int $id The ID of the user whom should be blocked from logging in
		 */
		public function lockout($id);
		/**
		 * Store a session salt value for a user
		 * @param int $id The ID of the user whom is getting a new session salt
		 * @param string $salt The session salt
		 */
		public function setSessionSalt($id, $salt);
		/**
		 * Set the authenticator secret for a user
		 * @param int $id The ID of the user whom is getting a new authenticator
		 * @param string $secret The shared secret
		 */
		public function setSecret($id, $secret);
		/**
		 * Set the users password
		 * @param int $id The ID of the user whom is getting a new password
		 * @param string $plaintext The new password plaintext
		 */
		public function setPassphrase($id, $plaintext);
		/**
		 * Load a user given username, email, or ID
		 * @param string $username The users login
		 * @param string $email The users email address
		 * @param int $id The users ID
		 * @return IDataContainer The user object
		 */
		public function getUser($username = null, $email = null, $id = null);
		/**
		 * Get all the users
		 */
		public function getUsers();
		/**
		 * Save a new user
		 * @param IDataContainer $user The user object
		 * @return IDataContainer[] The user object
		 */
		public function addUser($user);
		/**
		 * Save a modified user
		 * @param IDataContainer $user The user object
		 */
		public function saveUser($user);
		/**
		 * Check a username and password
		 * @param string $username The login
		 * @param string $passphrase The plaintext password
		 */
		public function authenticate($username, $passphrase);
		/**
		 * Get the current login state
		 * @param IDataContainer $user The user object
		 */
		public function getState($user);
		/**
		 * Hash a password
		 * @param string $plaintext A password
		 * @param string A hash
		 */
		public function encode($plaintext);
	}
?>
