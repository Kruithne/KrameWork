<?php
	interface IErrorHint
	{
		/**
		 * Return some data to be included in the error report
		 *
		 * @return string Information to aid with debugging an error
		 */
		public function getErrorHint();
	}
?>
