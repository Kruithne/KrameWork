## KrameWork\Runtime\ErrorDispatchers\GitHubDispatcher

***Table of Contents***
* **Overview** - Information about the class.
* **Example** - Example usage.
* **Functions** - Comprehensive list of all functions in the class.

___
### Overview
The `GitHubDispatcher` class is an implementation of `IErrorDispatcher` for use with the `ErrorHandler` class. If using this class as the error dispatcher, reports will be raised as issues in a GitHub repository.

Authentication uses a GitHub personal access token. A fine-grained token with only the **Issues: Read and write** permission on the target repository is recommended.

By default, the issue title is built from the error with no dynamic data, in the format `{prefix}: {name} in {file}:{line}`, for example `EXCEPTION: PDOException in Database.php:120`. The error message is not included in the title, it is included in the issue body. A callable can be provided to generate the title instead.

To prevent duplicate issues, the dispatcher throttles reports by title. If a report with the same title was raised within the throttle window (default `86400` seconds), the report is not sent. Throttle data is stored in a file in `sys_get_temp_dir()` by default. An `IDataCache` instance (such as `APCu` or `Memcached`) can be provided to store throttle data instead. A throttle of `0` disables throttling.

The issue title and body are always sanitized with `ErrorHandler::sanitize()`, which replaces e-mail addresses and IP addresses.

The dispatcher is intended for use with `MarkdownErrorFormatter`. Reports from other formatters are wrapped in a code block. Reports longer than `65536` characters are truncated.

Errors that occur while sending a report are ignored and the script is not halted. The request to GitHub has a timeout of `5` seconds.
___
### Example
```php
$dispatcher = new GitHubDispatcher(
	'github_pat_...', // Personal access token.
	'owner/repository', // Target repository.
	['bug'], // Issue labels.
	86400, // Throttle window in seconds.
	new APCu() // Optional throttle cache.
);

$handler = new ErrorHandler();
$handler->addDispatch($dispatcher, new MarkdownErrorFormatter());
```
___
### Functions
##### > __construct() : `void`
GitHubDispatcher constructor.

parameter | type | description
--- | --- | ---
`$token` | `string` | GitHub personal access token.
`$repository` | `string` | Target repository, in the format `owner/repository`.
`$labels` | `array` | Labels to add to raised issues.
`$throttle` | `int` | Seconds before a report with the same title can be raised again. `0` disables throttling.
`$cache` | `IDataCache|null` | Cache to store throttle data in. Defaults to a file in the system temp directory.
`$title` | `callable|null` | Title generator, called with the report. Defaults to a title built from the error.

##### > dispatch() : `bool`
Dispatch an error report. Always returns `false`.

parameter | type | description
--- | --- | ---
`$report` | `string|IErrorReport` | Report to dispatch.
