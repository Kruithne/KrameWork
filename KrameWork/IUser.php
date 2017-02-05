<?php
	interface IUser extends IDataContainer
	{
		/**
		 * Check if the user has a certain permission
		 * @param string $what A string known to the ACL system
		 * @return bool
		 */
		public function isMemberOf($what);
	}
