<?php
	require_once('../../KrameWork/KrameSystem.php');
	$kernel = new KrameSystem(KW_ERROR_HANDLER);
	$err = $kernel->getErrorHandler();
	KW_DatabaseConnection::$trace = true;
	$err->startup = microtime(true);
	$err->slowWarn = 0.6;
	$err->errorDocument = file_get_contents('error.html');
	ob_start(array($err,'errorCatcher'));
?>
