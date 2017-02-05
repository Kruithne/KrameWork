<?php
	require_once('../../KrameWork/KrameSystem.php'); // We need this.
	$sys = new KrameSystem(KW_DEFAULT_FLAGS & ~KW_ENABLE_SESSIONS); // Create a system without sessions.

	// By default, benchmark tests will cycle 2000 times, however we can change
	// this by passing in a new figure in the constructor, like below.
	$test = new ExampleBenchmarkTest(3000);

	// Calling run() on our test will execute the specified amount of cycles before
	// giving us some basic timing statistics about the execution.
	print_r($test->run());
