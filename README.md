## KrameWork - A simple web framework for PHP

### Setting Up

Place the **KrameWork** directory with its contents somewhere accessible to your website and in your bootstrap construct a new *KrameSystem* object. You are all good to go!

```php
$system = new KrameSystem();
```

### Setting flags.

The *KrameSystem* constructor takes a bitwise flag which can be manually provided to change the behavior from what KrameWork defaults with. The flags are detailed below.

* KW_ENABLE_SESSIONS: Automatically sets up a session when KrameWork is initialized. [Default]

### Namespaces?

Currently, KrameWork does not use namespaces and I have no intentions for it to do so, sorry.