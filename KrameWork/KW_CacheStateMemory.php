<?php
	class KW_CacheStateMemory implements ICacheState
	{
		public function __construct(Cache $memcache)
		{
			$this->cache = $memcache->getCache();
		}

		public function clear($cacheKey)
		{
			$this->cache->set($cacheKey, time());
		}

		public function read($statement)
		{
			$ts = $this->cache->get($cacheKey);
			if(!$ts)
			{
				$this->clear($cachKey);
				$ts = $this->cache->get($cacheKey);
			}
			return $ts;
		}

		private $cache;
	}
?>
