<?php
	namespace KrameWork\Users\Schema;

	/**
	 * Enum DatabaseType
	 * Type of database that contains the user tables.
	 *
	 * @package KrameWork\Users\Schema
	 */
	enum DatabaseType
	{
		case MYSQL;
		case POSTGRESQL;
		case SQLITE;
		case MSSQL;
	}
