<?php

declare(strict_types=1);

/**
 * Outputs the GET or POST variables in a safe and formatted way.
 *
 * @param array  $vars The array of variables ($_GET or $_POST).
 * @param string $type The type of request ("GET" or "POST").
 */
function output_vars(array $vars, string $type): void
{
    if (empty($vars)) {
        echo "\$_{$type} is empty.<br/>\n";
    } else {
        foreach ($vars as $key => $value) {
            echo "\$_{$type} '" . htmlspecialchars((string)$key, ENT_QUOTES, 'UTF-8') . "' is: " . htmlspecialchars((string)$value, ENT_QUOTES, 'UTF-8') . "<br/>\n";
        }
    }
}

// Output GET variables
output_vars($_GET, 'GET');

// Output POST variables
output_vars($_POST, 'POST');
