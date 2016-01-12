<?php
	class KW_AuthenticationService extends KW_JSONService
	{
		public function __construct(KW_UserSystem $users, $origin)
		{
			$this->users = $users;
			parent::__construct($origin);
		}

		public function process($request)
		{
			$path = false;
			if(isset($_SERVER['PATH_INFO']))
				$path = $_SERVER['PATH_INFO'];
			if($_SERVER['REQUEST_METHOD'] == 'POST')
			{
				switch($path)
				{
					case '/recover':
						global $user;
						if(!isset($request->token))
						{
							if(isset($request->email))
							{
								$user = $this->users->recover($request->email);
								if($user)
								{
									$_SESSION['userid'] = $user->id;
									$_SESSION['state'] = AUTH_NONE;
								}
								return true;
							}
							return false;
						}
						if(!isset($_SESSION['reset_token']) || $request->token != $_SESSION['reset_token'])
							return false;

						if(isset($request->passphrase) && strlen($request->passphrase) > 6)
						{
							$this->users->setPassphrase($user->id, $request->passphrase);
							return true;
						}
						else
						{
							return [ 'username' => $user->username ];
						}
						return true;

					case '/login':
						if(isset($request->username) && isset($request->passphrase))
							return $this->authenticate($request);
						break;
				}
			}
			if($_SERVER['REQUEST_METHOD'] == 'GET')
			{
				switch($path)
				{
					case '/logout':
						return $this->end_session();
					default:
						return $this->get_session();
				}
			}
		}

		private function authenticate($request)
		{
			$result = $this->users->authenticate(
				$request->username,
				$request->passphrase
			);
			if($result == AUTH_ERR_UNKNOWN)
				return ['name' => null, 'state' => AUTH_ERR_UNKNOWN];
			global $user;
			$user = $this->users->getUser($request->username);
			$_SESSION['userid'] = $user->id;
			$_SESSION['state'] = $result;
			if($request->remember)
				$this->grant_token($user);
			return $this->get_session();
		}

		private function get_session()
		{
			$auto = $this->use_token();
			global $user;
			if($user)
				return [
					'name' => $user->name,
					'state' => $_SESSION['state']
				];

			else if($auto)
				return [
					'name' => $auto->name,
					'state' => $_SESSION['state']
				];

			else
				return ['name' => null, 'state' => 0];
		}

		private function use_token()
		{
			if(!isset($_COOKIE['auth_token']) || isset($_SESSION['token_used']))
				return false;
			$_SESSION['token_used'] = true;
			$token = explode(';', $_COOKIE['auth_token']);
			$user = $this->users->getUser(null, null, $token[0]);
			if(!$user)
				return false;
			switch($this->token_validate($user, $token))
			{
				case -1:
					return false;
				case 0:
					$this->grant_token($user);
					$_SESSION['verified'] = false;
					$_SESSION['userid'] = $user->id;
					$_SESSION['state'] = AUTH_MULTIFACTOR;
					return $user;
				case 1:
					$_SESSION['verified'] = true;
					$_SESSION['userid'] = $user->id;
					$_SESSION['state'] = $this->users->getState($user);
					return $user;
			}
		}

		private function grant_token($user)
		{
			setcookie(
				'auth_token',
				$user->id.';'.$this->ip_lock($user->getAuthToken()).';'.$_SERVER['REMOTE_ADDR'],
				strtotime('+1 year'),
				'/auth.php',
				'lab-api.runsafe.no',
				true,
				true
			);
		}

		private function token_validate($user, $token)
		{
			// Token hash the user should have from the IP the cookie was given to
			$tok1 = $this->ip_lock($user->getAuthToken(), $token[2]);

			// Token hash the user should have now
			$tok2 = $this->ip_lock($user->getAuthToken());

			// Token hash the user sent
			$tok3 = $token[1];

			if($tok1 != $tok3)
				return -1; // Invalid token, user session key changed, or cookie was edited

			return $tok2 == $tok3 ? 1 : 0;
		}

		private function ip_lock($token, $ip = null)
		{
			if($ip === null)
				$ip = $_SERVER['REMOTE_ADDR'];
			return sha1($token.$ip);
		}

		private function end_session()
		{
			if(isset($_SESSION['userid']))
			{
				if(isset($_COOKIE['auth_token']))
					setcookie('auth_token', '', strtotime('-1 year'), '/auth.php', 'lab-api.runsafe.no', true, true);
				session_destroy();
			}
			return ['name' => null, 'state' => 0];
		}

		private $users;
	}
?>
