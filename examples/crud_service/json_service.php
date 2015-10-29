<?php
	// This is a JSON based service endpoint
	require_once('../../KrameWork/KrameSystem.php'); // We need this.

	$system = new KrameSystem(KW_DEFAULT_FLAGS & ~KW_ENABLE_SESSIONS); // Create a system without sessions.
	$system->addAutoLoadPath(getcwd()); // Auto-load from the current directory.

	$system->addComponent(
		new KW_DatabaseConnection('pgsql:dbname=test;host=localhost', 'test', 'password')
	);
	$system->addComponent('KW_SchemaManager');
	$system->addComponent('SimpleService');

	$system->getComponent('SimpleService')->execute(); // Expose service to clients
?>
