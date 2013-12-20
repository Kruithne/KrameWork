### Setting flags.

The *KrameSystem* constructor takes a bitwise flag which can be manually provided to change the behavior from what KrameWork defaults with. The flags are detailed below.

* KW_ENABLE_SESSIONS: Automatically sets up a session when KrameWork is initialized. [Default]
* KW_ERROR_HANDLER: Enables the KrameWork error handler, see [error handling](docs/error_handling.md). [Default]
* KW_LEAVE_ERROR_LEVEL: Stops the error handler module from changing the runtime error reporting level.