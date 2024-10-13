<?php

/**
 * Bootstrap Initialization File
 *
 * This file initializes the Tracker singleton if autoStartTracker is set in the configuration.
 * It should be included in composer.json after all other files to ensure proper initialization.
 *
 * Key features:
 * - Checks for PHPUnit testing environment
 * - Loads configuration file
 * - Initializes Tracker singleton based on configuration
 */

namespace FOfX\Helper;

// Only call initialize_tracker if not in a testing environment
if (!is_phpunit_environment()) {
    // If HELPER_CONFIG_FILE is not defined yet, then set it as the default config file
    // This constant can be used to set the config file for initialize_tracker() before autoloading
    if (!defined('HELPER_CONFIG_FILE')) {
        define('HELPER_CONFIG_FILE', 'config/helper.config.php');
    }
    initialize_tracker(HELPER_CONFIG_FILE);
}

/**
 * Initialize the Tracker Singleton if autoStartTracker is set in helper.config.php.
 *
 * This function attempts to load the configuration file, checks for the
 * 'autoStartTracker' setting, and initializes the Tracker singleton if
 * the setting is true.
 *
 * @param string|null $configFile The path to the configuration file.
 *                                Defaults to 'config/helper.config.php'.
 *
 * @throws \RuntimeException If the configuration file cannot be loaded.
 *                           This exception is caught and ignored within the function.
 *
 * @return void
 *
 * @see     Tracker::getInstance()
 * @see     resolve_config_file_path()
 * @see     load_config()
 */
function initialize_tracker(?string $configFile = 'config' . DIRECTORY_SEPARATOR . 'helper.config.php'): void
{
    $config_file_resolved = resolve_config_file_path($configFile);

    $config_data = [];
    if ($config_file_resolved !== null) {
        try {
            $config_data = load_config($config_file_resolved);
        } catch (\RuntimeException $e) {
            // Configuration file not found or invalid, using default settings
        }
    }

    // Convert the string to a boolean
    $autoStart = isset($config_data['tracker']['autoStartTracker'])
        ? filter_var($config_data['tracker']['autoStartTracker'], FILTER_VALIDATE_BOOLEAN)
        : false;
    if ($autoStart) {
        Tracker::getInstance(configFile: $configFile);
    }
}
