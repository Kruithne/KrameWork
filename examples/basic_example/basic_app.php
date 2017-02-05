<?php
	require_once('../../KrameWork/KrameSystem.php'); // We need this.

	$system = new KrameSystem(); // Create a system object, how sweet!
	$system->addAutoLoadPath(getcwd()); // Auto-load from the current directory.

	new BasicTestObject(); // Construct an object using an automatically loaded class.
