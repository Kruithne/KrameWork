<?php
	class MockErrorHandler implements IErrorHandler
	{
		public function addEmailOutputRecipient($recipient) {}
		public function getMailObject() {}
		public function debugMode() {}
		public function debugJSON() {}
		public function setOutputLog($log) {}
		public function handleError($type, $string, $file, $line) {}
		public function handleException($exception) {}
		public function reportException($exception) {}
		public function sendErrorReport($report) {}
	}
