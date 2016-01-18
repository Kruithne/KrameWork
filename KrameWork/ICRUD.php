<?php
	interface ICRUD
	{
		/**
		 * DESCRIPTION
		 * @return TYPE
		 */
		public function getKey();

		/**
		 * DESCRIPTION
		 * @return TYPE
		 */
		public function hasAutoKey();

		/**
		 * DESCRIPTION
		 * @return TYPE
		 */
		public function getValues();

		/**
		 * DESCRIPTION
		 * @param TYPE $key
		 * @return TYPE
		 */
		public function getKeyType($key);

		/**
		 * DESCRIPTION
		 * @param TYPE $data
		 * @return TYPE
		 */
		public function getNewObject($data);

		/**
		 * DESCRIPTION
		 * @return TYPE
		 */
		public function prepare();

		/**
		 * DESCRIPTION
		 * @param TYPE $object
		 * @return TYPE
		 */
		public function create($object);

		/**
		 * DESCRIPTION
		 * @param null|TYPE $key
		 * @return TYPE
		 */
		public function read($key = null);

		/**
		 * DESCRIPTION
		 * @param TYPE $object
		 * @return TYPE
		 */
		public function update($object);

		/**
		 * DESCRIPTION
		 * @param TYPE $object
		 * @return TYPE
		 */
		public function delete($object);
	}
?>
