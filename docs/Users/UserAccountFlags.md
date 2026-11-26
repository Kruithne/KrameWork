## KrameWork\Users\UserAccountFlags

***Table of Contents***
* **Overview** - Information about the class.

___
### Overview
`UserAccountFlags` contains the bit flags for the `flags` column of a user account. Use `UserManager::setFlag()` to set or clear a flag.

constant | value | description
--- | --- | ---
`NONE` | 0 | No flags.
`ACCOUNT_DISABLED` | 1 | The account cannot log in, has no valid sessions and has no permissions.
`REQUIRES_VERIFICATION` | 2 | The account must be verified with a code before it can log in.
`FORCE_PASSWORD_RESET` | 4 | The account must reset its password before it can log in.
`ALL_PERMISSIONS` | 8 | The account has all permissions.
`OAUTH_ACCOUNT` | 16 | The account logs in with OAuth and has no password.

KrameWork uses bits 0 to 4. Applications can use bits 5 to 30 for their own flags.
