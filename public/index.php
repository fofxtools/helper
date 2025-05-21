<?php

declare(strict_types=1);

// Optionally manuolly define the config file to be used by initialize_tracker()
//define('HELPER_CONFIG_FILE', __DIR__ . '/../config/helper.config.php.example');

require_once __DIR__ . '/../vendor/autoload.php';

use FOfX\Helper;
use FOfX\Helper\Tracker;

if (defined('HELPER_CONFIG_FILE')) {
    $tracker = Tracker::getInstance(configFile: constant('HELPER_CONFIG_FILE'));
} else {
    $tracker = Tracker::getInstance();
}

// Create three code sections
$sections = ['1', '2', '3'];
foreach ($sections as $i) {
    Tracker::scriptTimer('Section' . $i, 'start');
    Helper\rand_sleep(.1);
    echo Helper\now() . PHP_EOL;
    Tracker::scriptTimer('Section' . $i, 'end');
}

// Print the section timer information arrays
Tracker::trackerEnd();
