## KrameWork\Runtime\ErrorFormatters\MarkdownErrorFormatter

***Table of Contents***
* **Overview** - Information about the class.
* **Functions** - Comprehensive list of all functions in the class.

___
### Overview
The `MarkdownErrorFormatter` is an implementation of `IErrorFormatter` for use with the `ErrorHandler` class. Error reports will be formatted as GitHub-flavored Markdown, with a content type of `text/markdown` and a `.md` file extension. It is intended for use with `GitHubDispatcher`, but can be used with any dispatcher.

Error details and arrays (such as `$_SERVER`) are formatted as tables. The stack trace, exception debug data and raw strings are formatted as code blocks. Below is an example of a report this formatter would produce.

````markdown
## RUNTIME ERROR: USER NOTICE

| | |
| --- | --- |
| **Message** | ` Something blew up! ` |
| **Server** | ` Linux web01 6.1.0 x86_64 ` |
| **Occurred** | ` Fri, 17 Feb 2017 02:17:07 +0000 ` |
| **Script** | ` /var/www/handling.test.php:20 ` |

### Stack Trace (2 steps)

```
handling.test.php:20 - user_error((string) (18) "Something blew up!", (integer) 1024)
handling.test.php:29 - merp((integer) 42)
```

### $_GET (1 items)

| Key | Value |
| --- | --- |
| ` sid ` | ` (string) (25) "k10ac6g9vskqfg9dj0k7ne67o" ` |

### $_POST (0 items)

_No data to display._

_Report generated automatically on Fri, 17 Feb 2017 02:17:07 +0000._
````
___
### Functions
##### > beginReport() : `void`
Called just before this report is used.

##### > reportError() : `void`
Format an error and add it to the report.

parameter | type | description
--- | --- | ---
`$error` | `IError` | Error which occurred.

##### > reportDebug() : `void`
Format application debug data and add it to the report. Nothing is added if the array is empty.

parameter | type | description
--- | --- | ---
`$debug` | `array` | Array of key/value pairs.

##### > reportStacktrace() : `void`
Format a stacktrace and add it to the report.

parameter | type | description
--- | --- | ---
`$trace` | `array` | Stacktrace.

##### > reportArray() : `void`
Format an array and add it to the report.

parameter | type | description
--- | --- | ---
`$name` | `string` | Name for the array.
`$arr` | `array` | Array of data.

##### > reportString() : `void`
Format a data string and add it to the report.

parameter | type | description
--- | --- | ---
`$name` | `string` | Name of the data string.
`$str` | `string` | Data string.

##### > generate() : `IErrorReport`
Generate the report.

exception | reason
--- | ---
`LogicException` | `reportError()` was not called before `generate()`.
