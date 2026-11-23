## KrameWork\Database\Schema\ManagedTable

***Table of Contents***
* **Overview** - Information about the class.
* **Examples** - Usage examples.
* **Functions** - Comprehensive list of all functions in the class.

___
### Overview
A base definition of a table that is managed by a schema manager.
Specific dialects are subtypes of this class, such as `MSSQLManagedTable` for Microsoft SQL Server.

___
### Examples
```php
class MyTable extends MSSQLManagedTable
{
    public function getName()
    {
        return "mytable";
    }

    public function versionLog()
    {
        return [
            new TableVersion(
                1,
                ['CREATE TABLE [dbo].[MyTable] ([column] VARCHAR(50) NOT NULL)']
            )
        ];
    }

    public function latestVersion()
    {
        return ['CREATE TABLE [dbo].[MyTable] ([column] VARCHAR(50) NOT NULL)'];
    }
}
```

___
### Functions

##### > getSchema() : `string`
Returns the name of the schema.

##### > getName() : `string`
Returns the name of the table, without schema.

##### > getFullName() : `string`
Returns the fully qualified name of the table, ie. `schema.table`

##### > versionLog() : `TableVersion[]`
Returns an array of TableVersion definition
Used by the schema manager for updating the database.

##### > latestVersion() : `string[]`
Returns a set of queries to create the latest revision of the table.
Used by the schema manager to initialize a clean table.
That is, during initial setup of the schema on a an empty database, or a new table.

##### > update() : `string`
Called by the schema manager to perform an update.
Each missing revision runs in its own transaction. If a revision fails, that revision rolls back and the earlier revisions stay applied.
Returns a status message passed on to the caller.

##### > drop() : `void`
Delete the table from the database

##### > create() : `string`
Creates the current version of the table in the database.
All statements from `latestVersion()` run in one transaction. If a statement fails, all of them roll back and no version is stored.
Returns a status message passed on to the caller.

___
### Rollback on MySQL
MySQL and MariaDB commit each table change (for example `CREATE TABLE` or `ALTER TABLE`) immediately. A failed revision or table creation cannot roll back these changes. Put each table change in its own revision, so a failure leaves the table at a known revision.