<?php
	class KW_CacheStateTable extends KW_Repository implements ICacheState
	{
		/**
		 * KW_CacheStateTable constructor.
		 * @param ISchemaManager $schema
		 */
		public function __construct(ISchemaManager $schema)
		{
			$schema->addTable($this);
			parent::__construct();
		}

		public function clear($cacheKey)
		{
			$this->read($cacheKey);
			$this->update->key = $cacheKey;
			$this->update->timestamp = time();
			$this->update->execute();
		}

		public function read($cacheKey)
		{
			$this->select->key = $cacheKey;
			$result = $this->select->getRows();

			if (!$result || count($result) == 0)
			{
				$ts = time();
				$this->insert->key = $cacheKey;
				$this->insert->timestamp = $ts;
				$this->insert->execute();
				return $ts;
			}
			return $result[0]->timestamp;
		}

		public function prepare()
		{
			switch ($this->db->getType())
			{
				case 'pgsql':
					$this->select = $this->db->prepare('SELECT "timestamp" FROM _cache WHERE "key" = :key');
					$this->update = $this->db->prepare('UPDATE _cache SET "timestamp" = :timestamp WHERE "key" = :key');
					$this->insert = $this->db->prepare('INSERT INTO _cache ("key","timestamp") VALUES (:key, :timestamp)');

					break;

				default:
					$this->select = $this->db->prepare('SELECT `timestamp` FROM _cache WHERE `key` = :key');
					$this->update = $this->db->prepare('UPDATE _cache SET `timestamp` = :timestamp WHERE `key` = :key');
					$this->insert = $this->db->prepare('INSERT INTO _cache (`key`,`timestamp`) VALUES (:key, :timestamp)');
			}
		}

		public function getName()
		{
			return '_cache';
		}

		public function getVersion()
		{
			return 1;
		}

		public function getQueries()
		{
			switch($this->db->getType())
			{
				case 'pgsql':
					return array(
						1 => array('
							CREATE TABLE _cache (
								"key" VARCHAR(100),
								"timestamp" INTEGER,
								PRIMARY KEY("key")
							)'
						)
					);
					break;

				default:
					return array(
						1 => array('
							CREATE TABLE `_cache` (
								`key` VARCHAR(100),
								`timestamp` INTEGER,
								PRIMARY KEY(`key`)
							)'
						)
					);
			}
		}
	}
?>
