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
		 * Injection with a decorator via an interface binding
		 */
		public function testInterfaceInjection()
		{
			$kernel = new KrameSystem(KW_PRELOAD_CLASSES);
			$kernel->addComponent('MockDependency');
			$kernel->addBinding('IMockDependency', 'MockDependency');
			$kernel->addDecorator('IMockDependency', 'MockDecorator');
			$component = $kernel->getComponent('IMockDependency');
			$this->assertEquals(get_class($component), 'MockDecorator', 'Kernel did not return a decorated IMockDependency object');
		}
	}
?>
