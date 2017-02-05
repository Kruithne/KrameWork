<?php
	// Compile time config for the object graph

	require_once('../../KrameWork/BootstrapUtils.php');
	require_once('../../KrameWork/KrameSystem.php');

	$system = new KrameSystem(); // Enable autoloading

	interface IService {}
	class AppleService implements IService {}
	class OrangeService implements IService {}
	class PearService implements IService {}

	$kernel = new KrameSystem(0);
	echo $kernel->makeBootstrap([
		new Library('example'),
		new Binding('ICacheState','KW_CacheStateTable'),
		'KW_SchemaManager',
		new Binding('IService', 'AppleService'),
		new Binding('IService', 'OrangeService'),
		'PearService',
		new Decorator('IService','ServiceLogger'),
		'KW_Template'
	]);
