<?php

/**
 * Server and Environment Utility Functions
 *
 * This file contains functions related to server operations and environment information.
 * It includes utilities for IP address handling, host information retrieval,
 * and cross-platform command execution.
 *
 * Key features:
 * - Remote IP address detection
 * - HTTP host retrieval
 * - Cross-platform shell command utilities
 */

declare(strict_types=1);

namespace FOfX\Helper;

/**
 * Get the remote IP address of the client.
 *
 * This function checks for the IP address in the following order:
 * 1. HTTP_CLIENT_IP
 * 2. HTTP_X_FORWARDED_FOR
 * 3. REMOTE_ADDR
 *
 * If none are found, it defaults to '127.0.0.1'.
 * If the IP is invalid, an exception is thrown.
 *
 * @throws \UnexpectedValueException If the IP address is invalid
 *
 * @return string The valid IP address of the client
 */
function get_remote_addr(): string
{
    // Headers in order of priority
    $ip_headers = [
        'HTTP_CLIENT_IP',
        'HTTP_X_FORWARDED_FOR',
        'REMOTE_ADDR',
    ];

    $remote_addr = null;

    // Check headers and use the first header that is valid
    foreach ($ip_headers as $header) {
        if (!empty($_SERVER[$header])) {
            $remote_addr = $_SERVER[$header];

            // If multiple IPs, take the first one
            if (strpos($remote_addr, ',') !== false) {
                $ip_array    = explode(',', $remote_addr);
                $remote_addr = trim($ip_array[0]);
            }

            break;
        }
    }

    // If the IP is blank, default to '127.0.0.1'
    if (empty($remote_addr)) {
        return '127.0.0.1';
    }

    // Validate the IP address
    if (!filter_var($remote_addr, FILTER_VALIDATE_IP)) {
        throw new \UnexpectedValueException('Invalid IP address provided.');
    }

    return $remote_addr;
}

/**
 * Get the HTTP host of the server.
 *
 * This function retrieves the HTTP host in the following order:
 * 1. HTTP_X_FORWARDED_HOST (if $use_forwarded_host is true)
 * 2. HTTP_HOST
 * 3. SERVER_NAME
 * 4. 'localhost' (as a last resort default)
 *
 * @param bool $use_forwarded_host Whether to prioritize HTTP_X_FORWARDED_HOST if available (default: false)
 *
 * @throws \UnexpectedValueException If the host is invalid
 *
 * @return string The HTTP host
 */
function get_http_host(bool $use_forwarded_host = false): string
{
    $host = null;

    if ($use_forwarded_host && !empty($_SERVER['HTTP_X_FORWARDED_HOST'])) {
        $hosts = explode(',', $_SERVER['HTTP_X_FORWARDED_HOST']);
        $host  = trim($hosts[0]);
    } elseif (!empty($_SERVER['HTTP_HOST'])) {
        $host = $_SERVER['HTTP_HOST'];
    } elseif (!empty($_SERVER['SERVER_NAME'])) {
        $host = $_SERVER['SERVER_NAME'];
    }

    // If the host is blank, default to 'localhost'
    if (empty($host)) {
        return 'localhost';
    }

    // Validate the host
    $host_parts = explode(':', $host);
    $domain     = $host_parts[0];
    if (!filter_var($domain, FILTER_VALIDATE_DOMAIN, ['flags' => FILTER_FLAG_HOSTNAME])) {
        throw new \UnexpectedValueException("Invalid host provided: $host");
    }

    return $host;
}

/**
 * Retrieve the home directory of the current user.
 *
 * This function retrieves the user's home directory based on the operating system:
 * - On Windows, it uses the HOMEDRIVE and HOMEPATH environment variables, or USERPROFILE as a fallback.
 * - On Unix-like systems, it uses posix_getpwuid() to retrieve the home directory.
 * - If all else fails, it falls back to using `shell_exec('echo ~')`.
 *
 * @link    https://stackoverflow.com/questions/1894917/how-to-get-the-home-directory-from-a-php-cli-script
 * @link    https://stackoverflow.com/questions/20535474/php-get-user-home-directory-for-virtual-hosting
 *
 * @param string|null $osFamily The OS family to use (for testing purposes).
 *
 * @return string The home directory of the current user.
 */
