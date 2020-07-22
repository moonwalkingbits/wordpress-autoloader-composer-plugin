# WordPress Autoloader Composer Plugin

To easily consume libraries following the [WordPress coding standards](https://make.wordpress.org/core/handbook/best-practices/coding-standards/php/)
this plugin enables the libraries to register namespace mappings in the
composer configuration like any other autoloading standard.

A library can register any number of namespace mappings:

```json
{
    "autoload": {
        "wordpress": {
            "Namespace\\": "path/to/classes/"
        }
    }
}
```

This plugin will generate a file named `wordpress-autoload.php` that resides in
the composer vendor directory. The consumer of the library can include this file
directly and it will work as expected. But you can also add the file to the list
of autoloaded files in the composer configuration. Doing this allows you to use
all possible autoloading standards simultaneously:

```json
{
    "autoload": {
        "files": [
            "vendor/wordpress-autoload.php"
        ]
    }
}
```

The consumer can of course also define its own set of namespace mappings:

```json
{
    "autoload": {
        "Additional\\Namespace\\": "path/",
        "files": [
            "vendor/wordpress-autoload.php"
        ]
    }
}
```

Just like the composer autoload file `wordpress-autoload.php` returns the
autoloader instance. If you use `wordpress-autoload.php` directly you can
store the return value of the include call in a variable and add more
namespace mappings:

```php
<?php

$autoloader = require_once 'vendor/wordpress-autoload.php';
$autoloader->add_namespace_mapping( 'Additional\\Namespace\\', __DIR__ . '/path' );
```

## License

WordPress Autoloader Composer Plugin is released under the [GPL](https://www.gnu.org/licenses/) license.
