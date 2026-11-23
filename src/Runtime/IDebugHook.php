<?php
	namespace KrameWork\Runtime;

	interface IDebugHook
	{
		/**
		 * Method called by the error handler when an error occurs, in order to provide the developer with internal
		 * application data to facilitate debugging the application.
		 * @api getDebug
		 * @return array<string, mixed> Key/Value pairs to add to an error report
		 */
		function getDebug(): array;
	}