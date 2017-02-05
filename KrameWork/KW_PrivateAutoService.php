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

		/**
		 * Hook filter to enable authorization and auditing
		 * @param string $endpoint The method that will be invoked
		 * @param string[] $args The arguments to be passed
		 */
		public function filter($endpoint, $args)
		{
			if(!$this->authorize($this->user, $endpoint, $args))
				throw new Exception('Not authorized');
			$this->audit($this->user, $endpoint, $args);
			return null;
		}

		/**
		 * Override this method to implement authorization
		 * @param IDataContainer $user The calling user
		 * @param string $endpoint The method being called
		 * @param string[] $args The arguments given
		 */
		public function authorize($user, $endpoint, $args)
		{
			return true;
		}

		/**
		 * Override this method to implement auditing
		 * @param IDataContainer $user The calling user
		 * @param string $endpoint The method being called
		 * @param string[] $args The arguments given
		 */
		public function audit($user, $endpoint, $args)
		{
		}

		/**
		 * Process a client request
		 * @param object $request The posted data
		 */
		public function process($request)
		{
			$this->user = $this->users->getCurrent();
			$state = $this->user ? $this->users->getState($this->user) : AUTH_NONE;
			if(!$this->user || ($state != AUTH_OK && $state != AUTH_OK_OLD))
				return (object)['success' => false, 'error' => 'Not authenticated', 'reauth' => true];

			return parent::process($request);
		}

		/**
		 * @var IUserSystem
		 */
		private $users;
		private $user;
	}
