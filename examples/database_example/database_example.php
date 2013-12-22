<?php
	require_once('../../KrameWork/KrameSystem.php'); // We need this.

	$system = new KrameSystem(KW_DEFAULT_FLAGS & ~KW_ENABLE_SESSIONS); // Create a system without sessions.

	// Start a new database connection.
	$db = new KW_DatabaseConnection('sqlite:test_database.sq3', null, null);

	// Simple function to make repeating the query easy!
	function getTestRows()
	{
		global $db;

		// Create an SQL statement and execute it.
		$statement = $db->prepare("SELECT test_column FROM test_table");
		$statement->execute();

		return $statement->getRows();
	}


	// Get every row and reverse the value.
	foreach (getTestRows() as $row)
	{
		// Reverse the string (makes for a repeatable example)
		$row->test_column = $row->test_column == 'Hello, world!' ? 'World, hello!' : 'Hello, world!';

		// Prepare a query to persist this change.
		$update = $db->prepare('UPDATE test_table SET test_column = :test_column');

		// Pack the statement with values from our row object and execute the statement.
		$update->copyValuesFromRow($row)->execute();
	}

	// Loop every row we have and output the value of test_column.
	foreach (getTestRows() as $row)
		echo $row->test_column . HTML_EOL;

	// The output of this script will revert between 'Hello, world!' and 'World, hello!' each time it's run.
?>