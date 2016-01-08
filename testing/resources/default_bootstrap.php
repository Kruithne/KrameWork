<?php
	require_once("/home/travis/build/Kruithne/KrameWork/KrameWork/KrameSystem.php");
	date_default_timezone_set("Europe/London");

	KW_ClassLoader::addClassPath("/home/travis/build/Kruithne/KrameWork/testing/");

	$sys = new KrameSystem();
?>