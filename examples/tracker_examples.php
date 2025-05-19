<?php

declare(strict_types=1);

require_once __DIR__ . '/../vendor/autoload.php';

use FOfX\Helper\Tracker;

$tracker = Tracker::getInstance();

// Start memory tracking
Tracker::scriptTimer('MemoryCheck', 'start');

// Your memory-intensive operations here...
$largeArray = range(1, 1000000);

// HTTP request
$url      = 'https://www.example.com';
$response = file_get_contents($url);
$bytes    = strlen($response);
echo "Bytes for $url: $bytes\n";

// End tracking
Tracker::scriptTimer('MemoryCheck', 'end');

// Print results with $printAllArrays true
Tracker::trackerEnd(true);
