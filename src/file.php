<?php

/**
 * File and Logging Utility Functions
 *
 * This file provides functions for file operations and logging.
 * It includes utilities for creating debug logs, generating log filenames,
 * and writing log messages to files.
 *
 * Key features:
 * - Debug logging
 * - Log file generation with customizable filenames
 * - File writing operations with error handling
 */

namespace FOfX\Helper;

/**
 * Create or append a debug message to a debug file in the current folder.
 * 
 * The function logs a message with a timestamp and script filename to a specified log file.
 * 
 * @param    string      $message   The debug message to log.
 * @param    string      $filename  The log file name. Defaults to "debug_log.log".
 * @return   void
 * @throws   \Exception
 * @see      get_micro_timestamp()
 * 
 * @example  
 * $message = "A debug message." . PHP_EOL . "Test.";
 * Helper\debug_log($message);
 * $message = "Another message.";
 * Helper\debug_log($message);
 */
function debug_log(string $message, string $filename = "debug_log.log"): void
{
    $directory = dirname($filename);

    if (!is_dir($directory) && !mkdir($directory, 0777, true)) {
        throw new \Exception("Failed to create directory: " . $directory);
    }

    $appendString = sprintf(
        "===== %s.%s\t%s =====\n%s",
        date("Y-m-d H:i:s"),
        get_micro_timestamp(true),
        $_SERVER['SCRIPT_FILENAME'],
        $message
    );

    if (file_put_contents(getcwd() . "/" . $filename, $appendString . PHP_EOL, FILE_APPEND) === false) {
        throw new \Exception("Failed to write to debug log file: " . $filename);
    }
}

/**
 * Create or append a message to a log file in the current folder.
 * The filename is generated based on the current date in "Y-m-d_H-i-s" format.
 * Optionally, microseconds can be included in the filename, and a prefix can be added before the date string.
 * 
 * @param    string             $message       The message to log.
 * @param    string             $prefix        The prefix to prepend to the filename.
 * @param    bool               $microseconds  Whether to append microseconds to the filename.
 * @return   string                            The full path of the log file.
 * @throws   \RuntimeException                 If the log file cannot be written to.
 * @see      generate_log_filename()
 * @see      get_micro_timestamp()
 * 
 * @example  
 * $message = "A debug message." . PHP_EOL . "Test.";
 * Helper\log_file($message, 'log', false);
 * $message = "Another message.";
 * Helper\log_file($message, 'log', false);
 */
function log_file(string $message, string $prefix = '', bool $microseconds = true): string
{
    // Generate the log filename
    $filename = generate_log_filename($prefix, $microseconds);

    $appendString = sprintf(
        "===== %s.%s\t%s =====\n%s",
        date("Y-m-d H:i:s"),
        get_micro_timestamp(true),
        $_SERVER['SCRIPT_FILENAME'],
        $message
    );

    // Full path to the log file
    $putfile = getcwd() . "/" . $filename;

    try {
        // Attempt to write to the log file
        if (file_put_contents($putfile, $appendString . PHP_EOL, FILE_APPEND) === false) {
            throw new \RuntimeException("Failed to write to log file: " . $filename);
        }
    } catch (\ErrorException $e) {
        // Catch ErrorException and rethrow as a RuntimeException
        throw new \RuntimeException($e->getMessage(), 0, $e);
    }

    // Return the full path of the log file
    return $putfile;
}

/**
 * Generate a log filename based on the current date and optional parameters.
 *
 * This function generates a filename using the current date and time, with optional
 * microseconds and a user-defined prefix. The filename is sanitized to remove any
 * invalid characters, except for those necessary for directory paths.
 *
 * @param   string  $prefix        The prefix to prepend to the filename. Defaults to an empty string.
 * @param   bool    $microseconds  Whether to append microseconds to the filename. Defaults to true.
 * @param   string  $date_format   The format of the date. Defaults to "Y-m-d_H-i-s".
 * @return  string                 The generated filename.
 * @see     get_micro_timestamp()
 */
function generate_log_filename(string $prefix = '', bool $microseconds = true, string $date_format = "Y-m-d_H-i-s"): string
{
    $filename = date($date_format);

    if (!empty($prefix)) {
        // Sanitize the prefix, but allow directory separators if needed
        $prefix = preg_replace('/[^A-Za-z0-9_\-\/\\\\]/', '', $prefix);
        $filename = $prefix . "_" . $filename;
    }

    if ($microseconds) {
        $timestamp = get_micro_timestamp(true);
        $filename .= $timestamp;
    }

    return $filename . ".log";
}
