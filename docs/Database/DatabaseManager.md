## KrameWork\Database\DatabaseManager

***Table of Contents***
* **Overview** - Information about the class.
* **Examples** - Usage examples.
* **Functions** - Comprehensive list of all functions in the class.

___
### Overview
This interface defines the necessary functions to implement a schema manager

___
### Examples
Define one or more classes extending ManagedTable and add them to an instance of DatabaseManager.

```php
require('KrameWork/src/Database/Database.php');
require('KrameWork/src/Database/Schema/MSSQLMetaTable.php');
require('KrameWork/src/Database/Schema/DatabaseManager.php');
require('KrameWork/src/Database/Schema/MSSQLManagedTable.php');

use KrameWork\Database\ConnectionString;
use KrameWork\Database\Database;
use KrameWork\Database\Schema\MSSQLManagedTable;
use KrameWork\Database\Schema\MSSQLMetaTable;
use KrameWork\Database\Schema\DatabaseManager;

$username = 'domain\user';
$password = 'password';
$dsn = new ConnectionString('dblib:version=7.0;charset=UTF-8;host=127.0.0.1;dbname=example', $username, $password);
$db = new Database($dsn, Database::DB_DRIVER_PDO);
$dbm = new DatabaseManager();
$meta = new MSSQLMetaTable($db);
$dbm->MyTable = new MyTable($db, $meta);
$status = $dbm->updateSchema();
var_dump($status);
```

The meta table stores the current revision of each managed table, in a table named `_tables`. It creates `_tables` on first use. Use the meta table and the managed table base class that match your database:

database | meta table | managed table base class | notes
--- | --- | --- | ---
Microsoft SQL Server | `MSSQLMetaTable` | `MSSQLManagedTable` | Schema `dbo`
MySQL / MariaDB | `MySQLMetaTable` | `MySQLManagedTable` | Uses the current database
PostgreSQL | `PostgreSQLMetaTable` | `PostgreSQLManagedTable` | Schema `public`, PostgreSQL 9.5 or later
SQLite | `SQLiteMetaTable` | `SQLiteManagedTable` | Schema `main`, SQLite 3.24 or later

For example, on SQLite:
```php
$db = new Database(new ConnectionString('sqlite:example.db'), Database::DB_DRIVER_PDO);
$meta = new SQLiteMetaTable($db);
$dbm->MyTable = new MyTable($db, $meta);
```
___
### Functions
##### > __set() : `void`
Add a managed table to the schema manager

parameter | type | description
--- | --- | ---
`$name` | `string` | 
`$table` | `IManagedTable` | 

##### > __get() : `IManagedTable`
Fetch a managed table from the schema manager

parameter | type | description
--- | --- | ---
`$name` | `string` | 

##### > updateSchema() : `array`
Update the database schema according to managed tables

