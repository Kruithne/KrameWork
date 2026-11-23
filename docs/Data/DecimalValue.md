## KrameWork\Data\DecimalValue

***Table of Contents***
* **Overview** - Information about the class.
* **Examples** - Usage examples.
* **Functions** - Comprehensive list of all functions in the class.

___
### Overview
The `DecimalValue` class is a container class which provides features to deal with floating point data in reports.
___
### Examples
The value stored in the `DecimalValue` class will automatically be converted to string when necessary.
```php
$value = new DecimalValue(1234.5);
print($value); // > 1 234,50
```
The separators are set for all `DecimalValue` instances with static properties. The defaults are `,` for decimals and a space for thousands. `CurrencyValue` has its own separators.
```php
DecimalValue::$decimalSeparator = '.';
DecimalValue::$thousandsSeparator = ',';
print(new DecimalValue(1234.5)); // > 1,234.50
```
___
### Functions
##### > __construct() : `void`
DecimalValue constructor.

parameter | type | description
--- | --- | ---
`$value` | `int|float|string|null` | Any value, will be cast to float automatically.


##### > real() : `float|null`
Returns the value passed to the constructor converted to a float.

##### > json() : `float|null`
Returns the value passed to the constructor converted to a float.
API for `JsonSerialization`.

##### > compare() : `int`
Compares the value to another value. Used in sorting operations. Values that are not scalar are sorted as null.

parameter | type | description
--- | --- | ---
`$to` | `float|DecimalValue|null` | Another value to compare with.
##### > __toString() : `string`
Returns the inner value converted to a string.
A null value will be converted to an empty string.