## KrameWork - A simple web framework for PHP

### Setting Up

Place the **KrameWork** directory with its contents somewhere accessible to your website and in your bootstrap construct a new *KrameSystem* object. You are all good to go!

```php
$system = new KrameSystem();
```

### What does KrameWork provide?

* Auto-loading which defaults to the KrameWork directory and just PHP files. See below for options.
* Automatic session set-up by default along with session utilities provided by the *Session* global.
* Simple conforming static interfaces for REST, Session and Cookie control.
* Provides a simple dependency injection system.
* MVC framework and templates to build a simple structure.
* More to be added soon.

### Manipulate auto-loading.

#### Add paths to auto-load from.

```php
$system->addAutoLoadPath("directory/relative/to/my/project");
```

#### Set which extensions get auto-loaded.

```php
$system->setAutoLoadExtensions(".php,.lua"); // Comma-separated.
```

### Setting flags.

The *KrameSystem* constructor takes a bitwise flag which can be manually provided to change the behavior from what KrameWork defaults with. The flags are detailed below.

* KW_ENABLE_SESSIONS: Automatically sets up a session when KrameWork is initialized. [Default]

### FAQ

#### Why does KrameWork not use namespaces?

Personally I dislike namespaces in PHP, if this causes problems for you, KrameWork isn't for you!

#### How does X feature work?

KrameWork is simple enough to be self-explanatory for the most part. Start by checking out the *examples* directory, if something is still confusing let me know and I will assist you and add more documentation/support.