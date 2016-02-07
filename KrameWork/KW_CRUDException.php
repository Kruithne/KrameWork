<?php
	class KW_CRUDException extends KW_Exception
	{
		/**
		 * Crafts a new create, read, update, delete operation exception.
		 *
		 * @param string $message Exception message.
		 */
		public function __construct($message)
		{
			parent::__construct($message);
		}
	}
?>
