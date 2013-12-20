### Error Handling

The error handling module of KrameWork is enabled by default. If you are using customized flags with the system constructor you will need to add the *KW_ERROR_HANDLER* flag or this module will not be loaded. The error handler is responsible for the following things.

* Setting the error level to report all errors (Can be disabled, see flags
* Collecting non-fatal