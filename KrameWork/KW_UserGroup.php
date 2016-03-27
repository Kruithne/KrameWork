<?php
	abstract class KW_UserGroup extends KW_CacheAwareCRUDService implements IAccessControl
	{
		public function getName() { return 'usergroup'; }

		/**
		 * KW_UserGroup constructor.
		 * @param ISchemaManager $schema
		 */
		public function __construct(ISchemaManager $schema)
		{
			parent::__construct($schema);
		}

		public function isMemberOf($what, $who)
		{
			$group = $this->read($who->id);
			return $group && $group->groupname == $what;
		}

		/**
		 * Get the name of the administrators group
		 * @return string Name of administrators group
		 */
		public abstract function getAdminGroup();

		public function canCreate($object)
		{
			return $this->isMemberOf($this->getAdminGroup(), $this->user);
		}

		public function canRead()
		{
			return true;
		}

		public function canUpdate($object)
		{
			return $this->isMemberOf($this->getAdminGroup(), $this->user);
		}

		public function canDelete($object)
		{
			return $this->isMemberOf($this->getAdminGroup(), $this->user);
		}

		public function getVersion()
		{
			return 1;
		}

		public function getKey() { return 'uid'; }
		public function hasAutoKey() { return false; }
		public function getValues() { return ['groupname']; }


		public function getQueries()
		{
			$table = $this->getName();
			return array(
				1 => array('
					CREATE TABLE '. $table. ' (
						uid INTEGER NOT NULL,
						groupname VARCHAR(100) NOT NULL,
						PRIMARY KEY (uid)
					)'
				)
			);
		}

		private $acl;
	}
?>
