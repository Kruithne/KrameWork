<?php
	define("TRAVIS_EXEC_PATH", "/home/travis/build/Kruithne/");

	require_once(TRAVIS_EXEC_PATH . "KrameWork/KrameWork/KrameSystem.php");
	date_default_timezone_set("Europe/London");

	KW_ClassLoader::addClassPath(TRAVIS_EXEC_PATH . "KrameWork/testing/");
	KW_ClassLoader::addClassPath(TRAVIS_EXEC_PATH . "KrameWork/testing/resources/");

	$sys = new KrameSystem(KW_DEFAULT_FLAGS & KW_ANY_ERROR);
?>
