<?php
	namespace KrameWork;

	class DuplicateClassException extends \Exception {}
	class ClassResolutionException extends \Exception {}
	class ClassInstantiationException extends \Exception {}
	class InterfaceBindingException extends \Exception {}

	/**
	 * Class DependencyInjector
	 * Constructs instantiable classes and their dependencies.
	 *
	 * @package KrameWork\DI
	 */
	class DependencyInjector
	{
		const AUTO_BIND_INTERFACES = 0x1;
		const AUTO_ADD_DEPENDENCIES = 0x2;
		const DEFAULT_FLAGS = self::AUTO_BIND_INTERFACES | self::AUTO_ADD_DEPENDENCIES;

		/**
		 * DependencyInjector constructor.
		 *
		 * @api __construct
		 * @param int $flags Flags to control how this module behaves.
		 * @param (string|object)[]|null $components Initial components.
		 * @param array<string, string|object>|null $bindings Initial bindings.
		 */
		public function __construct(private int $flags = self::DEFAULT_FLAGS, ?array $components = null, ?array $bindings = null) {
			$this->classList = [];
			$this->bindingList = [];

			// Add initial components.
			if ($components !== null)
				$this->addComponent($components);

			// Add initial interface bindings.
			if ($bindings)
				foreach ($bindings as $interface => $class)
					$this->bind($interface, $class);

			// If DependencyInjector is requested, we should return our own instance.
			$this->addComponent($this);
		}

		/**
		 * Add a component to the injector.
		 * string: Name of a class which can be instantiated.
		 * object: Pre-constructed object.
		 * array: Multiple of the above.
		 *
		 * @api addComponent
		 * @param string|(string|object)[]|object|null $class
		 * @throws DuplicateClassException
		 * @throws ClassResolutionException
		 */
		public function addComponent(string|array|object|null $class): void {
			// Null: Do nothing.
			if (!$class)
				return;

			// Array: Loop all items and add them one by one.
			if (\is_array($class)) {
				foreach ($class as $classItem)
					$this->addComponent($classItem);

				return;
			}

			// String: Class name, add to the internal class list.
			if (\is_string($class)) {
				if (\array_key_exists($class, $this->classList))
					throw new DuplicateClassException('Duplicate class added to injector: ' . $class);

				$this->classList[$class] = null;

				if ($this->flags & self::AUTO_BIND_INTERFACES)
					$this->bindInterfaces($class);

				return;
			}

			// Object: Extract class and add that.
			$className = \get_class($class);
			if (\array_key_exists($className, $this->classList))
				throw new DuplicateClassException('Duplicate class added to injector: ' . $className);

			$this->classList[$className] = $class;

			if ($this->flags & self::AUTO_BIND_INTERFACES)
				$this->bindInterfaces($className);
		}

		/**
		 * Resolve a given class name.
		 * object: Resolves the class name of the object.
		 * string: Resolves the given string as a class name.
		 * array: Multiple of the above, returns array.
		 *
		 * @internal
		 * @param mixed $class
		 * @param string[] $output
		 * @return string|string[]
		 * @throws ClassResolutionException
		 */
		public function resolveClassName(mixed $class, array &$output = []): string|array {
			// Array given, loop elements and process.
			if (\is_array($class)) {
				foreach ($class as $node)
					$this->resolveClassName($node, $output);

				return $output;
			}

			// Resolve class name from object.
			if (\is_object($class))
				$class = \get_class($class);

			// If we don't have a string at this point, we can't do much with it.
			if (!\is_string($class))
				throw new ClassResolutionException('Unable to resolve class for: ' . \gettype($class));

			// Check interface bindings for the class.
			if (\array_key_exists($class, $this->bindingList))
				return $this->resolveClassName($this->bindingList[$class], $output);

			$output[] = $class;
			return $class;
		}

		/**
		 * Obtain the injectors instance of a specific component.
		 * Non-constructed object instances will be instantiated.
		 *
		 * @api getComponent
		 * @param string $className Class name of the component to create.
		 * @param bool $add Attempt to add the class to the injector if missing.
		 * @param bool $throw If the component cannot be found, throws an error. Otherwise returns NULL.
		 * @return object|null
		 * @throws ClassResolutionException
		 * @throws ClassInstantiationException
		 */
		public function getComponent(string $className, bool $add = false, bool $throw = true): ?object {
			$resolve = $this->resolveClassName($className);

			// getComponent() should only ever return a single component.
			if (\is_array($resolve))
				throw new ClassResolutionException('Injector contains multiple resolutions for class: ' . $className);

			// Check if component is missing and react according to $add.
			if (!\array_key_exists($resolve, $this->classList)) {
				if ($add && \class_exists($resolve) && (new \ReflectionClass($resolve))->isInstantiable()) {
					$this->addComponent($resolve);
				} else {
					if (!$throw)
						return null;
					
					throw new ClassResolutionException('Injector does not have valid match for class: ' . $resolve);
				}
			}

			// Return cached instance, or construct new one.
			return $this->classList[$resolve] ?? $this->constructComponent($resolve);
		}

		/**
		 * Retrieve components from the injector that implement the given interface.
		 *
		 * @api getImplementors
		 * @param string $interfaceName Interface components must implement to be returned.
		 * @param bool $add Attempt to add the class to the injector if missing.
		 * @return object[]
		 */
		public function getImplementors(string $interfaceName, bool $add = false): array {
			$resolves = $this->resolveClassName($interfaceName);
			if (!\is_array($resolves))
				$resolves = [$resolves];

			$objects = [];
			foreach ($resolves as $resolve) {
				// Class has not yet been registered with the injector.
				if (!\array_key_exists($resolve, $this->classList)) {
					if ($add)
						$this->addComponent($resolve);
					else
						continue;
				}

				$objects[] = $this->classList[$resolve] ?? $this->constructComponent($resolve);
			}
			return $objects;
		}

		/**
		 * Attempt to construct a class with the given name.
		 *
		 * @internal
		 * @param string $className Name of the class to construct.
		 * @return object Constructed class.
		 * @throws ClassInstantiationException
		 */
		public function constructComponent(string $className): object {
			if (!\class_exists($className))
				throw new ClassInstantiationException('Unknown class: ' . $className);

			$class = new \ReflectionClass($className);

			if (!$class->isInstantiable())
				throw new ClassInstantiationException('Non-instantiable class: ' . $className);

			$inject = [];
			$constructor = $class->getConstructor();
			if ($constructor)
				foreach ($constructor->getParameters() as $param)
					$inject[] = $this->resolveParameter($param, $className);

			$object = $class->newInstanceArgs($inject);
			$this->classList[$className] = $object;
			return $object;
		}

		private function resolveParameter(\ReflectionParameter $param, string $className): mixed {
			$paramType = $param->getType();
			if (!$paramType)
				throw new ClassInstantiationException('Constructor contains undefined parameter: ' . $className);

			$types = $paramType instanceof \ReflectionUnionType ? $paramType->getTypes() : [$paramType];
			foreach ($types as $type) {
				if (!($type instanceof \ReflectionNamedType))
					throw new ClassInstantiationException(\sprintf('Intersection type for param #%d (%s) for %s is not supported', $param->getPosition(), $paramType, $className));

				$component = $this->resolveNamedTypeComponent($type, $className);
				if ($component !== null)
					return $component;
			}

			if ($param->isOptional())
				return $param->getDefaultValue();

			if ($paramType->allowsNull())
				return null;

			throw new ClassInstantiationException(\sprintf('Injector cannot satisfy non-nullable param #%d (%s) for %s', $param->getPosition(), $paramType, $className));
		}

		/**
		 * Resolves a component for a given named type.
		 * @internal
		 * @param \ReflectionNamedType $type
		 * @param string $className Name of the class the type belongs to.
		 * @throws ClassInstantiationException
		 * @return object|null
		 */
		private function resolveNamedTypeComponent(\ReflectionNamedType $type, string $className): ?object {
			if ($type->isBuiltin())
				return null;

			$typeName = $type->getName();
			if ($typeName === $className)
				throw new ClassInstantiationException('Cyclic dependency in class ' . $className);

			return $this->getComponent($typeName, ($this->flags & self::AUTO_ADD_DEPENDENCIES) !== 0, false);
		}

		/**
		 * Detect interfaces for a class and bind them to it.
		 *
		 * @internal
		 * @param string $className Class to bind interfaces for.
		 * @throws ClassResolutionException
		 */
		private function bindInterfaces(string $className): void {
			if (!\class_exists($className))
				throw new ClassResolutionException('Unknown class: ' . $className);

			$class = new \ReflectionClass($className);
			foreach ($class->getInterfaceNames() as $interface)
				$this->bind($interface, $className);
		}

		/**
		 * Manually bind an interface to a class.
		 * $class string: Binds to class name.
		 * $class object: Binds to the objects class name.
		 *
		 * @api bind
		 * @param string $interface Interface name to bind class to.
		 * @param mixed $class Class (string or object) to bind the interface to.
		 * @throws InterfaceBindingException
		 */
		public function bind(string $interface, mixed $class): void {
			if (\is_object($class))
				$class = \get_class($class);

			if (!\is_string($class))
				throw new InterfaceBindingException('Invalid input for interface binding: ' . \gettype($class));

			if (\array_key_exists($interface, $this->bindingList)) {
				$node = $this->bindingList[$interface];

				if (!\is_array($node))
					$node = [$node];

				$node[] = $class;
				$this->bindingList[$interface] = $node;
			} else {
				$this->bindingList[$interface] = $class;
			}
		}

		/**
		 * @var array<string, object|null>
		 */
		private array $classList;

		/**
		 * @var array<string, string|string[]>
		 */
		private array $bindingList;
	}