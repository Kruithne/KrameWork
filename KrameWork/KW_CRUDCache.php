<?php
	abstract class KW_CRUDCache extends KW_CRUD
	{
		/**
		 * KW_CRUDCache constructor.
		 * @param ISchemaManager $schema
		 * @param ICacheState $state
		 * @param IErrorHandler|null $error
		 */
		public function __construct(ISchemaManager $schema, ICacheState $state, $error)
		{
			parent::__construct($schema, $error);
			$this->cache = $state;
		}

		public function create($object)
		{
			$result = parent::create($object);
			$this->cache_clear();
			return $result;
		}

		public function update($object)
		{
			parent::update($object);
			$this->cache_clear();
		}

		public function delete($object)
		{
			parent::delete($object);
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
