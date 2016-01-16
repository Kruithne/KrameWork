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
			$kernel->addComponent('MockDependency');
			$kernel->addBinding('IMockDependency', 'MockDependency');
			$component = $kernel->getComponent('IMockDependency');
			$this->assertEquals(get_class($component), 'MockDependency', 'Kernel did not return an IMockDependency object');
		}

		/**
		 * Basic injection via an interface binding and a preconstructed object
		 */
		public function testInterfaceInjectionPreCreated()
		{
			$kernel = new KrameSystem(KW_PRELOAD_CLASSES);
			$kernel->addComponent(new MockDependency());
			$kernel->addBinding('IMockDependency', 'MockDependency');
			$component = $kernel->getComponent('IMockDependency');
			$this->assertEquals(get_class($component), 'MockDependency', 'Kernel did not return an IMockDependency object');
		}

		/**
		 * Injection with a decorator via an interface binding
		 */
		public function testDecoratedInterfaceInjection()
		{
			$kernel = new KrameSystem(KW_PRELOAD_CLASSES);
			$kernel->addComponent('MockDependency');
			$kernel->addBinding('IMockDependency', 'MockDependency');
			$kernel->addDecorator('IMockDependency', 'MockDecorator');
			$component = $kernel->getComponent('IMockDependency');
			$this->assertEquals(get_class($component), 'MockDecorator', 'Kernel did not return a decorated IMockDependency object');
		}

		/**
		 * Injection with a decorator via an interface binding and a preconstructed object
		 */
		public function testDecoratedInterfaceInjectionPreCreated()
		{
			$kernel = new KrameSystem(KW_PRELOAD_CLASSES);
			$kernel->addComponent(new MockDependency());
			$kernel->addBinding('IMockDependency', 'MockDependency');
			$kernel->addDecorator('IMockDependency', 'MockDecorator');
			$component = $kernel->getComponent('IMockDependency');
			$this->assertEquals(get_class($component), 'MockDecorator', 'Kernel did not return a decorated IMockDependency object');
		}

		/**
		 * Injection with a decorator via an interface binding and a preconstructed object and decorator
		 */
		public function testDecoratorInterfaceInjectionPreCreated()
		{
			$kernel = new KrameSystem(KW_PRELOAD_CLASSES);
			$dep = new MockDependency();
			$dep->set('mock');
			$dec = new MockDecorator(null);
			$kernel->addComponent($dep);
			$kernel->addBinding('IMockDependency', 'MockDependency');
			$kernel->addDecorator('IMockDependency', new MockDecorator(null));
			$component = $kernel->getComponent('IMockDependency');
			$this->assertEquals(get_class($component), 'MockDecorator', 'Kernel did not return a decorated IMockDependency object');
			$this->assertEquals($component->test(), 'mock+', 'Decorated function call fails');
			$dep->set('MOCK');
			$dec->set('TEST');
			$this->assertEquals($component->test(), 'MOCKTEST', 'Decoreted function call fails');
		}
	}
?>
