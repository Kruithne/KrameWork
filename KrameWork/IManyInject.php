<?php
	/**
	 * This works around php type hinting deficiencies in regards to injection of type[]
	 */
	interface IManyInject
	{
		/**
		 * Request all the ocmponents of a given type
		 *
		 * @param string $type A class or interface name
		 * @return object[] Zero or more objects of the requested type
		 */
		public function getComponents($type);
	}
?>