function get_user_home_directory(?string $osFamily = null): string
{
    $osFamily = $osFamily ?? PHP_OS_FAMILY;

    if ($osFamily === 'Windows') {
        return get_windows_home_directory($osFamily);
    }

    return get_unix_home_directory($osFamily);
}

/**
 * Retrieve the home directory for Windows systems.
 *
 * @param string|null $osFamily The OS family to use (for testing purposes).
 *
 * @throws \RuntimeException If called on a non-Windows system or unable to determine the home directory.
 *
 * @return string
 */
function get_windows_home_directory(?string $osFamily = null): string
{
    $osFamily = $osFamily ?? PHP_OS_FAMILY;

    if ($osFamily !== 'Windows') {
        throw new \RuntimeException('get_windows_home_directory() can only be called on Windows systems.');
    }

    if (isset($_SERVER['HOMEDRIVE'], $_SERVER['HOMEPATH'])) {
        return $_SERVER['HOMEDRIVE'] . $_SERVER['HOMEPATH'];
    }

    if (isset($_SERVER['USERPROFILE'])) {
        return $_SERVER['USERPROFILE'];
    }

    $home = shell_exec('echo %USERPROFILE%');
    if ($home !== null) {
        return trim($home);
    }

    throw new \RuntimeException('Unable to determine home directory on Windows.');
}

/**
 * Retrieve the home directory for Unix-like systems.
 *
 * @param string|null $osFamily The OS family to use (for testing purposes).
 *                              $_SERVER['HOME'] is undefined from the browser, so use exec("echo ~") instead.
 *                              $_SERVER['HOME'] and exec("echo ~") will not work on CyberPanel, I believe due to OpenLiteSpeed.
 *                              So use posix_getpwuid(posix_getuid()) and $user['dir'] instead.
 *
 * @throws \RuntimeException If called on a Windows system or unable to determine the home directory.
 *
 * @return string
 */
function get_unix_home_directory(?string $osFamily = null): string
{
    $osFamily = $osFamily ?? PHP_OS_FAMILY;

    if ($osFamily === 'Windows') {
        throw new \RuntimeException('get_unix_home_directory() can only be called on Unix-like systems.');
    }

    if (function_exists('posix_getpwuid') && function_exists('posix_getuid')) {
        $user = posix_getpwuid(posix_getuid());
        if (isset($user['dir'])) {
            return $user['dir'];
        }
    }

    if (isset($_SERVER['HOME'])) {
        return $_SERVER['HOME'];
    }

    $home = shell_exec('echo ~');
    if ($home !== null) {
        return trim($home);
    }

    throw new \RuntimeException('Unable to determine home directory on Unix-like system.');
}

/**
 * Get the filename from a valid URL.
 *
 * @link     https://stackoverflow.com/questions/7852296/get-only-filename-from-url-in-php-without-any-variable-values-which-exist-in-the
 *
 * @param string $url The URL to parse.
 *
 * @return string|null The filename, or null if the URL is invalid.
 *
 * @example
 * echo Helper\url_filename("https://www.example.com/subfolder1/subfolder2/index.php?string=abc&num=123") . PHP_EOL;
 * // Returns index.php
 */
function url_filename(string $url): ?string
{
    // Validate the URL
    if (!filter_var($url, FILTER_VALIDATE_URL)) {
        return null;
    }

    // Get the path component of the URL
    $path = parse_url($url, PHP_URL_PATH);

    // Check if the path is null or if it ends with a slash (directory)
    if ($path === null || substr($path, -1) === '/') {
        return '';
    }

    // Return the basename of the path
    return basename($path);
}

/**
 * Get the file extension from a valid URL.
 *
 * @link     https://stackoverflow.com/questions/173868/how-to-get-a-files-extension-in-php
 *
 * @param string $url The URL to extract the file extension from.
 *
 * @return string|null The file extension, or null if no extension is found.
 *
 * @example
 * echo Helper\url_file_extension("https://www.example.com/subfolder1/subfolder2/index.php?string=abc&num=123") . PHP_EOL;
 * // Returns php
 */
