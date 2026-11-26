## KrameWork\Users\UserSessionManager

***Table of Contents***
* **Overview** - Information about the class.
* **Examples** - Usage examples.
* **Functions** - Comprehensive list of all functions in the class.

___
### Overview
`UserSessionManager` creates, finds and ends user sessions. It keeps sessions in the `user_sessions` table. See [UserTables](UserTables.md).

A cookie identifies the session of the current request. The cookie is `HttpOnly` and `SameSite=Lax`. It is `Secure` when the request uses HTTPS.

`create()`, `get()`, `end()` and `endAll()` do not use cookies. `start()`, `getCurrent()` and `endCurrent()` use the session cookie.

A session does not expire on the server. To end it, call `end()`, `endCurrent()` or `endAll()`.
___
### Examples
Start a session after a login:
```php
$sessions = new UserSessionManager($db);
$sessions->start($userID);
```

Get the session of the current request:
```php
$session = $sessions->getCurrent();
if ($session === null)
	return 401;
```

Log out:
```php
$sessions->endCurrent();
```

End all sessions of an account, for example after a password reset:
```php
$sessions->endAll($userID);
```
___
### Functions
##### > __construct() : `void`
UserSessionManager constructor.

parameter | type | description
--- | --- | ---
`$db` | `Generic` | Database that contains the user tables.
`$cookieName` | `string` | Name of the session cookie. **Default: `session_id`**.
`$lifetime` | `int` | Lifetime of the session cookie in seconds. **Default: 31536000 (one year)**.
##### > create() : `UserSession`
Create a session for an account. This does not set a cookie.

parameter | type | description
--- | --- | ---
`$userID` | `int` | ID of the user account.

exception | reason
--- | ---
`UnknownUserException` | No account has the ID.
##### > start() : `UserSession`
Create a session for an account and set the session cookie. Call it before output starts.

parameter | type | description
--- | --- | ---
`$userID` | `int` | ID of the user account.

exception | reason
--- | ---
`UnknownUserException` | No account has the ID.
##### > get() : `UserSession|null`
Get a session by ID. Returns null if the session does not exist or the account has the `ACCOUNT_DISABLED` flag.

parameter | type | description
--- | --- | ---
`$sessionID` | `string` | ID of the session.
##### > getCurrent() : `UserSession|null`
Get the session of the current request from the session cookie. Returns null if the request has no valid session or the account has the `ACCOUNT_DISABLED` flag.
##### > end() : `void`
End a session. This does not remove a cookie.

parameter | type | description
--- | --- | ---
`$sessionID` | `string` | ID of the session.
##### > endCurrent() : `void`
End the session of the current request and remove the session cookie.
##### > endAll() : `void`
End all sessions of an account.

parameter | type | description
--- | --- | ---
`$userID` | `int` | ID of the user account.
##### > refresh() : `void`
Set the `updated` time of all sessions of the given accounts to now. Use this to tell clients that the account data changed.

parameter | type | description
--- | --- | ---
`...$userIDs` | `int` | IDs of the user accounts.
