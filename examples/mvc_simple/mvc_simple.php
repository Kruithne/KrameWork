<?php
	// This serves as the entry point for the application.
	require_once('../../KrameWork/KrameSystem.php'); // We need this.

	$system = new KrameSystem(KW_DEFAULT_FLAGS & ~KW_ENABLE_SESSIONS); // Create a system without sessions.
	$system->addAutoLoadPath(getcwd()); // Auto-load from the current directory.

	echo new SimpleModule(); // Render our simple module example.
