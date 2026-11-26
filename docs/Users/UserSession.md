## KrameWork\Users\UserSession

***Table of Contents***
* **Overview** - Information about the class.
* **Functions** - Comprehensive list of all functions in the class.

___
### Overview
`UserSession` is a logged-in session of a user account. [UserSessionManager](UserSessionManager.md) returns it.

property | type | description
--- | --- | ---
`sessionID` | `string` | ID of the session.
`userID` | `int` | ID of the user account.
`flags` | `int` | `UserAccountFlags` of the account when the session was read.
`updated` | `int` | Unix time of the last refresh of the session.

> **Note**: `UserSessionManager` does not return a session of an account with the `ACCOUNT_DISABLED` flag. It does not check the other flags.
___
### Functions
##### > __construct() : `void`
UserSession constructor.

parameter | type | description
--- | --- | ---
`$sessionID` | `string` | ID of the session.
`$userID` | `int` | ID of the user account.
`$flags` | `int` | Flags of the user account.
`$updated` | `int` | Unix time of the last refresh of the session.
##### > hasFlag() : `bool`
Check if the user account has a flag set.

parameter | type | description
--- | --- | ---
`$flag` | `int` | One of the `UserAccountFlags` constants.
