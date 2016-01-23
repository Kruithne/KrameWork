# KrameWork : Code Guidelines

When writing code for this framework, please adhere to the coding style outlined in this document. Project manager may get grouchy if this is ignored.

+ All code written should be compatible with and will be tested against PHP 5.4 along with other major builds up to the current nightly release of PHP. Code will also be tested against the latest build of HHVM.

+ New features MUST include comprehensive tests to ensure that any changes made does not break intended functionality. Tests will be automatically run using PHPUnit.

+ Indenting should be done with tab characters, not spaces.

+ No trailing whitespace. No blank lines at the end of files.

+  PHP source files should use the `<?php` opening and  `?>` closing tags. Closing tags are required.

+ All added functions and variables should include correctly formatted and details phpDoc. For details on documentation syntax and formatting, see http://phpdoc.org/docs/latest/index.html.

+ Strings should be wrapped with double-quotation marks for almost all cases. Special cases may apply where this rule can be skipped.

+ Class names should use pascal casing. All methods (static too) and variables should use camel casing. For static functions intended for heavy use in production code, pascal casing is allowed. Static functions intended for (mostly) internal use should use camel casing.

+ Class names should include the prefix `KW_`. Some exceptions to this rule exist, such as interfaces and static functions intended for heavy production usage, such as the `Cookie` class.

+ Interface names should be prefixed with `I`, for example `IDataContainer` is valid where `DataContainerInterface` is not.

+ Parameters/arguments passed into a method call should be seperated by a comma and a space.
  ```
  some_func($oh,$baby); // Wrong
  some_func($oh, $baby); // Right
  ```

+ Classes should be organized in the following order:
  + constants
  + static methods
  + magic methods
  + normal methods
  + static variables (public -> protected -> private)
  + normal variables (public -> protected -> private).


+ A space should be included before the opening parentheses of a statement (example below).

  ```
  if($answer == 42) // This is wrong.
  if ($answer == 42) // This is right
  ```


+ If a scope (or a related scope) contains more than one line of code (regardless of actual statement count) then it must be wrapped with brackets.

  ```
  if ($rock == $roll // This is wrong.
      foreach($people as $person)
          doThing();
  else
      doTheOtherThing();

  if ($rock == $roll) // This is right.
  {
      foreach ($people as $person)
          doThing();
  }
  else
  {
      doTheOtherThing();
  }

  if ($rock == $roll) // This is also right.
      partyHard();
  else
      rockOn();
  ```

 + Opening scope brackets should be on a new-line. Rare exceptions such as anonymous functions.

  ```
  switch ($thing) { // Wrong
  }

  switch ($thing) // Right
  {
  }
  ```