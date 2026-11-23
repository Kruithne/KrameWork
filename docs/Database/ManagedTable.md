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
    public function getName(): string
    {
        return "mytable";
    }

    public function versionLog(): array
    {
        return [
            new TableVersion(1, ['CREATE TABLE [dbo].[MyTable] ([column] VARCHAR(50) NOT NULL)']),
            new TableVersion(2, ['ALTER TABLE [dbo].[MyTable] ADD [text] VARCHAR(50) NULL'])
        ];
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
Used by the schema manager to create and update the table. The first version creates the table, and each later version changes it.
The versions must be 1, 2, 3 and so on, in order, with no gaps. If not, `update()` and `create()` return an error status and change nothing. Only add new versions to the end of the log.

##### > update() : `string`
Called by the schema manager to perform an update.
If the table does not exist, all revisions run, starting at version 1. If the table exists, only the missing revisions run. Each revision runs in its own transaction. If a revision fails, that revision rolls back and the earlier revisions stay applied.
Returns a status message passed on to the caller.

##### > drop() : `void`
Delete the table from the database

##### > create() : `string`
Creates the table in the database by applying all revisions, starting at version 1.
Each revision runs in its own transaction. If a revision fails, that revision rolls back and the earlier revisions stay applied.
Returns a status message passed on to the caller.

___
### Rollback on MySQL
MySQL and MariaDB commit each table change (for example `CREATE TABLE` or `ALTER TABLE`) immediately. A failed revision or table creation cannot roll back these changes. Put each table change in its own revision, so a failure leaves the table at a known revision.