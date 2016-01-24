<?php
	class Library
	{
		/**
		 * Defines a class autoload path
		 * @param string $path A path containing class/interface files
		 */
		public function __construct($path)
		{
			$this->path = $path;
		}

		/**
		 * @var string
		 */
		public $path;
	}

	class Binding
	{
		/**
		 * Defines a class/interface binding
		 * @param string $source The name of a class or interface
		 * @param string|ValueInjector $target The name of a class or interface
		 */
		public function __construct($source, $target)
		{
			$this->source = $source;
			$this->target = $target;
		}

		/**
		 * @var string
		 */
		public $source;

		/**
		 * @var string|ValueInjector
		 */
		public $target;
	}

	class Decorator
	{
		/**
		 * Defines a class/interface decorator
		 * @param string $source The name of a class or interface
		 * @param string $target The name of a class or interface
		 */
		public function __construct($source, $target)
		{
			$this->source = $source;
			$this->target = $target;
		}

		/**
		 * @var string
		 */
		public $source;

		/**
		 * @var string
		 */
		public $target;
	}

	class ValueInjector
	{
		/**
		 * Defines a class that needs to be constructed with certain arguments, like a database connection
		 * @param string $class The namee of the class
		 * @param string[] $args The arguments to pass to the class when it gets constructed
		 */
		public function __construct($class, $args)
		{
			$this->class = $class;
			$this->args = $args;
		}

		/**
		 * @var string
		 */
		public $class;

		/**
		 * @var string[]
		 */
		public $args;
	}
?>
