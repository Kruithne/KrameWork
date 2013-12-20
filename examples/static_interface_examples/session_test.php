<?php
	require_once('../KrameWork/KrameSystem.php'); // We need this.

	$system = new KrameSystem(); // Create a system object, how sweet!

	Session::Set('test', 'Hello, world!'); // Store a string in the session.
	var_dump(Session::Get('test')); // Print the value.

	print(HTML_EOL);

	Session::Delete('test'); // Delete the value we just stored.
	var_dump(Session::Get('test')); // Try printing the value, will return null.
?>