function url_file_extension(string $url): ?string
{
    // Validate the URL
    if (!filter_var($url, FILTER_VALIDATE_URL)) {
        return null;
    }

    // Extract the filename from the URL
    $filename = url_filename($url);

    // Handle cases where filename extraction fails
    if ($filename === null || $filename === '') {
        return null;
    }

    // Extract the file extension
    $extension = pathinfo($filename, PATHINFO_EXTENSION);

    // Return null if no extension is found
    return $extension ?: null;
}

/**
 * Get network statistics for Windows or Linux.
 *
 * @param int|false $pid             Optional process ID for Linux.
 * @param ?callable $commandExecutor A callable that executes the shell command. For testing.
 *                                   Defaults to shell_exec.
 *
 * @throws \RuntimeException         If unable to fetch network statistics.
 * @throws \InvalidArgumentException If an invalid process ID is provided.
 *
 * @return array An associative array with network interfaces as keys, and their stats.
 *
 * @see     get_windows_network_stats
 * @see     get_linux_network_stats
 */
function get_network_stats(int|false $pid = false, ?callable $commandExecutor = null): array
{
    // Default to 'shell_exec' if no callable is provided
    $commandExecutor = $commandExecutor ?? 'shell_exec';

    if (PHP_OS_FAMILY === 'Windows') {
        return get_windows_network_stats($commandExecutor);
    } else {
        return get_linux_network_stats($pid, $commandExecutor);
    }
}

/**
 * Get network statistics for Windows using netstat.
 *
 * @param ?callable $commandExecutor A callable that executes the shell command. For testing.
 *
 * @throws \RuntimeException If unable to retrieve network statistics.
 *
 * @return array An associative array of network stats.
 */
function get_windows_network_stats(?callable $commandExecutor = null): array
{
    // Default to 'shell_exec' if no callable is provided
    $commandExecutor = $commandExecutor ?? 'shell_exec';

    $output = $commandExecutor('netstat -e');
    if ($output === false) {
        throw new \RuntimeException('Failed to retrieve network statistics for Windows.');
    }

    $lines = explode("\n", $output);
    $stats = [];

    foreach ($lines as $line) {
        if (strpos($line, 'Bytes') !== false) {
            $split = preg_split('/\s+/', trim($line));
            if (count($split) === 3) {
                $stats['Bytes'] = [
                    'Receive'  => $split[1],
                    'Transmit' => $split[2],
                ];
            }

            break;
        }
    }

    return $stats;
}

/**
 * Get network statistics for Linux using /proc/net/dev.
 *
 * @param int|false $pid             Optional process ID.
 * @param ?callable $commandExecutor A callable that executes the shell command. For testing.
 *
 * @throws \InvalidArgumentException If an invalid process ID is provided.
 * @throws \RuntimeException         If unable to retrieve network statistics.
 *
 * @return array An associative array of network stats.
 */
function get_linux_network_stats(int|false $pid = false, ?callable $commandExecutor = null): array
{
    // Default to 'shell_exec' if no callable is provided
    $commandExecutor = $commandExecutor ?? 'shell_exec';

    // For shell_exec, validate that the process ID is a positive integer, and that the file exists
    // If not using shell_exec, this is probably a mock callable for testing, so we'll ignore the validation
    if ($commandExecutor === 'shell_exec' && $pid !== false && ($pid <= 0 || !is_readable("/proc/$pid/net/dev"))) {
        throw new \InvalidArgumentException('Invalid process ID provided.');
    }

    $file   = $pid ? "/proc/$pid/net/dev" : '/proc/net/dev';
    $output = $commandExecutor("cat $file");

    if ($output === false) {
        throw new \RuntimeException('Failed to retrieve network statistics for Linux.');
    }

    $lines = explode("\n", $output);
    $stats = [];

    foreach ($lines as $line) {
        if (strpos($line, ':') !== false) {
            $split     = preg_split('/\s+/', trim($line));
            $interface = str_replace(':', '', $split[0]);
            // The 1st and 9th elements are the Receive and Transmit bytes
            if (isset($split[1], $split[9])) {
                $stats[$interface] = [
                    'Receive'  => $split[1],
                    'Transmit' => $split[9],
                ];
            }
        }
    }

    return $stats;
}

