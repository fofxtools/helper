# PHP Helper Functions Library

This is a library of PHP Helper functions.

The main class is `Tracker`, which allows you to create code sections to track script times. It can also track changes in memory, and bandwidth.

The Tracker class uses a Singleton pattern to ensure a single global instance.

## Installation

To include this package in your project, run the following command in your project’s root directory:

```bash
composer require fofx/helper
```

The default configuration file path `config/helper.config.php`.

## Usage

Below is the code from the `public/index.php` file. It creates code sections, and prints the Helper statistics with `timer_end()`.

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

Alternatively, in config/helper.config.php you can set 'autoStartTracker' to 'true'. This will auto-start the Tracker global Singleton.

Since this can cause problems in testing environments, this will only work if is_phpunit_environment() returns false.

If autoStart is enabled, you do not need to do "$tracker = Tracker::getInstance();". The global Singleton will be initialized automatically during autoloading.