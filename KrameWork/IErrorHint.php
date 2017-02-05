<?php
	interface IErrorHint
	{
		/**
		 * Return a label identifying what the hint means
		 *
		 * @return string A label for the error hint
		 */
		public function getErrorHintLabel();

		/**
		 * Return some data to be included in the error report
		 *
		 * @return string Information to aid with debugging an error
		 */
		public function getErrorHint();
	}
