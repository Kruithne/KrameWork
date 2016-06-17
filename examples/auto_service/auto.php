<?php
	// Core dependencies
	require_once('../lib/KrameWork/KrameWork/KrameSystem.php');

	$kernel = new KrameSystem(KW_DEFAULT_FLAGS & ~KW_ENABLE_SESSIONS);
	$kernel->addAutoLoadPath(getcwd());
	$kernel->start();

	$service = new AutoService();
	$service->execute();
?>
