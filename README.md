## KrameWork - A simple web framework for PHP

### Setting Up

Place the **KrameWork** directory with its contents somewhere accessible to your website and in your bootstrap construct a new *KrameSystem* object. You are all good to go!

```php
$system = new KrameSystem();
```

### What does KrameWork provide?

* Auto-loading which defaults to the KrameWork directory and just PHP files. See below for options.
* Automatic session set-up by default along with session utilities provided by the *Session* global.
* REST utilities provided by the *REST* global.
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

### Namespaces?

Currently, KrameWork does not use namespaces and I have no intentions for it to do so, sorry.