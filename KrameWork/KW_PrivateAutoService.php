<?php
	/**
	 * Generic private service base class
	 */
	abstract class KW_PrivateAutoService extends KW_AutoService
	{
		/**
		 * KW_PrivateAutoService constructor.
		 * @param IUserSystem $users The user system to get authentication from
		 * @param string $origin Sets the value of the Access-Control-Allow-Origin header
		 * @param string[] $endpoints An array of allowable API endpoints (svc.php/endpoint)
		 * @param bool $post Allow posts to this service
		 * @param bool $get Allow gets from this service
		 */
		public function __construct($users, $origin, $endpoints, $post = true, $get = true)
		{
			$this->users = $users;
			parent::__construct($origin, $endpoints, $post, $get);
		}

		public function process($request)
		{
			$user = $this->users->getCurrent();
			$state = $user ? $this->users->getState($user) : AUTH_NONE;
			if(!$user || ($state != AUTH_OK && $state != AUTH_OK_OLD))
				return (object)['success' => false, 'error' => 'Not authenticated', 'reauth' => true];

			return parent::process($request);
		}

		/**
		 * @var IUserSystem
		 */
		private $users;
	}
?>
