## KrameWork\Reporting\ReportColumn

***Table of Contents***
* **Overview** - Information about the class.
* **Examples** - Usage examples.
* **Functions** - Comprehensive list of all functions in the class.

___
### Overview
The `ReportColumn` class holds the specification of a column in a report; the column label and data type.
This is typically done in the columns() method of a class extending `ReportRunner`.

### Examples
```php
class MyReport extends ReportRunner
{
  public function columns() {
    return [
      new ReportColumn('Name', ReportColumn::COL_STRING),
      new ReportColumn('Age', ReportColumn::COL_INTEGER),
      new ReportColumn('Birthday', ReportColumn::COL_DATE)
    ];
  }
}
```
A currency column can set the currency code for all of its values, or read the code from another field in each row.
```php
new ReportColumn('Price', ReportColumn::COL_CURRENCY, 'EUR');
new ReportColumn('Price', ReportColumn::COL_CURRENCY, currencyField: 'price_currency');
```
The currency code for each value is taken from the first of these that is set:
1. The field named by `$currencyField` in the row, if its value is a non-empty string.
2. The `$currency` of the column.
3. The `currency()` of the `ReportRunner`.
4. `USD`.

___
### Constants
Constants available in the `ReportColumn` class:

constant | value | json format | description
--- | --- | --- | ---
`COL_NONE` | `` | unchanged | No specified value type, wrap the column with `Value`.
`COL_STRING` | `String` | unchanged | String data, wrap with `StringValue`.
`COL_DECIMAL` | `Decimal` | `float` | Decimal data, wrap with `DecimalValue`.
`COL_INTEGER` | `Integer` | `int` | Integer data, wrap with `IntegerValue`.
`COL_DATETIME` | `Datetime` | `ISO 8601 format` | Date and time data, wrap with `DateTimeValue`.
`COL_DATE` | `Date` | `ISO 8601 format` | Date data, wrap with `DateTimeValue`.
`COL_CUSTOM` | `Custom` | undefined | Custom data, wrap with `CustomValue`.
`COL_CURRENCY` | `Currency` | `{amount, currency}` | Currency data, wrap with `CurrencyValue`.

### Functions
##### > __construct() : `void`
ReportColumn constructor.

parameter | type | description
--- | --- | ---
`$label` | `string` | A label describing the column.
`$type` | `string` | A data type as defined by the `ReportColumn::COL_` constants. 
`$currency` | `string|null` | ISO 4217 currency code for a `COL_CURRENCY` column. Throws `InvalidArgumentException` for an unknown code.
`$currencyField` | `string|null` | Name of a field in each row that holds the ISO 4217 currency code for a `COL_CURRENCY` column.

