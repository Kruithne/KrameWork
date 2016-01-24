<?php
	abstract class KW_AuthenticationService extends KW_JSONService
	{
		/**
		 * KW_AuthenticationService constructor.
		 * @param IUserSystem $users Database access layer for user data
		 * @param string $origin Sets the value of the Access-Control-Allow-Origin header
		 * @param bool $multiFactor Whether we are going to be requiring the user to use multifactor authentication
		 */
		public function __construct(IUserSystem $users, $origin, $multiFactor = false)
		{
			$this->users = $users;
			$this->multifactor = $multiFactor;
			parent::__construct($origin);
		}

		/**
		 * Process a client request
		 * @param object $request The posted data
		 */
		public function process($request)
		{
			$path = false;
			if (isset($_SERVER['PATH_INFO']))
				$path = $_SERVER['PATH_INFO'];

			if ($_SERVER['REQUEST_METHOD'] == 'POST')
			{
				switch($path)
				{
					case '/recover':
						global $user;
						if (!isset($request->token))
						{
							if (isset($request->email))
							{
								$user = $this->users->recover($request->email);
								if ($user)
								{
									$_SESSION['userid'] = $user->id;
									$_SESSION['state'] = AUTH_NONE;
								}
								return true;
							}
							return false;
						}

						if (!isset($_SESSION['reset_token']) || $request->token != $_SESSION['reset_token'])
							return false;

						if (isset($request->passphrase) && strlen($request->passphrase) > 6)
						{
							$this->users->setPassphrase($user->id, $request->passphrase);
							return true;
						}
						else
						{
							return ['username' => $user->username];
						}

					case '/login':
						if (isset($request->username) && isset($request->passphrase))
							return $this->authenticate($request);
						break;
				}
			}

			if ($_SERVER['REQUEST_METHOD'] == 'GET')
			{
				switch($path)
				{
					case '/logout':
						return $this->end_session();
					default:
						return $this->get_session();
				}
			}

			return false;
		}

		/**
		 * Process an authentication request
		 * @param object $request The posted data (username and passphrase)
		 * @return array Key value pairs "name" and "state"
		 */
		private function authenticate($request)
		{
			$result = $this->users->authenticate(
				$request->username,
				$request->passphrase
			);

			if ($result == AUTH_ERR_UNKNOWN)
				return ['name' => null, 'state' => AUTH_ERR_UNKNOWN];

			global $user;
			$user = $this->users->getUser($request->username);
			$_SESSION['userid'] = $user->id;
			$_SESSION['state'] = $result;

			if ($request->remember)
				$this->grant_token($user, $this->get_token($user));

			return $this->get_session();
		}

		/**
		 * Get the current user session
		 * @return array Key value pairs "name" and "state"
		 */
		private function get_session()
		{
			$auto = $this->use_token();

			global $user;
			if ($user)
				return ['name' => $user->name, 'state' => $_SESSION['state']];
			else if ($auto)
				return ['name' => $auto->name, 'state' => $_SESSION['state']];
			else
				return ['name' => null, 'state' => 0];
		}

		/**
		 * Try to log a user in automatically using a token
		 * @return array|false Key value pairs "name" and "state"
		 */
		private function use_token()
		{
			if (!isset($_COOKIE['auth_token']) || isset($_SESSION['token_used']))
				return false;

			$_SESSION['token_used'] = true;
			$token = explode(';', $_COOKIE['auth_token']);
			$user = $this->users->getUser(null, null, $token[0]);

			if (!$user)
				return false;

			switch($this->token_validate($user, $token))
			{
				case -1:
					return false;

				case 0:
					$this->grant_token($user, $this->get_token($user));
					$_SESSION['verified'] = false;
					$_SESSION['userid'] = $user->id;
					$_SESSION['state'] = $this->multifactor ? AUTH_MULTIFACTOR : AUTH_NONE;
					return $user;

				case 1:
					$_SESSION['verified'] = true;
					$_SESSION['userid'] = $user->id;
					$_SESSION['state'] = $this->users->getState($user);
					return $user;
			}

			return false;
		}

		/**
		 * Generate an authentication token for the user
		 * @param IDataContainer The user
		 * @return string An authentication token
		 */
		private function get_token($user)
		{
			return $user->id . ';' . $this->ip_lock($this->getAuthToken($user)) . ';' . $_SERVER['REMOTE_ADDR'];
		}

		/**
		 * Send the user a login token for future automatic login
		 * @param IDataContainer $user The authenticated user
		 * @param string The token string
		 */
		protected abstract function grant_token($user, $token);

		/**
		 * Validate a token for automatic login
		 * @param object $user The requested user
		 * @param string[] $token The authentication token used
		 * @return int -1 for invalid, 1 for valid and same IP, 0 for valid but new IP
		 */
		private function token_validate($user, $token)
		{
			// Token hash the user should have from the IP the cookie was given to
			$tok1 = $this->ip_lock($this->getAuthToken($user), $token[2]);

			// Token hash the user should have now
			$tok2 = $this->ip_lock($this->getAuthToken($user));

			// Token hash the user sent
			$tok3 = $token[1];

			if ($tok1 != $tok3)
				return -1; // Invalid token, user session key changed, or cookie was edited

			return $tok2 == $tok3 ? 1 : 0;
		}

		/**
		 * Get the users current authentication token, based on the current password hash and a asession salt.
		 * This ensures the token will be invalid if the password is changed or the user requests a permanent logout.
		 * @param IDataContainer $user The user object.
		 * @return string An authentication token
		 */
		public function getAuthToken($user)
		{
			return sha1($this->getSessionSalt($user).$user->passphrase);
		}

		/**
		 * Get the users current session salt, or create one if it is missing
		 * @param IDataContainer $user The user object
		 * @return string The current session salt
		 */
		public function getSessionSalt($user)
		{
			if($user->session_salt)
				return $user->session_salt;

			$user->session_salt = base64_encode(mcrypt_create_iv(12));
			$this->dal->setSessionSalt($user->id, $user->session_salt);
			return $user->session_salt;
		}

		/**
		 * Encode a token so it only works for a given IP address
		 * @param string $token The token
		 * @param string|null $ip When specified, encode an arbitrary IP rather than the client IP
		 */
		private function ip_lock($token, $ip = null)
		{
			if ($ip === null)
				$ip = $_SERVER['REMOTE_ADDR'];
			return sha1($token. $ip);
		}

		/**
		 * Log the user out
		 * @return array Key value pairs "name" and "state"
		 */
		private function end_session()
		{
			if (isset($_SESSION['userid']))
			{
				if (isset($_COOKIE['auth_token']))
					setcookie('auth_token', '', strtotime('-1 year'));

				session_destroy();
			}
			return ['name' => null, 'state' => 0];
		}

		private $users;
		private $multifactor;
	}
?>
