<?php
	interface IAccessControl
	{
		/**
		 * Check if the user object has a certain permission
		 * @param string $what A string known to the ACL system
		 * @param IDataContainer $who A user
		 * @return bool
		 */
		public function isMemberOf($what, $who);
	}