/**
 * Prints various diagnostic information for debugging purposes.
 *
 * This function retrieves and prints diagnostic information such as:
 * - PHP system constants (__LINE__, __FILE__, __DIR__, etc.).
 * - Path information (dirname(__FILE__), getcwd(), etc.).
 * - Server variables ($_SERVER, $_SESSION, $_COOKIE, etc.).
 * - Counts and lengths of global arrays.
 *
 * This function should only be used in development environments, as it may expose sensitive data.
 *
 * @return void
 *
 * @see     print_php_constants
 * @see     print_server_variables
 * @see     print_path_info
 * @see     print_array_info
 */
function get_diagnostics(): void
{
    print_php_constants();
    print_path_info();
    print_server_variables();

    // Note that arrays like $GLOBALS may contain values that give errors
    // when trying to serialize() in recursive_implode().
    // For instance if you have a \NumberFormatter $GLOBAL you can get:
    // "PHP Fatal error:  Uncaught Exception: Serialization of 'NumberFormatter' is not allowed"
    // Thus get_diagnostics() should be called before creating any such global variables
    print_array_info('GLOBALS', $GLOBALS);
    print_array_info('_COOKIE', $_COOKIE);
    print_array_info('_ENV', $_ENV);
    print_array_info('_FILES', $_FILES);
    print_array_info('_GET', $_GET);
    print_array_info('_POST', $_POST);
    print_array_info('_REQUEST', $_REQUEST);
    print_array_info('_SERVER', $_SERVER);
    if (isset($_SESSION)) {
        print_array_info('_SESSION', $_SESSION);
    } else {
        print_array_info('_SESSION', []);
    }

    // php://input stream length
    try {
        $input = file_get_contents('php://input');
        echo 'strlen(php://input) : ' . strlen($input) . PHP_EOL;
    } catch (\Exception $e) {
        echo 'Error reading php://input: ' . $e->getMessage() . PHP_EOL;
    }
}

/**
 * Prints basic PHP system constants for diagnostic purposes.
 *
 * @return void
 */
function print_php_constants(): void
{
    echo '__LINE__ : ' . __LINE__ . PHP_EOL;
    echo '__FILE__ : ' . __FILE__ . PHP_EOL;
    echo '__DIR__ : ' . __DIR__ . PHP_EOL;
    echo '__FUNCTION__ : ' . __FUNCTION__ . PHP_EOL;

    // Check if within a class or trait context
    echo '__CLASS__ : ' . (defined('__CLASS__') ? __CLASS__ : 'N/A') . PHP_EOL;
    echo '__TRAIT__ : ' . (defined('__TRAIT__') ? __TRAIT__ : 'N/A') . PHP_EOL;

    echo '__METHOD__ : ' . __METHOD__ . PHP_EOL;
    echo '__NAMESPACE__ : ' . __NAMESPACE__ . PHP_EOL;
}

/**
 * Prints file system path information.
 *
 * @return void
 */
function print_path_info(): void
{
    $paths = [
        "dirname('.')"      => dirname('.'),
        'dirname(__FILE__)' => dirname(__FILE__),
        'getcwd()'          => getcwd(),
    ];

    foreach ($paths as $label => $path) {
        echo "$label: $path\n";
    }
}

/**
 * Prints server-specific diagnostic information.
 *
 * @return void
 */
function print_server_variables(): void
{
    echo 'get_remote_addr(): ' . get_remote_addr() . PHP_EOL;
    echo 'get_http_host(): ' . get_http_host() . PHP_EOL;
    echo "\$_SERVER['SCRIPT_FILENAME']: " . $_SERVER['SCRIPT_FILENAME'] . PHP_EOL;
    echo "\$_SERVER['PHP_SELF']: " . $_SERVER['PHP_SELF'] . PHP_EOL;
    echo "\$_SERVER['DOCUMENT_ROOT']: " . $_SERVER['DOCUMENT_ROOT'] . PHP_EOL;
    echo "realpath(\$_SERVER['SCRIPT_FILENAME']): " . realpath($_SERVER['SCRIPT_FILENAME']) . PHP_EOL;
    echo "realpath(\$_SERVER['PHP_SELF']): " . realpath($_SERVER['PHP_SELF']) . PHP_EOL;
    echo "realpath(\$_SERVER['DOCUMENT_ROOT']): " . realpath($_SERVER['DOCUMENT_ROOT']) . PHP_EOL;
}

