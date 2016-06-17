<?php
	/**
	 * Generic service base class
	 */
	abstract class KW_AutoService
	{
		/**
		 * KW_AutoService constructor.
		 * @param string $origin Sets the value of the Access-Control-Allow-Origin header
		 * @param string[] $endpoints An array of allowable API endpoints (svc.php/endpoint)
		 * @param bool $post Allow posts to this service
		 * @param bool $get Allow gets from this service
		 */
		public function __construct($origin, $endpoints, $post = true, $get = true)
		{
			$this->endpoints = $endpoints;
			$this->origin = $origin;
			$http = [];
			if ($get) $http[] = 'GET';
			if ($post) $http[] = 'POST';
			$this->method = join(', ', $http);
		}

		public function execute()
		{
			header('Access-Control-Allow-Origin: ' . $this->origin);
			header('Access-Control-Allow-Methods: ' . $this->method);
			header('Access-Control-Allow-Headers: Content-Type, Cookie');
			header('Access-Control-Allow-Credentials: true');
			header('Cache-Control: no-cache');
			header('Pragma: no-cache');

			if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS')
				die();

			if($_SERVER['REQUEST_METHOD'] == 'POST')
				$request = json_decode(file_get_contents('php://input'));
			else
				$request = null;

			$response = $this->process($request);
			header('Content-Type: application/json;charset=UTF-8');
			echo json_encode($response);
			die();
		}

		/**
		 * Process a client request
		 * @param object $request The posted data
		 */
		public function process($request)
		{
			if (!isset($_SERVER['PATH_INFO']))
				return (object)['success' => false, 'error' => 'Unsupported request'];

			$args = explode('/', $_SERVER['PATH_INFO']);
			if (count($args) < 2 || !in_array($args[1], $this->endpoints))
				return (object)['success' => false, 'error' => 'Unknown method'];

			$method = $args[1];
			$varargs = count($args) > 2 ? array_slice($args, 2) : [];
			if ($request !== null)
				$varargs[] = $request;

			try
			{
				$return = call_user_func_array(array($this, $method), $varargs);
			}
			catch(Exception $e)
			{
				return (object)['success' => false, 'exception' => $e->getMessage()];
			}
			return (object)['success' => $return !== false, 'result' => $return === false ? null : $return];
		}

		/**
		 * @var string
		 */
		private $origin;

		/**
		 * @var string
		 */
		private $method;

		/**
		 * @var string[]
		 */
		private $endpoints;
	}
?>
