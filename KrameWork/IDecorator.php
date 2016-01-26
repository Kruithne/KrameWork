<?php
	interface IDecorator
	{
		/**
		 * @param object $object An object to decorate
		 * @return object A decorated object
		 */
		public function inject($object);
	}
?>
