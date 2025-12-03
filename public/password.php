<?php

declare(strict_types=1);

require_once __DIR__ . '/../vendor/autoload.php';

use FOfX\Helper;

$num       = isset($_GET['num']) ? intval($_GET['num']) : 1;
$passwords = [];
for ($i = 0; $i < $num; $i++) {
    $passwords[] = Helper\generate_password(10, true, true, false);
}

echo implode('<br/>' . PHP_EOL, $passwords);
