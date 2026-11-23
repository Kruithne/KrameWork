## KrameWork\Data\DateValue

***Table of Contents***
* **Overview** - Information about the class.
* **Examples** - Usage examples.
* **Functions** - Comprehensive list of all functions in the class.

___
### Overview
The `DateValue` class is a container class which provides features to deal with dates in reports.
___
### Examples
The value stored in the `DateValue` class will automatically be converted to string when necessary.
```php
$value = new DateValue("1980-01-01");
print($value);
```
___
### Functions
##### > __construct() : `void`
DateValue constructor.

parameter | type | description
--- | --- | ---
`$value` | `string|int|float|null` | A unix timestamp, or a string that will be passed through `strtotime`. An invalid string is stored as null.


##### > real() : `int|null`
Returns the value passed to the constructor, converted into a unix timestamp.

##### > json() : `string|null`
Returns the value passed to the constructor, converted into an ISO 8601 timestamp.
API for `JsonSerialization`.

##### > compare() : `int`
Compares the value to another value. Used in sorting operations. A unix timestamp or a date string can be given instead of a `DateValue`.

parameter | type | description
--- | --- | ---
`$to` | `DateValue|string|int|null` | Another value to compare with.
##### > __toString() : `string`
Returns the inner value converted to a string.
A null value will be converted to an empty string.