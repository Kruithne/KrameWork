## KrameWork\HTTP\Request\JSONRequest : WebRequest

***Table of Contents***
* **Overview** - Information about the class.
* **Example** - Example usage of the class.
* **Functions** - Comprehensive list of all functions in the class.

___
### Overview
The `JSONRequest` class is a basic extension of the `WebRequest` class which attempts to automatically decode the response as a JSON object when `getResponse()` is called. It's worth noting that `__toString()` will return the raw response data.

> **Note**: `postJSON()` requires the `WebRequest::METHOD_POST` method. It throws an `InvalidMethodException` if you use another method.

The following headers are set by default, as well as the `WebRequest` defaults.
header | value
--- | ---
`Content-type` | `application/json`, only set when using `postJSON()`, otherwise uses `WebRequest` default.
`Accept` | `application/json`

___
### Example
Sending a request using this class is straight-forward, take a look at the example below for the most basic usage.
```php
$req = new JSONRequest('http://hipsterjesus.com/api/');
if ($req->send())
    var_dump($req->getResponse());

// > object(stdClass)#1 (2) {
//     ["text"]=>string(2502) "<p>Banh mi chicharrones cred aute...
```
The raw response of the request can be obtained by invoking the `__toString()` magic method on the object. Trying to obtain the response before calling `send()`, or if `send()` returns `false`, will throw a `ResponseNotAvailableException`.

Posting some object to the API;
```php
$req = new JSONRequest('https://supersecret.example.com/api/magical', WebRequest::METHOD_POST);
if ($req->postJSON((object) ['something' => 'else']))
    var_dump($req->getResponse());
```
___
### Functions
##### > __construct() : `void`
WebRequest constructor.

parameter | type | description
--- | --- | ---
`$url` | `string` | Request endpoint.
`$method` | `string` | Request method (use class constants).

##### > setHeader() : `void`
Add a header to this request.

parameter | type | description
--- | --- | ---
`$fieldName` | `string` | Field name of the header.
`$fieldValue` | `string` | Field value of the header.

##### > setHeaderObject() : `void`
Add a HTTP header object to the request.

parameter | type | description
--- | --- | ---
`$header` | `HTTPHeader` | Header object to add.

##### > setHeaders() : `void`
Add multiple headers to the request. Array must be in fieldName => fieldValue format.

parameter | type | description
--- | --- | ---
`$headers` | `array` | Array of headers to add.

##### > hasHeader() : `bool`
Check if this request has a header set.

parameter | type | description
--- | --- | ---
`$checkName` | `string` | Field name to check for.

##### > getHeader() : `array|null`
Get a header set on this request. Returns a two-value array (fieldName, fieldValue) or null.

parameter | type | description
--- | --- | ---
`$getName` | `string` | Field name of the header.

##### > send() : `bool`
Send the request. Return boolean indicates success.

##### > postJSON() : `bool`
Post an object encoded as json with the request. Return boolean indicates success.

parameter | type | description
--- | --- | ---
`$object` | `object` | Data to json encode and post to the server API.

exception | reason
--- | ---
`InvalidMethodException` | Not using METHOD_POST for a JSON-body request.
`JsonException` | Object could not be encoded as JSON.

##### > getResponse() : `mixed`
Get the JSON decoded response from this request. Returns null if unable to decode response.

exception | reason
--- | ---
`ResponseNotAvailableException` | Request not sent or failed.

##### > success() : `bool|null`
True/false depending on success of request. Returns null if request has not yet been sent.

##### > __toString() : `string`
Return the response for this request.

exception | reason
--- | ---
`ResponseNotAvailableException` | Request not sent or failed.