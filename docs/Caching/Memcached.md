## KrameWork\Caching\Memcached : IDataCache

***Table of Contents***
* **Overview** - Information about the class.
* **Examples** - Usage examples.
* **Functions** - Comprehensive list of all functions in the class.

___
### Overview
The `Memcached` class provided by KrameWork is a basic interface for Memcached which conforms to the `IDataCache` interface, providing compatibility with other caching solutions provided in the same namespace.
> **Note**: Usage of this class requires a Memcached server set-up, running and accessible to the server which is executing your PHP script. Failure to do so will result in demons, lots of demons.

> **Note**: Memcached works with shared memory, meaning data stored within will be accessible to every request, and even other processes; it is not restricted to a specific user like sessions are. **Don't let sensitive data leak!**

> **Note**: Give each app its own prefix to prevent key collisions on a shared server. `flush()` always removes all keys on the server, including keys without the prefix.

___
### Examples
Below is a basic example of how to use the class. For a full overview of provided methods, check the function list at the bottom of the document.
```php
$cache = new Memcached(); // Defaults to 127.0.0.1:11211
$cache = new Memcached('192.168.1.5', 11222); // Non-standard address/port.
$cache = new Memcached('127.0.0.1', 11211, 'myapp:'); // Add 'myapp:' to every key, to keep keys apart from other apps.

$cache->foo = 'bar'; // Store string 'bar' under key 'foo' using magic setter.
print($cache->foo); // Retrieve the same value using magic getter. Prints 'bar'.

$cache->store('foo', 'bar'); // Equivalent to the previous store operation above.
$cache->store('foo', 'bar', 60); // Same again, but value is only stored for 60 seconds.

unset($cache->foo); // Manually unset the 'foo' key.
$cache->flush(); // Delete everything in the cache!
```
___
### Functions
##### > __construct() : `void`
Memcached constructor.

parameter | type | description
--- | --- | ---
`$server` | `string` | Memcached server address.
`$port` | `int` | Memcached server port.
`$prefix` | `string` | Prefix added to every key. Empty for no prefix.

exception | reason
--- | ---
`CacheOperationException` | Prefix is not valid, for example longer than 128 characters.
##### > __get() : `mixed|null`
Obtain a value from the cache. Returns null if the item does not exist. Null is never stored, so it cannot be confused with a stored value.

parameter | type | description
--- | --- | ---
`$key` | `string` | Key of the value.

exception | reason
--- | ---
`CacheOperationException` | Value could not be read from the server.
##### > exists() : `bool`
Check if a value exists in the cache.

parameter | type | description
--- | --- | ---
`$key` | `string` | Key used to store the value.

exception | reason
--- | ---
`CacheOperationException` | Value could not be read from the server.
##### > store() : `void`
Store a value in the cache. Storing `null` removes the key.

parameter | type | description
--- | --- | ---
`$key` | `string` | Key to store the value under.
`$value` | `mixed` | Value to store in the cache.
`$expire` | `int` | 60*60*24*30 >= Unix Timestamp, otherwise seconds. 0 = Never.

exception | reason
--- | ---
`CacheOperationException` | Value could not be stored on the server.
##### > __set() : `void`
Store a value in the cache. Value will not expire, use store() if needed. Storing `null` removes the key.

parameter | type | description
--- | --- | ---
`$key` | `string` | Key to store the value under.
`$value` | `mixed` | Value to store in the cache.

exception | reason
--- | ---
`CacheOperationException` | Value could not be stored on the server.
##### > __unset() : `void`
Remove an item stored in the cache.

parameter | type | description
--- | --- | ---
`$key` | `string` | Key of the item to remove.

exception | reason
--- | ---
`CacheOperationException` | Value could not be removed from the server.
##### > flush() : `void`
Flush the cache, removing all stored data. This removes all keys on the server, including keys without the prefix.

exception | reason
--- | ---
`CacheOperationException` | Cache could not be flushed on the server.
##### > increment() : `int`
Increase an integer value in the cache. A missing value starts at 0. Throws `CacheOperationException` on failure. Returns the new value.

parameter | type | description
--- | --- | ---
`$key` | `string` | Key of the value.
`$weight` | `int` | How much to increment the value.
##### > decrement() : `int`
Decrease an integer value in the cache. A missing value starts at 0. The value does not go below 0. Throws `CacheOperationException` on failure. Returns the new value.

parameter | type | description
--- | --- | ---
`$key` | `string` | Key of the value.
`$weight` | `int` | How much to decrement the value.
