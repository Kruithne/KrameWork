<?php
	class KW_ClassDependencyException extends KW_Exception
	{
		/**
		 * Crafts a new class dependency exception.
		 *
		 * @param string $class_name Name of the class the exception relates to.
		 * @param int $message Exception message.
		 */
		public function __construct($class_name, $message)
		{
			parent::__construct(sprintf($message, $class_name));
		}
	}
?>