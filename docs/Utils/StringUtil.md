## KrameWork\Utils\StringUtil

***Table of Contents***
* **Overview** - Information about the class.
* **Functions** - Comprehensive list of all functions in the class.

___
### Overview
The `StringUtil` class provides static methods for common string manipulation tasks that are used by some other components provided by KrameWork but benefit from being contained in their own transportable class.
___
### Functions
##### > formatDirectorySlashes() : `string`
Convert all slashes in a string to match the environment directory separator.

parameter | type | description
--- | --- | ---
`$path` | `string` | Path to clean.
`$trimTrail` | `bool` | If true, trailing spaces/slashes will be trimmed.
##### > namespaceBase() : `string`
Get the base class-name from a namespace string.

parameter | type | description
--- | --- | ---
`$namespace` | `string` | Namespace path.
##### > variableAsString() : `string`
Represent a variable as a pretty string.

parameter | type | description
--- | --- | ---
`$var` | `mixed` | Variable to represent.