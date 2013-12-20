<?php
	abstract class KW_Module implements IModule
	{
		public function __construct()
		{
			$this->buildModule();
		}

		public function __toString()
		{
			$this->buildModule();
			$data = new StringBuilder();

			foreach ($this->sub_modules as $sub_module)
			{
				$sub_module->buildModule();
				$data->append($sub_module->renderModule());
			}

			$data->append($this->renderModule());

			return $data->__toString();
		}

		/**
		 * Adds a sub-module to the module.
		 *
		 * @param IModule $sub_module A sub-module to add.
		 */
		public function addSubModule($sub_module)
		{
			if ($sub_module instanceof IModule)
				$this->sub_modules[] = $sub_module;
		}

		/**
		 * @var IModule[] Stores sub-modules linked to this module.
		 */
		protected $sub_modules = Array();
	}
?>