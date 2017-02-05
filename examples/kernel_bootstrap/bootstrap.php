<?php
	define('KW_TEMPLATE_DIR', 'view/');
	require_once('../../KrameWork/KrameSystem.php');
	require_once('kernel.php');
	$kernel->getErrorHandler()->addEmailOutputRecipient('admin@eaxmple.com');

	// Database access
	$kernel->addComponent(new KW_DatabaseConnection('pgsql:dbname=db;host=example.com','user','password'));
	$kernel->addBinding('IDatabaseConnection', 'KW_DatabaseConnection');
	$schema = $kernel->getComponent('KW_SchemaManager');

	$kernel->start();
