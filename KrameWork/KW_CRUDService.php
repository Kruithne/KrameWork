<?php
	abstract class KW_CRUDService extends KW_CRUD implements ICRUDService
	{
		public function getOrigin()
		{
			return '*';
		}

		public function getMethod()
		{
			return 'GET, POST';
		}

		/**
		 * KW_CRUDService constructor.
		 * @param ISchemaManager $schema
		 * @param IErrorHandler|null $error
		 */
		public function __construct(ISchemaManager $schema, $error)
		{
			parent::__construct($schema, $error);
		}

		public function execute()
		{
			header('Access-Control-Allow-Origin: ' . $this->getOrigin());
			header('Access-Control-Allow-Methods: ' . $this->getMethod());
			header('Access-Control-Allow-Headers: Content-Type, Cookie');
			header('Access-Control-Allow-Credentials: true');
			header('Cache-Control: no-cache');
			header('Pragma: no-cache');

			if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS')
				die();

			$request = json_decode(file_get_contents('php://input'));

			if (!$this->authorized($request))
			{
				header('HTTP/1.0 403 Access Denied');
				return '';
			}

			$response = $this->process($request);
			header('Content-Type: application/json;charset=UTF-8');
			echo json_encode($response);
			die();
		}

		public function authorize($user)
		{
			$this->user = $user;
		}

		public function authorized($request)
		{
			return true;
		}

		/**
		 * Check if the given object can be created.
		 * @param object $object
		 * @return bool
		 */
		public function canCreate($object)
		{
			return true;
		}

		/**
		 * Check if the given object can be read.
		 * @return bool
		 */
		public function canRead()
		{
			return true;
		}

		/**
		 * Check if the given object can be updated.
		 * @param object $object
		 * @return bool
		 */
		public function canUpdate($object)
		{
			return true;
		}

		/**
		 * Check if the given object can be deleted.
		 * @param object $object
		 * @return bool
		 */
		public function canDelete($object)
		{
			return true;
		}

		public function process($object)
		{
			if (!isset($_SERVER['PATH_INFO']))
				return (object)['success' => false, 'error' => 'Unsupported request'];

			$args = explode('/', $_SERVER['PATH_INFO']);
			if (count($args) < 2 || !($args[1] == 'create' || $args[1] == 'read' || $args[1] == 'update' || $args[1] == 'delete' || $args[1] == 'query'))
				return (object)['success' => false, 'error' => 'Unknown method'];

			$method = $args[1];
			$varargs = count($args) > 2 ? array_slice($args, 2) : [];
			if ($object !== null)
				$varargs[] = $object;

			try
			{
				$return = $this->filter_call($method, $args);
				if($return === null)
					$return = call_user_func_array(array($this, $method), $varargs);
			}
			catch(Exception $e)
			{
				return (object)['success' => false, 'exception' => $e->getMessage()];
			}
			return (object)['success' => $return !== false, 'result' => $return === false ? null : $return];
		}

		/**
		 * Enable authorization and auditing
		 * @param string $endpoint The method that will be invoked
		 * @param string[] $args The arguments to be passed
		 */
		public function filter_call($endpoint, $args)
		{
			if(!$this->authorize_call($this->user, $endpoint, $args))
				throw new Exception('Not authorized');
			$this->audit_call($this->user, $endpoint, $args);
			return null;
		}

		/**
		 * Override this method to implement authorization
		 * @param IDataContainer $user The calling user
		 * @param string $endpoint The method being called
		 * @param string[] $args The arguments given
		 */
		public function authorize_call($user, $endpoint, $args)
		{
			return true;
		}

		/**
		 * Override this method to implement auditing
		 * @param IDataContainer $user The calling user
		 * @param string $endpoint The method being called
		 * @param string[] $args The arguments given
		 */
		public function audit_call($user, $endpoint, $args)
		{
		}

		public function create($object)
		{
			if (!$this->canCreate($object))
			{
				header('HTTP/1.0 403 Access Denied');
				return false;
			}
			return parent::create($object);
		}

		public function update($object)
		{
			if (!$this->canUpdate($object))
			{
				header('HTTP/1.0 403 Access Denied');
				return false;
			}
			parent::update($object);
			return true;
		}

		public function delete($object)
		{
			if (!$this->canDelete($object))
			{
				header('HTTP/1.0 403 Access Denied');
				return false;
			}
			parent::delete($object);
			return true;
		}

		public function query()
		{
			if (!$this->canRead())
			{
				header('HTTP/1.0 403 Access Denied');
				return false;
			}
			$query = array();
			$cols = $this->getValues();
			foreach ($_GET as $k => $v)
				if (in_array($k, $cols))
					$query[$k] = $v;
			$keys = array_keys($query);
			$q = false;
			do
			{
				$key = array_shift($keys);
				$q = $q ? $q->andColumn($key) : $this->search($key);
				if (strpos($query[$key], '%') !== false)
					$q = $q->like($query[$key]);
				else
					$q = $q->equals($query[$key]);
			}
			while (count($keys));
			return $q->execute();
		}

		public function read($lookup = null)
		{
			if (!$this->canRead())
			{
				header('HTTP/1.0 403 Access Denied');
				return false;
			}
			if(count($lookup))
			{
				$key = $this->getKey();
				if (is_array($key))
				{
					$search = array();
					foreach ($key as $i => $col)
						$search[$col] = $lookup[$i + 1];

					return parent::read($search);
				}
				else
				{
					return parent::read($lookup[1]);
				}
			}
			return parent::read();
		}

		protected $user;
	}
?>
