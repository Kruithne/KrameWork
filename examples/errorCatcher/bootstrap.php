<?php
	// This serves as the entry point for the application.
	// $kernel gets magically injected by prepend_file here
	$kernel->addAutoLoadPath(getcwd()); // Auto-load from the current directory.
	$kernel->getErrorHandler()->addEmailOutputRecipient('someone@example.com');
