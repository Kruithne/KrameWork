<?php
	interface IDependencyInjector
	{
		/**
		 * Registers a class to be used by the dependency injector.
		 *
		 * @param string|object $classInput The name of the class to add or an already constructed object.
		 */
		public function addComponent($classInput);

		public function addBinding($source, $target);

		public function resolve($class_name);

		/**
		 * Returns a constructed component from the dependency injector.
		 *
		 * @param string $class_name The name of the class to return.
		 * @return object The object requested with dependencies injected.
		 * @throws KW_ClassDependencyException
		 */
		public function getComponent($class_name);
	}
?>
