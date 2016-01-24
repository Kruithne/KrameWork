<?php
	class KW_DependencyInjector implements IDependencyInjector
	{
		/**
		 * Registers a class to be used by the dependency injector.
		 *
		 * @param string|object $classInput The name of the class to add or an already constructed object.
		 */
		public function addComponent($classInput)
		{
			if (is_array($classInput))
			{
				foreach ($classInput as $classInputItem)
					$this->addComponent($classInputItem);
			}

			if (is_string($classInput))
			{
				if (!array_key_exists($classInput, $this->classes))
				{
					if ($this->preload)
						KW_ClassLoader::loadClass($classInput);

					$this->classes[$classInput] = null;

					if ($this->bindInterfaces)
						$this->extractInterfaces($classInput);
				}
			}
			elseif (is_object($classInput))
			{
				$className = get_class($classInput);
				if (!array_key_exists($className, $this->classes))
					$this->classes[$className] = $classInput;

				if ($this->bindInterfaces)
					$this->extractInterfaces($className);
			}
		}

		/**
		 * Extract interfaces from a class and bind them.
		 * @param string $className
		 */
		private function extractInterfaces($className)
		{
			$class = new ReflectionClass($className);
			foreach ($class->getInterfaceNames() as $interface)
				$this->addBinding($interface, $className);
		}

		/**
		 * Add a binding mapping a class or interface to another class or interface. $target should
		 * normally be a class, not an interface.
		 * @param string $source The name of a class or interface
		 * @param string $target The name of the class to return when the source is requested
		 */
		public function addBinding($source, $target)
		{
			$this->bindings[$source] = $target;
			$this->addComponent($target);
		}

		/**
		 * Add a decorator such that a request for $bind returns $with with $bind injected into it
		 * @param string $bind The name of a class or interface
		 * @param string $with The name of a class. must have a constructor that takes $bind objects as the only argument.
		 */
		public function addDecorator($bind, $with)
		{
			if (!isset($this->decorators[$bind]))
				$this->decorators[$bind] = array();

			$this->decorators[$bind][] = $with;
		}

		/**
		 * @param string $class_name
		 * @return string
		 */
		public function resolve($class_name)
		{
			if (isset($this->bindings[$class_name]))
				return $this->resolve($this->bindings[$class_name]);

			return $class_name;
		}

		/**
		 * Returns a constructed component from the dependency injector.
		 *
		 * @param string $class_name The name of the class to return.
		 * @param bool $create If true, injector will attempt to create the component if missing.
		 * @return object The object requested with dependencies injected.
		 * @throws KW_ClassDependencyException
		 */
		public function getComponent($class_name, $create = false)
		{
			$resolved_name = $this->resolve($class_name);
			if (!array_key_exists($resolved_name, $this->classes))
			{
				if ($create)
					$this->addComponent($class_name);
				else
					throw new KW_ClassDependencyException($resolved_name, "Class %s has not been added to the injector");
			}

			$object = $this->classes[$resolved_name];
			if ($object === null)
				$object = $this->constructComponent($resolved_name);

			if (isset($this->decorators[$class_name]))
			{
				foreach ($this->decorators[$class_name] as $decorator)
				{
					if ($decorator instanceof IDecorator)
					{
						$decorator->inject($object);
						$object = $decorator;
					}
					else
					{
						$object = new $decorator($object);
					}
				}
			}
			return $object;
		}

		/**
		 * Returns a fully constructed object using components available to the injector.
		 *
		 * @param string $class_name The class to use when building the object.
		 * @return object A fully constructed instance of the component.
		 * @throws KW_ClassDependencyException
		 */
		private function constructComponent($class_name)
		{
			$class = new ReflectionClass($class_name);

			if (!$class->isInstantiable())
				throw new KW_ClassDependencyException($class_name, "Class %s cannot be instantiated");

			$to_inject = array();
			$constructor = $class->getConstructor();
			$object = $class->newInstanceWithoutConstructor();

			if ($constructor)
			{
				foreach ($constructor->getParameters() as $parameter)
				{
					$parameter_class = $parameter->getClass();
					if ($parameter_class === null)
						throw new KW_ClassDependencyException($class_name, "Constructor for %s contains parameters with an undefined class");

					$parameter_class_name = $parameter_class->getName();
					if ($parameter_class_name === $class_name)
						throw new KW_ClassDependencyException($class_name, "Cyclic dependency when constructing %s");

					$to_inject[] = $this->getComponent($parameter_class_name);
				}

				call_user_func_array(array($object, "__construct"), $to_inject);
			}

			if ($this->classes[$class_name] === null)
				$this->classes[$class_name] = $object;

			return $object;
		}

		/**
		 * Pre-compiled boot-loader injection point
		 * @param array $components Pre-load classes with this collection
		 * @param array $bindings Pre-load type bindings with this collection
		 * @param array $decorators Pre-load decorators with this collection
		 */
		public function __construct($components = null, $bindings = null, $decorators = null)
		{
			if ($components)
				$this->classes = $components;

			if ($bindings)
				$this->bindings = $bindings;

			if ($decorators)
				$this->decorators = $decorators;
		}

		/**
		 * @var object[]
		 */
		private $classes = array();

		/**
		 * @var array
		 */
		private $bindings = array();

		/**
		 * @var array
		 */
		private $decorators = array();

		/**
		 * @var bool
		 */
		protected $preload = false;

		/**
		 * @var bool
		 */
		protected $bindInterfaces = false;
	}
?>
