<?php
	interface IStartup
	{
		/**
		 * Automatically gets called on kernel start
		 */
		public function start();
	}
