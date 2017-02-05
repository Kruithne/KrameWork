<?php
	require_once('../../KrameWork/KrameSystem.php'); // We need this.

	$system = new KrameSystem(KW_DEFAULT_FLAGS & ~KW_ENABLE_SESSIONS); // Create a system without sessions.

	$system->addAutoLoadPath(getcwd()); // Include the current directory.

	$system->addComponent('TestDatabaseConnection'); // Add our custom database connection.
	$system->addComponent('RandomClass'); // Add our random class which uses the database connection.

	// Call the object so it gets constructed.
	$random_class = $system->getComponent('RandomClass');