/**
 * Prints the count and length of recursively imploded values in a global array.
 *
 * @param string $name The name of the array to print information about
 * @param array  $arr  The array to analyze
 *
 * @return void
 *
 * @see     recursive_implode
 */
function print_array_info(string $name, array $arr): void
{
    echo "count(\$$name) : " . count($arr) . PHP_EOL;
    echo "strlen(recursive_implode(\$$name)) : " . strlen(recursive_implode($arr)) . PHP_EOL;
}

/**
 * Escapes a string to be used as a command line argument on Windows.
 *
 * This function properly escapes special characters, including double quotes,
 * so that the argument is passed correctly to Windows CLI commands.
 *
 * @param string $arg The argument to escape.
 *
 * @return string The escaped argument.
 */
function escape_windows_cmd_argument(string $arg): string
{
    // Enclose the argument in double quotes
    $escaped         = '"';
    $length          = strlen($arg);
    $num_backslashes = 0;

    for ($i = 0; $i < $length; $i++) {
        $char = $arg[$i];

        if ($char === '\\') {
            // Count backslashes
            $num_backslashes++;
        } elseif ($char === '"') {
            // Escape all backslashes before a double quote
            $escaped .= str_repeat('\\', $num_backslashes * 2 + 1);
            $escaped .= '"';
            $num_backslashes = 0;
        } else {
            // Output any accumulated backslashes
            if ($num_backslashes > 0) {
                $escaped .= str_repeat('\\', $num_backslashes);
                $num_backslashes = 0;
            }
            // Add the current character
            $escaped .= $char;
        }
    }

    // Escape remaining backslashes
    if ($num_backslashes > 0) {
        $escaped .= str_repeat('\\', $num_backslashes * 2);
    }

    $escaped .= '"';

    return $escaped;
}

/**
 * Escapes a string to be used as a shell argument, handling platform-specific differences.
 *
 * This wrapper function ensures that shell arguments are properly escaped based on the operating system.
 * On Windows, it uses a custom escaping function to handle special characters and quotes
 * (`escape_windows_cmd_argument`), while on Unix-like systems, it uses the native `escapeshellarg` function.
 *
 * @param string $arg The argument to escape.
 *
 * @return string The escaped argument, suitable for safe usage in shell commands.
 */
function escapeshellarg_crossplatform(string $arg): string
{
    if (PHP_OS_FAMILY === 'Windows') {
        return escape_windows_cmd_argument($arg);
    } else {
        return escapeshellarg($arg);
    }
}

/**
 * Checks if a given file or folder path is relative or absolute.
 *
 * @param string $path The file or folder path to check.
 *
 * @return bool True if the path is absolute, false if it is relative.
 */
function is_absolute_path(string $path): bool
{
    // Check for Windows absolute paths (drive letter or network share)
    if (DIRECTORY_SEPARATOR === '\\') {
        return (bool) preg_match('/^[A-Z]:[\\\\\\/]/i', $path) ||
            str_starts_with($path, '\\\\');
    }

    // Check for Unix-like absolute paths
    return str_starts_with($path, '/');
}

/**
 * Attempts to mimic escapeshellcmd()'s behavior on both Linux and Windows.
 *
 * Since escapeshellcmd() works differently on Linux and Windows, this function attempts to mimic
 * the behavior of escapeshellcmd() on a specified operating system.
 *
 * @param string $command The command to escape.
 * @param ?bool  $windows Whether to escape for Windows. If null, detects the current OS.
 *
 * @throws \ValueError If the command contains null bytes.
 *
 * @return string The escaped command.
 *
 * @link https://github.com/php/php-src/blob/master/ext/standard/exec.c
 */
