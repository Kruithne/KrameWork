<?php
	require_once('../../KrameWork/KrameSystem.php'); // We need this.

	$system = new KrameSystem(KW_DEFAULT_FLAGS & ~KW_ENABLE_SESSIONS); // Create a system without sessions.

	// Here we set all error reports to be e-mailed.
	$system->getErrorHandler()->addEmailOutputRecipient('kruithne@gmail.com');

	// Throw an error to test.
	trigger_error('This is a random error that might occur', E_USER_WARNING);
?>