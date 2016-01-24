<?php
	// Compile time config for the object graph

	require_once('../../KrameWork/BootstrapUtils.php');
	require_once('../../KrameWork/KrameSystem.php');

	$kernel = new KrameSystem(0);
	echo $kernel->makeBootstrap([
		new Library('example'),
		new Binding('ICacheState','KW_CacheStateTable'),
		new Binding('ISchemaManager', 'KW_SchemaManager'),
		new Decorator('IService','ServiceLogger'),
		'KW_Template'
	]);
?>