function escapeshellcmd_os(string $command, ?bool $windows = null): string
{
    // Determine if we're on Windows if $windows is null
    if ($windows === null) {
        $windows = PHP_OS_FAMILY === 'Windows';
    }

    $escaped = '';
    // Tracks the current quote type (' or ")
    $inQuote = null;
    // Split into multi-byte characters
    $chars = mb_str_split($command);

    // Windows special characters to escape with '^'
    $windows_special_chars = ['%', '!', '"', '\'', '#', '&', ';', '`', '|', '*', '?', '~', '<', '>', '^', '(', ')', '[', ']', '{', '}', '$', '\\', "\x0A", "\xFF"];

    // Unix-like special characters to escape with '\'
    $unix_special_chars = ['#', '&', ';', '`', '|', '*', '?', '~', '<', '>', '^', '(', ')', '[', ']', '{', '}', '$', '\\', "\x0A", "\xFF"];

    // Lookup tables for quick lookups
    $windows_special_lookup = array_flip($windows_special_chars);
    $unix_special_lookup    = array_flip($unix_special_chars);

    foreach ($chars as $char) {
        // Check for null byte
        if ($char === "\x00") {
            throw new \ValueError('escapeshellcmd_os(): Argument #1 ($command) must not contain any null bytes');
        }

        // Check if the character is a multi-byte character
        $is_multibyte = strlen($char) > 1;

        // Check for single-byte characters from \x80 to \xFF
        if (!$is_multibyte) {
            $ord = ord($char);
            if ($ord >= 0x80) {
                if ($windows) {
                    if ($char === "\xFF") {
                        // On Windows, escape \xFF with a single caret
                        $escaped .= '^' . $char;

                        continue;
                    }
                    if ($char === "\xA0") {
                        // On Windows, \xA0 (non-breaking space) is valid
                        $escaped .= $char;

                        continue;
                    }
                    // Other characters in \x80 to \xFE range are valid on Windows
                    $escaped .= $char;
                }

                // On Linux, ignore characters in \x80 to \xFF range
                continue;
            }
        } elseif (!$windows) {
            $is_valid_utf8 = mb_check_encoding($char, 'UTF-8');
            // If not valid UTF-8, skip it
            if (!$is_valid_utf8) {
                continue;
            }
        }

        if ($windows) {
            if (isset($windows_special_lookup[$char])) {
                $escaped .= '^' . $char;
            } else {
                $escaped .= $char;
            }
        } else {
            if (isset($unix_special_lookup[$char])) {
                $escaped .= '\\' . $char;
            } elseif ($char === '"' || $char === '\'') {
                if ($inQuote === null) {
                    // Check if there's a matching quote ahead
                    $pos = mb_strpos($command, $char, mb_strpos($command, $char) + 1);
                    if ($pos !== false) {
                        // Paired quote found
                        $inQuote = $char;
                        $escaped .= $char;
                    } else {
                        // Unpaired quote, escape it
                        $escaped .= '\\' . $char;
                    }
                } elseif ($inQuote === $char) {
                    // Closing quote
                    $inQuote = null;
                    $escaped .= $char;
                } else {
                    // Inside a different quote, escape it
                    $escaped .= '\\' . $char;
                }
            } else {
                $escaped .= $char;
            }
        }
    }

    return $escaped;
}

/**
 * Wrapper for escapeshellcmd_os() that defaults to Linux escaping.
 *
 * @param string $command The command to escape.
 *
 * @return string The escaped command.
 *
 * @see escapeshellcmd_os()
 */
function escapeshellcmd_linux(string $command): string
{
    return escapeshellcmd_os($command, false);
}

/**
 * Wrapper for escapeshellcmd_os() that defaults to Windows escaping.
 *
 * @param string $command The command to escape.
 *
 * @return string The escaped command.
 *
 * @see escapeshellcmd_os()
 */
function escapeshellcmd_windows(string $command): string
{
    return escapeshellcmd_os($command, true);
}

/**
 * Escapes a string for use in a shell command.
 *
 * Since escapeshellarg() works differently on Windows and Linux, this function attempts to mimic
 * the behavior of escapeshellarg() on a specified operating system.
 *
 * @param string $arg     The argument to be escaped.
 * @param ?bool  $windows Whether to escape for Windows. If null, detects the current OS.
 *
 * @return string The escaped argument.
 *
 * @link https://github.com/php/php-src/blob/master/ext/standard/exec.c
 */
