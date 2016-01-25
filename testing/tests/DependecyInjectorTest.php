<?php
	require_once("/home/travis/build/Kruithne/KrameWork/testing/resources/default_bootstrap.php");

	class DependencyInjectorTest extends PHPUnit_Framework_TestCase
	{
		/**
		 * Basic injection via an interface binding
		 */
		public function testInterfaceInjection()
		{
			$kernel = new KrameSystem(KW_PRELOAD_CLASSES);
			$kernel->addBinding('IMockDependency', 'MockDependency');
			$component = $kernel->getComponent('IMockDependency');
			$this->assertEquals('MockDependency', get_class($component), 'Kernel did not return an IMockDependency object');
		}

		/**
		 * Basic injection via an interface binding and a preconstructed object
		 */
		public function testInterfaceInjectionPreCreated()
		{
			$kernel = new KrameSystem(KW_PRELOAD_CLASSES);
			$kernel->addBinding('IMockDependency', new MockDependency());
			$component = $kernel->getComponent('IMockDependency');
			$this->assertEquals('MockDependency', get_class($component), 'Kernel did not return an IMockDependency object');
		}

		/**
		 * Injection with a decorator via an interface binding
		 */
		public function testDecoratedInterfaceInjection()
		{
			$kernel = new KrameSystem(KW_PRELOAD_CLASSES);
			$kernel->addBinding('IMockDependency', 'MockDependency');
			$kernel->addDecorator('IMockDependency', 'MockDecorator');
			$component = $kernel->getComponent('IMockDependency');
			$this->assertEquals('MockDecorator', get_class($component), 'Kernel did not return a decorated IMockDependency object');
		}

		/**
		 * Injection with a decorator via an interface binding and a preconstructed object
		 */
		public function testDecoratedInterfaceInjectionPreCreated()
		{
			$kernel = new KrameSystem(KW_PRELOAD_CLASSES);
			$kernel->addBinding('IMockDependency', new MockDependency());
			$kernel->addDecorator('IMockDependency', 'MockDecorator');
			$component = $kernel->getComponent('IMockDependency');
			$this->assertEquals('MockDecorator', get_class($component), 'Kernel did not return a decorated IMockDependency object');
		}

		/**
		 * Injection with a decorator via an interface binding and a preconstructed object and decorator
		 */
		public function testDecoratorInterfaceInjectionPreCreated()
		{
			$kernel = new KrameSystem(KW_PRELOAD_CLASSES);
			$dep = new MockDependency();
			$dec = new MockDecorator($dep);
			$kernel->addBinding('IMockDependency', $dep);
			$kernel->addDecorator('IMockDependency', $dec);
			$component = $kernel->getComponent('IMockDependency');
			$this->assertEquals(get_class($component), 'MockDecorator', 'Kernel did not return a decorated IMockDependency object');
			$dep->set('mock');
			$dec->set('+');
			$this->assertEquals('mock+', $component->test(), 'Decorated function call fails');
			$dep->set('MOCK');
			$dec->set('TEST');
			$this->assertEquals('MOCKTEST', $component->test(), 'Decoreted function call fails');
		}

		/**
		 * Test chained decorators
		 */
		public function testDecoratorChain()
		{
			$kernel = new KrameSystem(KW_PRELOAD_CLASSES);
			$dep = new MockDependency();
			$dec1 = new MockDecorator($dep);
			$dec2 = new MockDecorator($dep);
			$kernel->addBinding('IMockDependency', $dep);
			$kernel->addDecorator('IMockDependency', $dec1);
			$kernel->addDecorator('IMockDependency', $dec2);
			$component = $kernel->getComponent('IMockDependency');
			$dep->set('mock');
			$dec1->set(' with ');
			$dec2->set('chain');
			$this->assertEquals('mock with chain', $component->test(), 'Chained decorated function call fails');
		}

		public function testInterfaceAutoBind()
		{
			$kernel = new KrameSystem(KW_AUTOBIND_INTERFACES);
			$kernel->addComponent('MockDependency');
			$component = $kernel->getComponent('IMockDependency');
			$this->assertEquals('MockDependency', get_class($component), 'Kernel returned wrong object type');
		}

		public function testInterfaceAutoBindDuplication()
		{
			$kernel = new KrameSystem(KW_AUTOBIND_INTERFACES);
			$kernel->addBinding('IMockDependency', 'MockDependency');
			$component = $kernel->getComponent('IMockDependency');
			$this->assertEquals('MockDependency', get_class($component), 'Kernel returned wrong object type');
		}

		public function testInterfaceAutoBindWithDecoration()
		{
			$kernel = new KrameSystem(KW_AUTOBIND_INTERFACES);
			$component = new MockDependency();
			$component->set('mock');
			$decorator = new MockDecorator($component);
			$decorator->set('decorator');
			$kernel->addComponent($component);
			$kernel->addDecorator('IMockDependency', $decorator);
			$object = $kernel->getComponent('IMockDependency');
			$this->assertEquals('MockDecorator', get_class($object), 'Kernel returned wrong object type');
			$this->assertEquals('mockdecorator', $object->test(), 'Decorated function call fails');
		}
	}
?>
