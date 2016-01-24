<?php
	require_once('../../KrameWork/KW_ErrorHandler.php');
	require_once('../../KrameWork/KW_ErrorReport.php');
	require_once('../../KrameWork/KW_DatabaseConnection.php');
	KW_DatabaseConnection::$trace = true;
	KW_ErrorHandler::$startup = microtime(true);
	KW_ErrorHandler::$slowWarn = 0.6;
	KW_ErrorHandler::$errorDocument = file_get_contents('error.html');
	ob_start(array('KW_ErrorHandler','errorCatcher'));
?>
