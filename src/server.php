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
 * @return  string                       The valid IP address of the client
 *
 * @throws  \UnexpectedValueException    If the IP address is invalid
 */
function get_remote_addr(): string
{
    // Headers in order of priority
    $ip_headers = [
        'HTTP_CLIENT_IP',
        'HTTP_X_FORWARDED_FOR',
        'REMOTE_ADDR'
    ];

    $remote_addr = null;

    // Check headers and use the first header that is valid
    foreach ($ip_headers as $header) {
        if (!empty($_SERVER[$header])) {
            $remote_addr = $_SERVER[$header];

            // If multiple IPs, take the first one
            if (strpos($remote_addr, ',') !== false) {
                $ip_array = explode(',', $remote_addr);
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
        throw new \UnexpectedValueException("Invalid IP address provided.");
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
 * @param   bool                       $use_forwarded_host  Whether to prioritize HTTP_X_FORWARDED_HOST if available (default: false)
 * @return  string                                          The HTTP host
 * @throws  \UnexpectedValueException                       If the host is invalid
 */
function get_http_host(bool $use_forwarded_host = false): string
{
    $host = null;

    if ($use_forwarded_host && !empty($_SERVER['HTTP_X_FORWARDED_HOST'])) {
        $hosts = explode(',', $_SERVER['HTTP_X_FORWARDED_HOST']);
        $host = trim($hosts[0]);
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
    $domain = $host_parts[0];
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
 * @param   string|null       $osFamily  The OS family to use (for testing purposes).
 * @return  string                       The home directory of the current user.
 * @throws  RuntimeException             If unable to determine the home directory.
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
 * @param   string|null        $osFamily  The OS family to use (for testing purposes).
 * @return  string
 * @throws  \RuntimeException             If called on a non-Windows system or unable to determine the home directory.
 */
function get_windows_home_directory(?string $osFamily = null): string
{
    $osFamily = $osFamily ?? PHP_OS_FAMILY;

    if ($osFamily !== 'Windows') {
        throw new \RuntimeException("get_windows_home_directory() can only be called on Windows systems.");
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

    throw new \RuntimeException("Unable to determine home directory on Windows.");
}

/**
 * Retrieve the home directory for Unix-like systems.
 * 
 * @param   string|null        $osFamily  The OS family to use (for testing purposes).
 *                                        $_SERVER['HOME'] is undefined from the browser, so use exec("echo ~") instead.
 *                                        $_SERVER['HOME'] and exec("echo ~") will not work on CyberPanel, I believe due to OpenLiteSpeed.
 *                                        So use posix_getpwuid(posix_getuid()) and $user['dir'] instead.
 *
 * @return  string
 * @throws  \RuntimeException             If called on a Windows system or unable to determine the home directory.
 */
function get_unix_home_directory(?string $osFamily = null): string
{
    $osFamily = $osFamily ?? PHP_OS_FAMILY;

    if ($osFamily === 'Windows') {
        throw new \RuntimeException("get_unix_home_directory() can only be called on Unix-like systems.");
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

    throw new \RuntimeException("Unable to determine home directory on Unix-like system.");
}

/**
 * Get the filename from a valid URL.
 * 
 * @link     https://stackoverflow.com/questions/7852296/get-only-filename-from-url-in-php-without-any-variable-values-which-exist-in-the
 * 
 * @param    string       $url  The URL to parse.
 * @return   string|null        The filename, or null if the URL is invalid.
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
 * @param    string       $url  The URL to extract the file extension from.
 * @return   string|null        The file extension, or null if no extension is found.
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
 * @param   int|false                  $pid              Optional process ID for Linux.
 * @param   ?callable                  $commandExecutor  A callable that executes the shell command. For testing.
 *                                                       Defaults to shell_exec.
 * @return  array                                        An associative array with network interfaces as keys, and their stats.
 *
 * @throws  \RuntimeException                            If unable to fetch network statistics.
 * @throws  \InvalidArgumentException                    If an invalid process ID is provided.
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
 * @param   ?callable          $commandExecutor  A callable that executes the shell command. For testing.
 * @return  array                                An associative array of network stats.
 *
 * @throws  \RuntimeException                    If unable to retrieve network statistics.
 */
function get_windows_network_stats(?callable $commandExecutor = null): array
{
    // Default to 'shell_exec' if no callable is provided
    $commandExecutor = $commandExecutor ?? 'shell_exec';

    $output = $commandExecutor("netstat -e");
    if ($output === false) {
        throw new \RuntimeException("Failed to retrieve network statistics for Windows.");
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
 * @param   int|false                  $pid              Optional process ID.
 * @param   ?callable                  $commandExecutor  A callable that executes the shell command. For testing.
 * @return  array                                        An associative array of network stats.
 *
 * @throws  \InvalidArgumentException                    If an invalid process ID is provided.
 * @throws  \RuntimeException                            If unable to retrieve network statistics.
 */
function get_linux_network_stats(int|false $pid = false, ?callable $commandExecutor = null): array
{
    // Default to 'shell_exec' if no callable is provided
    $commandExecutor = $commandExecutor ?? 'shell_exec';

    $file = $pid ? "/proc/$pid/net/dev" : "/proc/net/dev";
    $output = $commandExecutor("cat $file");

    if ($output === false) {
        throw new \RuntimeException("Failed to retrieve network statistics for Linux.");
    }

    $lines = explode("\n", $output);
    $stats = [];

    foreach ($lines as $line) {
        if (strpos($line, ':') !== false) {
            $split = preg_split('/\s+/', trim($line));
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
 * @return  void
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
 * @return  void
 */
function print_php_constants(): void
{
    echo "__LINE__ : " . __LINE__ . PHP_EOL;
    echo "__FILE__ : " . __FILE__ . PHP_EOL;
    echo "__DIR__ : " . __DIR__ . PHP_EOL;
    echo "__FUNCTION__ : " . __FUNCTION__ . PHP_EOL;
    echo "__CLASS__ : " . (__CLASS__ ?: 'N/A') . PHP_EOL;
    echo "__TRAIT__ : " . (__TRAIT__ ?: 'N/A') . PHP_EOL;
    echo "__METHOD__ : " . __METHOD__ . PHP_EOL;
    echo "__NAMESPACE__ : " . __NAMESPACE__ . PHP_EOL;
}

/**
 * Prints file system path information.
 *
 * @return  void
 */
function print_path_info(): void
{
    $paths = [
        "dirname('.')" => dirname('.'),
        'dirname(__FILE__)' => dirname(__FILE__),
        'getcwd()' => getcwd(),
    ];

    foreach ($paths as $label => $path) {
        echo "$label: $path\n";
    }
}

/**
 * Prints server-specific diagnostic information.
 *
 * @return  void
 */
function print_server_variables(): void
{
    echo "get_remote_addr(): " . get_remote_addr() . PHP_EOL;
    echo "get_http_host(): " . get_http_host() . PHP_EOL;
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
 * @param   string  $name  The name of the array to print information about
 * @param   array   $arr   The array to analyze
 * @return  void
 *
 * @see     recursive_implode
 */
function print_array_info(string $name, array $arr): void
{
    echo "count(\$$name) : " . count($arr) . PHP_EOL;
    echo "strlen(recursive_implode(\$$name)) : " . strlen(recursive_implode($arr)) . PHP_EOL;
}

/**
 * Escapes a string to be used as a shell argument on Windows.
 *
 * This function properly escapes special characters, including double quotes,
 * so that the argument is passed correctly to Windows CLI commands.
 *
 * @param   string  $arg  The argument to escape.
 * @return  string        The escaped argument.
 */
function escapeshellarg_windows(string $arg): string
{
    // Enclose the argument in double quotes
    $escaped = '"';
    $length = strlen($arg);
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
 * (`escapeshellarg_windows`), while on Unix-like systems, it uses the native `escapeshellarg` function.
 *
 * @param   string  $arg  The argument to escape.
 * @return  string        The escaped argument, suitable for safe usage in shell commands.
 */
function escapeshellarg_crossplatform(string $arg): string
{
    if (PHP_OS_FAMILY === 'Windows') {
        return escapeshellarg_windows($arg);
    } else {
        return escapeshellarg($arg);
    }
}
