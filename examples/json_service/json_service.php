<?php
	// This is a JSON based service endpoint
	require_once('../../KrameWork/KrameSystem.php'); // We need this.

	$system = new KrameSystem(KW_DEFAULT_FLAGS & ~KW_ENABLE_SESSIONS); // Create a system without sessions.
	$system->addAutoLoadPath(getcwd()); // Auto-load from the current directory.

	$service = new SimpleService(); // Expose service to clients
	$service->execute();
