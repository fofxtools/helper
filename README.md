# PHP Helper Functions Library

This is a library of PHP Helper functions.

The main class is `Tracker`, which allows you to create code sections to track script times. It can also track changes in memory, and bandwidth.

The Tracker class uses a Singleton pattern to ensure a single global instance.

## Installation

To include this package in your project, run the following command in your project's root directory:

```bash
composer require fofx/helper
```

The default configuration file path `config/helper.config.php`.

## Docs

- For a detailed overview of the project structure and components, please see [docs/project-structure.md](docs/project-structure.md).
- For detailed usage examples of the `Tracker` class, please see [docs/Tracker.usage.md](docs/Tracker.usage.md).
- For detailed documentation about the `ReflectionUtils` class and its methods, please see [docs/ReflectionUtils.usage.md](docs/ReflectionUtils.usage.md).

## Tracker Usage

Below is a basic example from the `public/index.php` file:

```php
require_once __DIR__ . "/../vendor/autoload.php";

use FOfX\Helper;
use FOfX\Helper\Tracker;

$tracker = Tracker::getInstance();
Helper\get_diagnostics();

// Create three code sections
$sections = array('1', '2', '3');
foreach ($sections as $i) {
    Tracker::scriptTimer("Section" . $i, "start");
    Helper\rand_sleep(.1);
    Tracker::scriptTimer("Section" . $i, "end");
}

// Print the section timer information arrays
Tracker::trackerEnd();
```

## AutoStart

Alternatively, in `config/helper.config.php` you can set `autoStartTracker` to `true`. This will auto-start the Tracker global Singleton.

Since this can cause problems in testing environments, this will only work if `is_phpunit_environment()` returns `false`.

If `autoStartTracker` is enabled, you do not need to do `$tracker = Tracker::getInstance();`. The global Singleton will be initialized automatically during autoloading.