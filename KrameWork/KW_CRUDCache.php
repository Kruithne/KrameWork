<?php
	abstract class KW_CRUDCache extends KW_CRUD
	{
		/**
		 * KW_CRUDCache constructor.
		 * @param ISchemaManager $schema
		 * @param ICacheState $state
		 */
		public function __construct(ISchemaManager $schema, ICacheState $state)
		{
			parent::__construct($schema);
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

		public function cache_clear()
		{
			$this->cache->clear('.#table#' . $this->getName());
		}

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
