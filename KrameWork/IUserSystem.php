<?php
	interface IUserSystem extends ICRUD
	{
		public function setLoginSuccess($id);
		public function setLoginFailed($id);
		public function lockout($id);
		public function setSessionSalt($id, $salt);
		public function setSecret($id, $secret);
		public function setPassphrase($id, $plaintext);
		public function getUser($username = null, $email = null, $id = null);
		public function getUsers();
		public function addUser($user);
		public function saveUser($user);
		public function authenticate($username, $passphrase);
		public function getState($user);
		public function encode($plaintext);
	}
?>
