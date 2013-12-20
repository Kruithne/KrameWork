<?php
	class KW_ClassDependencyException extends KW_Exception
	{
		public function __construct($class_name, $message)
		{
			parent::__construct(sprintf($message, $class_name));
		}
	}
?>