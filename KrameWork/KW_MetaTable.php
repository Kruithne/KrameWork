<?php
	class KW_MetaTable extends KW_Repository
	{
		public function prepare()
		{
			switch($this->db->getType())
			{
				case 'sqlite':
					$this->exists = $this->db->prepare('SELECT name FROM sqlite_master WHERE type=\'table\' AND name=\'_metatable\'');
					$this->load = $this->db->prepare('SELECT * FROM _metatable');
					$this->save = $this->db->prepare('
INSERT OR IGNORE INTO _metatable ("table", "version") VALUES (:table, :version);
UPDATE _metatable SET "version"=:version WHERE "table"=:table');
					break;
				case 'pgsql':
					$this->exists = $this->db->prepare('
SELECT c.relname 
FROM   pg_catalog.pg_class c
JOIN   pg_catalog.pg_namespace n ON n.oid = c.relnamespace
WHERE  n.nspname = \'public\'
AND    c.relname = \'_metatable\'
');
					$this->load = $this->db->prepare('SELECT * FROM _metatable');
					$this->save = $this->db->prepare('UPDATE _metatable SET "version"=:version WHERE "table"=:table');
					$this->create = $this->db->prepare('
INSERT INTO _metatable ("table","version")
SELECT _table, 0
FROM (
	SELECT CAST(:table AS VARCHAR) AS _table
) AS i
LEFT JOIN _metatable ON (_metatable."table" = i._table)
WHERE _metatable."table" IS NULL
');
					break;
				case 'mysql':
					$this->exists = $this->db->prepare('SHOW TABLES LIKE \'_metatable\'');
					$this->load = $this->db->prepare('SELECT * FROM `_metatable`');
					$this->save = $this->db->prepare('
INSERT INTO `_metatable` (`table`,`version`) VALUES (:table,:version)
	ON DUPLICATE KEY UPDATE `version`=VALUES(`version`)
');
					break;
				case 'dblib':
					$this->exists = $this->db->prepare('SELECT [TABLE_NAME] FROM INFORMATION_SCHEMA.TABLES WHERE TABLE_SCHEMA=\'dbo\' AND TABLE_NAME=\'_metatable\'');
					$this->load = $this->db->prepare('SELECT * FROM [_metatable]');
					$this->save = $this->db->prepare('UPDATE [_metatable] SET [version]=:version WHERE [table]=:table');
					$this->create = $this->db->prepare('
INSERT INTO dbo.[_metatable] ([table],[version])
SELECT :table, 0
WHERE NOT EXISTS (SELECT * FROM [_metatable] WHERE [table]=:table)
');
					break;
				default:
					trigger_error('The database driver "'.$this->db->getType().'" is not yet supported by SchemaManager, sorry!', E_USER_ERROR);
			}
		}

		public function getName()
		{
			return '_metatable';
		}

		public function getVersion()
		{
			return 1;
		}

		public function getQueries()
		{
			switch($this->db->getType())
			{
				case 'sqlite':
					return array(
						1 => array('CREATE TABLE _metatable ("table" TEXT PRIMARY KEY, "version" INT)')
					);
				case 'pgsql':
					return array(
						1 => array('
CREATE TABLE _metatable (
	"table" VARCHAR(50),
	"version" INTEGER,
	PRIMARY KEY("table")
)'
						)
					);
					break;
				case 'dblib':
					return [
						1 => ['
IF (NOT EXISTS (SELECT * FROM INFORMATION_SCHEMA.TABLES WHERE TABLE_NAME = \'_metatable\'))
BEGIN
	CREATE TABLE dbo.[_metatable] (
		[table] VARCHAR(50) NOT NULL,
		[version] INT NOT NULL
	)
END']
					];
				default:
					return array(
						1 => array('
CREATE TABLE `_metatable` (
	`table` VARCHAR(50),
	`version` INTEGER,
	PRIMARY KEY(`table`)
)'
						)
					);
			}
		}
	}
?>
