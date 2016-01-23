<?php
	abstract class KW_CacheAwareCRUDService extends KW_CRUDCache implements ICRUDService
	{
		public function getOrigin()
		{
			return '*';
		}

		public function getMethod()
		{
			return 'GET, POST';
		}

		public function getLevel()
		{
			return 'private';
		}

		public function __construct(ISchemaManager $schema, ICacheState $state)
		{
			parent::__construct($schema, $state);
		}

		public function execute()
		{
			$cached = false;
			if (function_exists('apache_request_headers'))
			{
				$req = apache_request_headers();
				if (isset($req['If-Modified-Since']))
					$cached = strtotime($req['If-Modified-Since']);
			}

			header('Access-Control-Allow-Origin: ' . $this->getOrigin());
			header('Access-Control-Allow-Methods: ' . $this->getMethod());
			header('Access-Control-Allow-Headers: Content-Type, Cookie');
			header('Access-Control-Allow-Credentials: true');
			header('Cache-Control: ' . $this->getLevel());

			if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS')
				die();

			$modified = $this->cache_read();
			header('X-Modified: ' . serialize($modified));

			if ($modified && $_SERVER['REQUEST_METHOD'] == 'GET')
			{
				header('Last-Modified: ' . date('r', $modified));
				header('Expires: ' . date('r', $modified + 365 * 24 * 3600));
				if ($cached && $cached >= $modified)
				{
					header('HTTP/1.1 304 Not Modified');
					die();
				}
			}

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

		public function canCreate($object)
		{
			return true;
		}

		public function canRead()
		{
			return true;
		}

		public function canUpdate($object)
		{
			return true;
		}

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
						try
						{
							return $this->create($object);
						}
						catch(PDOException $e)
						{
							return $e;
						}

					case '/update':
						$this->update($object);
						return true;

					case '/delete':
						$this->delete($object);
						return true;

					default:
						return false;
				}
			}
			if ($_SERVER['REQUEST_METHOD'] == 'GET')
			{
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
		}
	}
?>
