<?php
	class KW_MultifactorService extends KW_AutoService
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
	 		parent::__construct($origin, ['replace','show','verify'], true, false);
		}

		public function replace($request)
		{
			$state = $this->state();
			if ($state != AUTH_OK && $state != AUTH_ERR_NOSECRET)
				return (object)['error' => 'Not logged in'];

			if ($user->secret)
			{
				$result = $this->users->authenticate(
					$user->username,
					$request->passphrase
				);

				if ($result != AUTH_OK)
					return (object)['error' => 'Wrong passphrase'];
			}
			$secret = $this->auth->createSecret();
			$_SESSION['new_secret'] = $secret;
			$token = $this->auth->getQRCodeGoogleUrl('runsafe-lab', $secret);
			return (object)['token'=>$token];
		}

		public function show($request)
		{
			$state = $this->state();
			if ($state != AUTH_OK)
				return (object)['error' => $state == AUTH_ERR_NOSECRET ? 'No code set' : 'Not logged in'];

			$result = $this->users->authenticate(
				$user->username,
				$request->passphrase
			);

			if ($result != AUTH_OK)
				return (object)['error' => 'Wrong passphrase'];

			$token = $this->auth->getQRCodeGoogleUrl('runsafe-lab', $user->secret);
			return (object)['token'=>$token];
		}

		public function verify($request)
		{
			$user = $this->users->getCurrent();
			if ($user->lastcode == $request->code)
				return (object)['error' => 'replay'];

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
			$_SESSION['state'] = $this->state();
			return (object)['ok'=>$result];
		}

		private function state()
		{
			$user = $this->users->getCurrent();
			if (!$user || !$user->active)
				return AUTH_NONE;
			return $this->users->getState($user);
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