function escapeshellarg_os(string $arg, ?bool $windows = null): string
{
    // Determine if we're on Windows if $windows is null
    if ($windows === null) {
        $windows = PHP_OS_FAMILY === 'Windows';
    }

    // Split the argument into multi-byte characters
    $chars = mb_str_split($arg);

    $cmd = '';

    if ($windows) {
        // Start with a double quote on Windows
        $cmd .= '"';

        foreach ($chars as $char) {
            // Check for null byte
            if ($char === "\x00") {
                throw new \ValueError('escapeshellarg_os(): Argument #1 ($arg) must not contain any null bytes');
            }

            // Replace specific characters with a space
            if ($char === '"' || $char === '%' || $char === '!') {
                $cmd .= ' ';
            } else {
                $cmd .= $char;
            }
        }

        // Handle trailing backslashes
        $len = mb_strlen($cmd);
        if ($len > 1 && mb_substr($cmd, $len - 1) === '\\') {
            $k = 0;
            $n = $len - 1;
            while ($n >= 0 && mb_substr($cmd, $n, 1) === '\\') {
                $k++;
                $n--;
            }
            if ($k % 2 === 1) {
                $cmd .= '\\';
            }
        }

        // End with a double quote
        $cmd .= '"';
    } else {
        // Start with a single quote on non-Windows systems
        $cmd .= "'";

        foreach ($chars as $char) {
            // Check for null byte
            if ($char === "\x00") {
                throw new \ValueError('escapeshellarg_os(): Argument #1 ($arg) must not contain any null bytes');
            }

            // Check if the character is a multi-byte character
            $is_multibyte = strlen($char) > 1;

            // Skip processing for multi-byte characters that are not valid UTF-8
            if ($is_multibyte) {
                $is_valid_utf8 = mb_check_encoding($char, 'UTF-8');
                // Only add the character if it's valid UTF-8
                if ($is_valid_utf8) {
                    $cmd .= $char;
                }

                continue;
            }

            // Skip characters with ord >= 0x80 on non-Windows
            $ord = ord($char);
            if ($ord >= 0x80) {
                continue;
            }

            // Escape single quotes
            if ($char === "'") {
                $cmd .= "'\\''";
            } else {
                $cmd .= $char;
            }
        }

        // End with a single quote
        $cmd .= "'";
    }

    return $cmd;
}

/**
 * Wrapper for escapeshellarg_os() that defaults to Linux escaping.
 *
 * @param string $arg The argument to be escaped.
 *
 * @return string The escaped argument.
 *
 * @see escapeshellarg_os()
 */
function escapeshellarg_linux(string $arg): string
{
    return escapeshellarg_os($arg, false);
}

/**
 * Wrapper for escapeshellarg_os() that defaults to Windows escaping.
 *
 * @param string $arg The argument to be escaped.
 *
 * @return string The escaped argument.
 *
 * @see escapeshellarg_os()
 */
function escapeshellarg_windows(string $arg): string
{
    return escapeshellarg_os($arg, true);
}

/**
 * Get the first nameserver IP from WSL's resolv.conf file.
 *
 * @return string|null The first nameserver IP if found, null otherwise
 */
function resolv_conf_nameserver_ip(): ?string
{
    $result = shell_exec("grep nameserver /etc/resolv.conf | awk '{print $2}'");

    if ($result === null || $result === false) {
        return null;
    }

    $ip = trim($result);

    return $ip !== '' ? $ip : null;
}

/**
 * Convert a URL to be WSL-aware by replacing 'localhost' with the Windows host IP
 * when running under WSL (Windows Subsystem for Linux).
 *
 * @param string $url The URL that may need WSL awareness
 *
 * @return string The WSL-aware URL, or original URL if not in WSL
 */
function wsl_url(string $url): string
{
    // Only modify URL if running in WSL
    if (PHP_OS_FAMILY === 'Linux' && getenv('WSL_DISTRO_NAME')) {
        // Get Windows host IP from WSL's resolv.conf
        $nameserver = resolv_conf_nameserver_ip();

        if ($nameserver) {
            // Replace localhost with the Windows host IP, preserving any port number
            return preg_replace(
                '/localhost(:\d+)?/',
                $nameserver . '$1',
                $url
            );
        }
    }

    return $url;
}
