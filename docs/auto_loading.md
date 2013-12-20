### Auto-loading

Auto-loading of classes is built into the core of KrameWork and cannot be disabled at current. By default, the following settings apply.

* Only class files inside the KrameWork library folder are auto-loaded.
* Only files with the *.php* extension are loaded.

#### Add paths to auto-load from.

```php
$system->addAutoLoadPath("directory/relative/to/my/project");
```

#### Set which extensions get auto-loaded.

```php
$system->setAutoLoadExtensions(".php,.lua"); // Comma-separated.
```