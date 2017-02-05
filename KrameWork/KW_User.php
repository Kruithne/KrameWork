<?php
	class KW_User extends KW_DataContainer implements IUser
	{
		public function __construct(IAccessControl $acl, $data)
		{
			$this->acl = $acl;
			parent::__construct($data);
		}

		/**
		 * Check if the user has a certain permission
		 * @param string $what A string known to the ACL system
		 * @return bool
		 */
		public function isMemberOf($what)
		{
			return $this->acl->isMemberOf($what, $this);
		}

		private $acl;
	}
