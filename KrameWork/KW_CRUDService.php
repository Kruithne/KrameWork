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

			$input = trim(file_get_contents('php://input'));
			if($input)
				$input = json_decode($input);
			$request = $input ? $this->getNewObject($input) : null;

			if (!$this->authorized($request))
			{
				header('HTTP/1.0 401 Unauthorized');
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
				{
					$return = call_user_func_array(array($this, $method), $varargs);
					if($return !== false)
						$this->audit_call_success($this->user, $method, $args, $result);
					else
						$this->audit_call_failed($this->user, $method, $args, null);
				}
			}
			catch(Exception $e)
			{
				$this->audit_call_failed($this->user, $method, $args, $e);
				return (object)['success' => false, 'exception' => $e->getMessage()];
			}
			return (object)['success' => $return !== false, 'result' => $return === false ? null : $return];
		}

		/**
		 * Enable authorization and auditing
		 * @param string $endpoint The method that will be invoked
		 * @param string[] $args The arguments to be passed
		 * @return mixed Return null for normal processing, return anything else to abort the call
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
		 * @return bool Return false to throw an exception blocking the call
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

		/**
		 * Override this method to implement change auditing
		 * @param IDataContainer $user The calling user
		 * @param string $endpoint The method being called
		 * @param string[] $args The arguments given
		 * @param object $old The object prior to change
		 * @param object $new The object as it is now persisted
		 */
		public function audit_change($user, $endpoint, $args, $old, $new)
		{
		}

		/**
		 * Override this method to implement success auditing
		 * @param IDataContainer $user The calling user
		 * @param string $endpoint The method being called
		 * @param string[] $args The arguments given
		 * @param mixed $result The result of the endpoint execution
		 */
		public function audit_call_success($user, $endpoint, $args, $result)
		{
		}

		/**
		 * Override this method to implement failure auditing
		 * @param IDataContainer $user The calling user
		 * @param string $endpoint The method being called
		 * @param string[] $args The arguments given
		 * @param Exception|null $exception An exception when applicable
		 */
		public function audit_call_failed($user, $endpoint, $args, $exception)
		{
		}

		public function create($object)
		{
			if (!$this->canCreate($object))
			{
				header('HTTP/1.0 403 Access Denied');
				return false;
			}
			$new = parent::create($object);
			if($this->changeTracking)
				$this->audit_change($this->user, 'create', [$object], null, $new);
			return $new;
		}

		public function update($object)
		{
			if (!$this->canUpdate($object))
			{
				header('HTTP/1.0 403 Access Denied');
				return false;
			}
			if($this->changeTracking)
			{
				if($object instanceof IDataContainer)
					$v = $object->getAsArray();
				else
					$v = (array)$object;
				$old = $this->_read($v);
			}
			parent::update($object);
			if($this->changeTracking)
			{
				$new = $this->_read($v);
				$this->audit_change($this->user, 'update', [$object], $old, $new);
			}
			return true;
		}

		public function delete($object)
		{
			if (!$this->canDelete($object))
			{
				header('HTTP/1.0 403 Access Denied');
				return false;
			}
			if($this->changeTracking)
			{
				if($object instanceof IDataContainer)
					$v = $object->getAsArray();
				else
					$v = (array)$object;
				$old = $this->_read($v);
			}
			parent::delete($object);
			if($this->changeTracking)
				$this->audit_change($this->user, 'delete', [$object], $old, null);
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
			$key = $this->getKey();
			if (!$key)
				$key = [];
			if (!is_array($key))
				$key = [$key];
			foreach ($_GET as $k => $v)
				if (in_array($k, $cols) || in_array($k, $key))
					$query[$k] = $v;
			return $this->readQuery($query);
		}

		/**
		 * Override this method to gain fine controlled access restrictions for loading data
		 * @param array $query key/value pairs passed by the client
		 * @return mixed An array of matching objects or false
		 */
		public function readQuery($query)
		{
			$keys = array_keys($query);
			if(count($keys) == 0)
				return false;
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
			if(is_array($lookup) && count($lookup))
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
			return parent::read($lookup);
		}

		protected $changeTracking = false;
		protected $user;
	}
?>
