<?php
	abstract class KW_CacheAwareCRUDService extends KW_CRUDService
	{
		public function getLevel()
		{
			return 'private';
		}

		/**
		 * KW_CacheAwareCRUDService constructor.
		 * @param ISchemaManager $schema
		 * @param ICacheState $state
		 * @param IErrorHandler|null $error
		 */
		public function __construct(ISchemaManager $schema, ICacheState $state, $error)
		{
			parent::__construct($schema, $error);
			$this->cache = $state;
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

			$input = trim(file_get_contents('php://input'));
			if($input)
				$input = json_decode($input);
			$request = $input ? $this->getNewObject($input) : null;

			if (!$this->authorized($request))
			{
				header('HTTP/1.0 403 Access Denied');
				return '';
			}

			$response = $this->process($request);
			if(headers_sent())
				die();
			header('Content-Type: application/json;charset=UTF-8');
			echo json_encode($response);
			die();
		}

		protected function _create($object)
		{
			$result = parent::_create($object);
			$this->cache_clear();
			return $result;
		}

		protected function _update($object)
		{
			parent::_update($object);
			$this->cache_clear();
		}

		protected function _delete($object)
		{
			parent::_delete($object);
			$this->cache_clear();
		}

		/**
		 * Invalidates the cache of this service
		 */
		public function cache_clear()
		{
			$this->cache->clear('.#table#' . $this->getName());
		}

		/**
		 * Get the current timestamp for the cache of this service
		 * @return int Unix timestamp saying when the table was last updated
		 */
		public function cache_read()
		{
			return $this->cache->read('.#table#' . $this->getName());
		}

		/**
		 * @var ICacheState
		 */
		private $cache;
	}
?>
