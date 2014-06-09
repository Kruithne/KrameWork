<?php
	require_once('../../KrameWork/KrameSystem.php'); // We need this.

	$system = new KrameSystem(KW_DEFAULT_FLAGS & ~KW_ENABLE_SESSIONS); // Create a system without sessions.

	$value = REST::Get('test');

	if ($value === null)
		print('No test value was given, add &test=bob@nowhere.net to the end of this page URL.');
	else if ($value === false)
		print('The test value given was NOT a e-mail address.');
	else
		print('The test value given was a valid e-mail address!');
?>