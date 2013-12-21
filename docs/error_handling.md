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
$system->getErrorHandler()->appendEmailOutputRecipient('someone@somewhere.net');
```

For more advanced control over the e-mail output, the KW_Mail object can be accessed as shown below which allows for direct manipulation of the output such as changing the subject.

```php
$system->getErrorHandler()->getMailObject()->setSubject('This is a custom subject!');
```

The third parameter can be used to control the address from which the error reports are sent from. If neglected, the default address for your mail server/PHP installation will be used.

```php
$system->getErrorHandler()->setOutputEmail('someone@somewhere.net', null, 'Error Handler <noreply@somewhere.net>');
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

#### What is contained in an error report?

An error report contains information regarding the error (type, line, file, message) as per standard along with the version of PHP running, the operating system for the server and the output of the SESSION, GET and POST global arrays at the time of the error. The ability to customize an error report is not yet available however if requested this may be expanded upon.