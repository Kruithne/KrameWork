<?php
	interface ICacheState
	{
		/**
		 * Called to update the timestamp for a cache key
		 *
		 * @param string $cacheKey A string identifying the cached object.
		 */
		public function clear($cacheKey);

		/**
		 * Called to get the timestamp for a cache key
		 *
		 * @param string $statement
		 * @return int
		 * @internal param string $cacheKey A string identifying the cached object.
		 */
		public function read($statement);
	}
?>
