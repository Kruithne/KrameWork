<?php
	class KW_MultifactorService extends KW_JSONService
	{
		/**
		 * KW_MultifactorService constructor.
		 * @param IUserSystem $users
		 * @param IAuthenticator $auth
		 * @param string $origin
		 */
		public function __construct(IUserSystem $users, IAuthenticator $auth, $origin)
		{
			$this->users = $users;
			$this->auth = $auth;
	 		parent::__construct($origin);
		}

		public function process($request)
		{
			global $user;
			$path = false;

			if (isset($_SERVER['PATH_INFO']))
				$path = $_SERVER['PATH_INFO'];

			$user = $this->users->getCurrent();
			if (!$user || !$user->active)
				return;
			$state = $this->users->getState($user);

			if ($_SERVER['REQUEST_METHOD'] == 'POST')
			{
				switch($path)
				{
					case '/replace':
				 		if ($state != AUTH_OK && $state != AUTH_ERR_NOSECRET)
							return (object)['reason' => 'Not logged in'];

						if ($user->secret)
						{
							$result = $this->users->authenticate(
								$user->username,
								$request->passphrase
							);

							if ($result != AUTH_OK)
								return (object)['reason' => 'Wrong passphrase'];
						}
						$secret = $this->auth->createSecret();
						$_SESSION['new_secret'] = $secret;
						$token = $this->auth->getQRCodeGoogleUrl('runsafe-lab', $secret);
						return ['token' => $token];

					case '/clone':
				 		if ($state != AUTH_OK)
							return (object)['reason' => $state == AUTH_ERR_NOSECRET ? 'No code set' : 'Not logged in'];

						$result = $this->users->authenticate(
							$user->username,
							$request->passphrase
						);

						if ($result != AUTH_OK)
							return (object)['reason' => 'Wrong passphrase'];

						$token = $this->auth->getQRCodeGoogleUrl('runsafe-lab', $user->secret);
						return ['token' => $token];

					case '/verify':
						if ($user->lastcode == $request->code)
							return ['result' => false, 'reason' => 'replay'];

						if (isset($_SESSION['new_secret']) && $user->secret == null)
						{
							$result = $this->auth->verifyCode($_SESSION['new_secret'], $request->code, 2);
							if ($result)
							{
								$this->users->setSecret($user->id, $_SESSION['new_secret']);
								$user->secret = $_SESSION['new_secret'];
								unset($_SESSION['new_secret']);
							}
						}
						else
						{
							$result = $this->auth->verifyCode($user->secret, $request->code, 2);
						}

						$_SESSION['verified'] = $result;
						return ['result' => $result];
				}
			}
		}

		/**
		 * @var IUserSystem
		 */
		private $users;

		/**
		 * @var IAuthenticator
		 */
		private $auth;
	}
?>
