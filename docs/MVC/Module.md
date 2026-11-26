## KrameWork\MVC\Module

***Table of Contents***
* **Overview** - Information about the class.
* **Examples** - Usage examples.
* **Functions** - Comprehensive list of all functions in the class.

___
### Overview
`Module` is an abstract base class for a page component. Each module holds one [Template](Template.md) and renders it when you cast the module to a string. The template is `protected`, thus only the subclass can set its values.
___
### Example
Extend `Module`, give the template file to the parent constructor, then set the template values.
```php
// index.php
echo new GreetingModule('Lucy');

// greeting.php
<b>Hey <?=$user;?>, I remember your name.</b>

// GreetingModule.php
class GreetingModule extends Module {
    public function __construct(string $name) {
        parent::__construct(__DIR__ . '/greeting.php');
        $this->template->user = $name;
    }
}

// Output
<b>Hey Lucy, I remember your name.</b>
```
A module is a string when it renders, thus you can set a module as a value of a different template. The outer template then renders the module in its output.
```php
$layout = new Template(__DIR__ . '/layout.php');
$layout->content = new GreetingModule('Lucy');
echo $layout;
```
___
### Functions
##### > __construct() : `void`
Module constructor.

parameter | type | description
--- | --- | ---
`$file` | `string` | Path to the template file for this module.

exception | reason
--- | ---
`InvalidTemplateException` | Template file is either missing or invalid.

##### > __toString() : `string`
Render the template of this module and return it as a string.

exception | reason
--- | ---
`InvalidTemplateException` | Template closed the output buffer.
