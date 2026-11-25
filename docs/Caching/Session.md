## KrameWork\Caching\Session : IDataCache

***Table of Contents***
* **Overview** - Information about the class.
* **Functions** - Comprehensive list of all functions in the class.

___
### Overview
The `Session` class provides an `IDataCache` compatible interface to the request session.
___
### Functions
##### > __construct() : `void`
Session constructor.

parameter | type | description
--- | --- | ---
`$autoStart` | `bool` | Start session on instantiation.


exception | reason
--- | ---
`SessionInitiationException` | `$autoStart` is true and the session could not be started.
##### > start() : `void`
Attempt to start a session.


exception | reason
--- | ---
`SessionInitiationException` | Session could not be started, for example in a CLI environment or after headers are sent.
##### > flush() : `void`
Delete all existing data in the session and start a newly generated one.


exception | reason
--- | ---
`SessionInitiationException` | Session could not be cleared and started again.
##### > isActive() : `bool`
Check if a session is currently active.

##### > __get() : `mixed`
Retrieve a session value. Null is never stored, so it cannot be confused with a stored value.

parameter | type | description
--- | --- | ---
`string` | `$name` | Key to retrieve from the session.

##### > __set() : `void`
Set a session value. Storing `null` removes the key.

parameter | type | description
--- | --- | ---
`string` | `$name` | Key to set the value with.
`mixed` | `$value` | Value to store in the session.

##### > __unset() : `void`
Unset a session value.

parameter | type | description
--- | --- | ---
`string` | `$name` | Key to remove from the session.

##### > store() : `void`
Store a value in the cache. Storing `null` removes the key.

parameter | type | description
--- | --- | ---
`$key` | `string` | Key to store the value under.
`$value` | `mixed` | Value to store in the cache.
`$expire` | `int` | No effect for this implementation.

##### > increment() : `int|float`
Increase a numeric value in the cache. A missing or non-numeric value starts at 0. Returns the new value.

parameter | type | description
--- | --- | ---
`$key` | `string` | Key of the value.
`$weight` | `int` | How much to increment the value.

##### > decrement() : `int|float`
Decrease a numeric value in the cache. A missing or non-numeric value starts at 0. Returns the new value.

parameter | type | description
--- | --- | ---
`$key` | `string` | Key of the value.
`$weight` | `int` | How much to decrement the value.