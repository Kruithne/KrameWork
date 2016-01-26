<?php
	class KW_DependencyInjector implements IDependencyInjector, IManyInject
	{
		/**
		 * Add one or more components to the injector, either strings or preconstructed objects.
		 *
		 * @param string|string[]|object|object[] $classInput The name of the class to add or an already constructed object.
		 * @throws KW_ClassDependencyException
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
				if (array_key_exists($classInput, $this->classes))
					throw new KW_ClassDependencyException($classInput, "Class %s has already been added to the injector");

				if ($this->preload)
					KW_ClassLoader::loadClass($classInput);

				$this->classes[$classInput] = null;

				if ($this->bindInterfaces)
					$this->extractInterfaces($classInput);
			}
			elseif (is_object($classInput))
			{
				$className = get_class($classInput);
				if (array_key_exists($className, $this->classes))
				{
					if (!is_array($this->classes[$className]))
						$this->classes[$className] = array($this->classes[$className]);
					$this->classes[$className][] = $classInput;
				}
				else
				{
					$this->classes[$className] = $classInput;
				}

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
				$this->addBindingInternal($interface, $className);
		}

		/**
		 * Add a binding mapping a class or interface to another class or interface. $target should
		 * normally be a class, not an interface.
		 * @param string $source The name of a class or interface
		 * @param string|object $target The name of the class or a preconstructed object to return when the source is requested
		 */
		public function addBinding($source, $target)
		{
			$this->addBindingInternal($source, $target);
			$this->addComponent($target);
		}

		/**
		 * Add a binding mapping a class or interface to another class or interface. $target should
		 * normally be a class, not an interface.
		 * @param string $source The name of a class or interface
		 * @param string|object $target The name of the class or a preconstructed object to return when the source is requested
		 */
		private function addBindingInternal($source, $target)
		{
			$target_class = is_object($target) ? get_class($target) : $target;
			if (isset($this->bindings[$source]))
			{
				if (!is_array($this->bindings[$source]))
					$this->bindings[$source] = array($this->bindings[$source]);

				$this->bindings[$source][] = $target_class;
			}
			else
			{
				$this->bindings[$source] = $target_class;
			}
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
		 * @param string|string[] $class_name
		 * @return string|string[]
		 */
		public function resolve($class_name)
		{
			if (is_array($class_name))
			{
				$classes = array();
				foreach ($class_name as $class)
				{
					$resolved = $this->resolve($class);
					if (is_array($resolved))
					{
						foreach ($resolved as $resolvedClass)
							$classes[] = $resolvedClass;
					}
					else
					{
						$classes[] = $class;
					}
				}
				return $classes;
			}

			if (isset($this->bindings[$class_name]))
				return $this->resolve($this->bindings[$class_name]);

			return $class_name;
		}

		/**
		 * Returns a constructed component from the dependency injector.
		 *
		 * @param string $class_name The name of the class or interface to return.
		 * @param bool $add If true, injector will attempt to create the component if missing.
		 * @return object The object requested with dependencies injected.
		 * @throws KW_ClassDependencyException
		 */
		public function getComponent($class_name, $add = false)
		{
			$resolved_name = $this->resolve($class_name);
			if (is_array($resolved_name) || is_array($this->classes[$resolved_name]))
				throw new KW_ClassDependencyException($class_name, 'Class %s resolves to multiple classes, but a single instance was requested');

			if (!array_key_exists($resolved_name, $this->classes))
			{
				if ($add)
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
		 * Returns any number of constructed components from the dependency injector.
		 *
		 * @param string $class_name The name of the class or interface to return.
		 * @return object[] The objects requested with dependencies injected.
		 * @throws KW_ClassDependencyException
		 */
		public function getComponents(string $class_name)
		{
			$resolved_names = $this->resolve($class_name);
			if (!is_array($resolved_names))
				$resolved_names = array($resolved_names);

			$objects = array();
			foreach($resolved_names as $resolved_name)
			{
				if (!array_key_exists($resolved_name, $this->classes))
					throw new KW_ClassDependencyException($resolved_name, "Class %s has not been added to the injector");

				$object = $this->classes[$resolved_name];
				if ($object === null)
					$object = $this->constructComponent($resolved_name);

				if (!is_array($object))
					$object = array($object);

				foreach ($object as $obj)
				{
					if (isset($this->decorators[$resolved_name]))
					{
						foreach ($this->decorators[$class_name] as $decorator)
						{
							if ($decorator instanceof IDecorator)
							{
								// TODO This will probably be buggy, redesign interface or disallow
								$decorator->inject($obj);
								$obj = $decorator;
							}
							else
							{
								$obj = new $decorator($obj);
							}
						}
					}
					$objects[] = $obj;
				}
			}
			return $objects;
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

					if ($parameter_class_name == 'IManyInject')
						$to_inject[] = $this;
					else
						$to_inject[] = $this->getComponent($parameter_class_name);
				}

				call_user_func_array(array($object, "__construct"), $to_inject);
			}

			if ($this->classes[$class_name] === null)
				$this->classes[$class_name] = $object;

			return $object;
		}
 
 		/**
		 * Create and retun a bootstrap given the config.
		 * @param array $config A mix of Library, Binding, ValueInjector, and strings
		 * @return string A compiled kernel bootstrap, save it to a file and include it in your bootstrap
		 */
		public function makeBootstrap($config)
		{
			$path = array();
			$class = array();
			$binding = array();
			$decorator = array();
			$decorate = array();

			foreach ($config as $item)
			{
				$reflect = null;
				if ($item instanceof Library)
				{
					KW_ClassLoader::addClassPath($item->path);
					$path[] = "'" . $item->path . "'";
				}
				else if ($item instanceof Binding)
				{
					if ($item->target instanceof ValueInjector)
					{
						$reflect = new ReflectionClass($item->target->class);
						if (!isset($binding[$item->source]))
							$binding[$item->source] = array();
						$binding[$item->source][] = $item->target->class;
						$class[] = "'" . $item->target->class . "'=>new " . $item->target->class . "('" . join("','", $item->target->args) . "')";
					}
					else
					{
						$reflect = new ReflectionClass($item->target);
						if (!isset($binding[$item->source]))
							$binding[$item->source] = array();
						$binding[$item->source][] = $item->target;
						$class[] = "'" . $item->target . "'=>null";
					}
				}
				else if ($item instanceof ValueInjector)
				{
					$reflect = new ReflectionClass($item->class);
					$class[] = "'" . $item->class . "'=>new " . $item->class . "('" . join("','", $item->args) . "')";
				}
				else if ($item instanceof Decorator)
				{
					if (!isset($decorate[$item->source]))
						$decorator[$item->source] = array();

					$decorator[$item->source][] = "'" . $item->target . "'";
				}
				else
				{
					$reflect = new ReflectionClass($item);
					$class[] = "'" . $item . "'=>null";
				}

				if ($reflect)
				{
					foreach ($reflect->getInterfaceNames() as $interface)
					{
						if (!isset($binding[$interface]))
							$binding[$interface] = array();

						if (!in_array($reflect->getName(), $binding[$interface]))
							$binding[$interface][] = $reflect->getName();
					}
				}
			}

			$bindings = array();
			foreach ($binding as $source => $target)
			{
				if (count($target) > 1)
					$bindings[] = "'" . $source . "'=>['" . join("','", $target) . "']";
				else
					$bindings[] = "'" . $source . "'=>'" . $target[0] . "'";
			}

			foreach ($decorator as $source => $targets)
				$decorate[] = "'" . $source . "'=>[" . join(',', $targets) . "]";

			return sprintf(
				'<?php $kernel = new KrameSystem(KW_DEFAULT_FLAGS,[%s],[%s],[%s],[%s]); ?>',
				join(',', $path), join(',', $class), join(',', $bindings), join(',', $decorate)
			);
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
