## KrameWork\Users\Schema

***Table of Contents***
* **Overview** - Information about the tables.
* **Examples** - Usage examples.

___
### Overview
The user system keeps its data in managed tables. Each table is a `ManagedTable`. See [ManagedTable](../Database/ManagedTable.md).

The tables use portable SQL, unquoted names and the default schema of the connection. They work on MySQL, PostgreSQL, SQLite and SQL Server. Use the meta table class for your database, for example `MySQLMetaTable`.

`UserAccountsTable` needs a `DatabaseType` (`MYSQL`, `POSTGRESQL`, `SQLITE` or `MSSQL`). The type sets the SQL for the auto-increment `id` column. The other tables do not need a type.

User IDs are `BIGINT` values (`INTEGER` on SQLite). The managers read the ID of a new account with `getLastInsertID()`.

class | table | used by
--- | --- | ---
`UserAccountsTable` | `user_accounts` | `UserManager`, `UserSessionManager`, `OAuthManager`
`UserSessionsTable` | `user_sessions` | `UserSessionManager`
`UserVerificationCodesTable` | `user_verification_codes` | `UserManager`
`UserResetTokensTable` | `user_reset_tokens` | `UserManager`
`UserPermissionsTable` | `user_permissions` | `UserManager`
`OAuthAccountsTable` | `oauth_accounts` | `OAuthManager`
`OAuthStatesTable` | `oauth_states` | `OAuthManager`

All times are Unix times in seconds.

> **Note**: Do not add revisions to these tables. A later KrameWork version can add revisions, and the version numbers would conflict. Keep application data in application tables with a `user_id` column.
___
### Examples
```php
$meta = new MySQLMetaTable($db);
$manager = new DatabaseManager();

$manager->userAccounts = new UserAccountsTable($db, $meta, DatabaseType::MYSQL);
$manager->userSessions = new UserSessionsTable($db, $meta);
$manager->userVerificationCodes = new UserVerificationCodesTable($db, $meta);
$manager->userResetTokens = new UserResetTokensTable($db, $meta);
$manager->userPermissions = new UserPermissionsTable($db, $meta);
$manager->oauthAccounts = new OAuthAccountsTable($db, $meta);
$manager->oauthStates = new OAuthStatesTable($db, $meta);

$manager->updateSchema();
```
