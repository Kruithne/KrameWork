![Build Status](https://travis-ci.org/Kruithne/KrameWork.svg?branch=master)

## KrameWork - A simple web framework for PHP

### What does KrameWork provide?

* Auto-loading which defaults to the KrameWork directory and just PHP files. See below for options.
* Automatic session set-up by default along with session utilities provided by the *Session* global.
* Simple conforming static interfaces for REST, Session and Cookie control.
* Provides a simple dependency injection system.
* MVC framework and templates to build a simple structure.

### Setting Up

Place the **KrameWork** directory with its contents somewhere accessible to your website and in your bootstrap construct a new *KrameSystem* object. You are all good to go!

```php
$system = new KrameSystem();
```

### Guides

* [Flags](docs/flags.md)
* [Auto-loading](docs/auto_loading.md)
* [Error Handling](docs/error_handling.md)

### FAQ

#### Why does KrameWork not use namespaces?

Personally I dislike namespaces in PHP, if this causes problems for you, KrameWork isn't for you!

#### How does X feature work?

KrameWork is simple enough to be self-explanatory for the most part. Start by checking out the [guides](docs) linked above and the [examples](examples) directory, if something is still confusing let me know and I will assist you and add more documentation/support.

#### Something could be better or added...

If you have any suggestions, ideas or feedback, feel free to post an issue or e-mail me, whichever is more appropriate. Please provide as much information or reasoning as needed.