<?php
	require_once('../../KrameWork/KrameSystem.php'); // We need this.

	$system = new KrameSystem(KW_DEFAULT_FLAGS & ~KW_ENABLE_SESSIONS); // Create a system without sessions.

	$system->getErrorHandler()->addEmailOutputRecipient('kruithne@gmail.com');

	// Start a new database connection.
	$database = new KW_DatabaseConnection('sqlite:test_database.sq3', null, null);

	// Create an SQL statement and execute it.
	$statement = $database->prepareStatement("SELECT test_column FROM test_table");
	$statement->execute();

	// Loop every row we have and output the value of test_column.
	foreach ($statement->getRows() as $row)
		echo $row->test_column . HTML_EOL;
?>