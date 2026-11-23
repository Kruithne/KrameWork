## KrameWork\Data\CurrencyValue

***Table of Contents***
* **Overview** - Information about the class.
* **Examples** - Usage examples.
* **Functions** - Comprehensive list of all functions in the class.

___
### Overview
The `CurrencyValue` class is a container class which provides features to deal with money data in reports. Each value has an ISO 4217 currency code, such as `NOK` or `GBP`. If no code is given, `USD` is used.

The list of valid codes and the number of decimals for each code are defined in `KrameWork\Data\Currency`. The list is taken from the ISO 4217 list published 2026-09-17.
___
### Examples
The value stored in the `CurrencyValue` class will automatically be converted to string when necessary.
```php
$value = new CurrencyValue(4.2);
print($value); // > 4.20 USD

$value = new CurrencyValue(4.2, 'nok');
print($value); // > 4.20 NOK
print($value->currency()); // > NOK

$value = new CurrencyValue(1234.5, 'JPY');
print($value); // > 1,235 JPY

$value = new CurrencyValue(4.2, 'ABC'); // throws InvalidArgumentException
```
The separators are set for all `CurrencyValue` instances with static properties. The defaults are `.` for decimals and `,` for thousands.
```php
CurrencyValue::$decimalSeparator = ',';
CurrencyValue::$thousandsSeparator = ' ';
print(new CurrencyValue(1234.5, 'NOK')); // > 1 234,50 NOK
```
Values with different currency codes are sorted by the code first, then by the amount.
___
### Functions
##### > __construct() : `void`
CurrencyValue constructor.

parameter | type | description
--- | --- | ---
`$value` | `int|float|string|null` | Any value, will be cast to float automatically.
`$currency` | `string|null` | ISO 4217 currency code, such as `NOK`. Not case-sensitive. Defaults to `USD`. Throws `InvalidArgumentException` for an unknown code.

##### > currency() : `string`
Returns the currency code in upper case.


##### > real() : `float|null`
Returns the value passed to the constructor converted to a float.

##### > json() : `array`
Returns an array with the keys `amount` (`float|null`) and `currency` (`string`).
API for `JsonSerialization`.

##### > compare() : `int`
Compares the value to another value. Used in sorting operations. If both values are `CurrencyValue` with different currency codes, the codes are compared instead of the amounts. Values that are not scalar are sorted as null.

parameter | type | description
--- | --- | ---
`$to` | `float|CurrencyValue|null` | Another value to compare with.
##### > __toString() : `string`
Returns the inner value converted to a string, followed by the currency code. The number of decimals is defined by the currency, for example 2 for `USD` and 0 for `JPY`. Codes without decimals in ISO 4217, such as `XAU`, use 2.
A null value will be converted to an empty string.