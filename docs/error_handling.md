### Error Handling

The error handling module of KrameWork is enabled by default. If you are using customized flags with the system constructor you will need to add the *KW_ERROR_HANDLER* flag or this module will not be loaded. The error handler is responsible for the following things:

* Setting the error level to report all errors (Can be disabled, see flags
* Collecting non-fatal errors.
* Collecting uncaught exceptions.
* Reporting collected errors/exceptions where desired.

#### Error Output

By default, errors and exceptions collected by the error handler are silent. Below you can see how to alter this behavior to log how you desire. Both of the options below can be used at the same time if you desire.

##### E-mail

Route error reports to an address.

```php
$system->getErrorHandler()->setOutputEmail('someone@somewhere.net');
```

Set a custom subject for the error e-mails. For the subject string, the wildcards *%time%* and *%type%* will be replaced with a timestamp and the error type, respectively.

```php
$system->getErrorHandler()->setOutputEmail('someone@somewhere.net', 'A wild error has appeared at %time%');
```

The third parameter can be used to control the address from which the error reports are sent from. If neglected, the default address for your mail server/PHP installation will be used.

```php
$system->getErrorHandler()->setOutputEmail('someone@somewhere.net', null, 'noreply@somewhere.net');
```

##### File Logging

When logging to files, the handler will check if the given path is a directory, if it is then single files for each report will be created. If the path is not a directory, the handler will treat it as a file and append each report to the end of the log. Should the file not exist, the handler will attempt to create it before failing silently if unable.

All reports being logged to one file is *NOT* recommended. You should either use the separate file method or the e-mail option above.

```php
// Attempt to log to the website_errors.log file.
$system->getErrorHandler()->setOutputLog('../logs/website_errors.log');
```

```php
// Attempt to log reports as separate files inside the error_reports directory.
$system->getErrorHandler()->setOutputLog('../logs/error_reports');
```