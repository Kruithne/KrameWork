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
		 */
		public function __construct(ISchemaManager $schema)
		{
			parent::__construct($schema);
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
			$path = false;
			if (isset($_SERVER['PATH_INFO']))
				$path = $_SERVER['PATH_INFO'];

			if ($_SERVER['REQUEST_METHOD'] == 'POST')
			{
				switch($path)
				{
					case '/create':
						if (!$this->canCreate($object))
						{
							header('HTTP/1.0 403 Access Denied');
							return '';
						}
						try
						{
							return $this->create($object);
						}
						catch (PDOException $e)
						{
							return $e;
						}

					case '/update':
						if (!$this->canUpdate($object))
						{
							header('HTTP/1.0 403 Access Denied');
							return false;
						}
						$this->update($object);
						return true;

					case '/delete':
						if (!$this->canDelete($object))
						{
							header('HTTP/1.0 403 Access Denied');
							return false;
						}
						$this->delete($object);
						return true;

					default:
						return false;
				}
			}

			if($_SERVER['REQUEST_METHOD'] == 'GET')
			{
				if (!$this->canRead())
				{
					header('HTTP/1.0 403 Access Denied');
					return false;
				}

				if ($path)
				{
					$lookup = explode('/', $path);
					$key = $this->getKey();
					if (is_array($key))
					{
						$search = array();
						foreach($key as $i => $col)
							$search[$col] = $lookup[$i + 1];

						return $this->read($search);
					}
					else
					{
						return $this->read($lookup[1]);
					}
				}
				return $this->read();
			}
			return false;
		}
	}
?>
