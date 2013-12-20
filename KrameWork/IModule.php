<?php
	interface IModule
	{
		/**
		 * Builds the module, called just before the module is rendered.
		 */
		public function buildModule();

		/**
		 * Called after the module is built.
		 * @return string The output of the module.
		 */
		public function renderModule();
	}
?>