<?php

require_once __DIR__ . '/../vendor/autoload.php';

use FOfX\Helper;
use FOfX\Helper\Tracker;

$tracker = Tracker::getInstance();

// Create three code sections
$sections = ['1', '2', '3'];
foreach ($sections as $i) {
    Tracker::scriptTimer('Section' . $i, 'start');
    Helper\rand_sleep(.1);
    Tracker::scriptTimer('Section' . $i, 'end');
}

// Print the section timer information arrays
Tracker::trackerEnd();
