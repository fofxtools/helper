<?php

/**
 * Memory Management Utility Functions
 *
 * This file provides functions for memory management and reporting.
 * It includes utilities for setting memory limits, retrieving memory usage information,
 * and performing memory-related operations across different operating systems.
 *
 * Key features:
 * - Memory limit management
 * - Cross-platform memory usage reporting
 * - Memory size conversion and formatting
 */

namespace FOfX\Helper;

/**
 * Sets the memory limit to the maximum value.
 * 
 * @return  void
 */
function set_memory_max(): void
{
    ini_set("memory_limit", -1);
}

/**
 * Modify memory limit if less than the minimum value
 *
 * @link    https://stackoverflow.com/questions/10208698/checking-memory-limit-in-php
 *
 * @param   string|int         $min  Minimum memory limit (e.g., '2048M' or bytes as integer)
 * @return  void
 *
 * @throws  \RuntimeException        If unable to set the new memory limit
 * @see     convert_to_bytes
 */
function minimum_memory_limit(string|int $min = '2048M'): void
{
    // Convert minimum value to bytes
    $minBytes = convert_to_bytes($min);

    // Get current memory limit and convert to bytes
    $currentLimit = convert_to_bytes(ini_get('memory_limit'));

    // If memory limit is less than the minimum (and not negative), increase it
    if ($currentLimit >= 0 && $currentLimit < $minBytes) {
        // Convert minimum bytes back to a string representation (in MB)
        $newLimit = ceil($minBytes / (1024 * 1024)) . 'M';
        if (ini_set('memory_limit', $newLimit) === false) {
            throw new \RuntimeException("Failed to set new memory limit: $newLimit");
        }
    }
}

/**
 * Calculate the memory usage of a variable
 * 
 * Based on Adam's getMemorySize() function
 * 
 * This function attempts to measure the memory usage of a given variable.
 * Note that the result is an approximation and may not be exact due to
 * PHP's memory management and garbage collection.
 * 
 * @link    https://stackoverflow.com/questions/2192657/how-to-determine-the-memory-footprint-size-of-a-variable
 * 
 * @param   mixed  $value  The variable whose memory usage is to be calculated.
 * @return  int            The approximate memory usage of the variable in bytes.
 */
function get_memory_size(mixed $value): int
{
    // existing variable with integer value so that the next line
    // does not add memory consumption when initiating $start variable
    $initial_memory = 1;
    $initial_memory = memory_get_usage();

    // json functions return less bytes consumptions than serialize
    $encoded_value = json_decode(json_encode($value));

    // Ensure we don't return a negative value due to potential garbage collection
    return max(0, memory_get_usage() - $initial_memory);
}

/**
 * Get memory information for the current system
 *
 * @param   bool        $format  Whether to format the memory sizes
 * @return  array                An array containing memory information
 *
 * @throws  \Exception           If the OS family is not supported
 * @see     get_windows_memory_info
 * @see     get_linux_memory_info
 * @see     get_macos_memory_info
 * @see     format_memory_info
 */
function get_memory_info(bool $format = false): array
{
    $os_family = PHP_OS_FAMILY;
    $memory_info = [];

    switch ($os_family) {
        case 'Windows':
            $memory_info = get_windows_memory_info();
            break;
        case 'Linux':
            $memory_info = get_linux_memory_info();
            break;
        case 'Darwin':
            $memory_info = get_macos_memory_info();
            break;
        default:
            throw new \Exception("Unsupported OS family: $os_family");
    }

    return $format ? format_memory_info($memory_info) : $memory_info;
}

/**
 * Get memory information for Windows systems
 *
 * @return  array         An array containing Windows memory information
 *
 * @throws  \Exception    If wmic command fails
 * @see     parse_windows_memory_output
 * @see     calculate_windows_memory_info
 */
function get_windows_memory_info(): array
{
    // Execute wmic command and get output
    $output = shell_exec('wmic OS get TotalVirtualMemorySize,TotalVisibleMemorySize,FreeVirtualMemory,FreePhysicalMemory,SizeStoredInPagingFiles,FreeSpaceInPagingFiles /format:list');

    // Handle error
    if ($output === false || $output === null) {
        throw new \Exception("Failed to retrieve memory information on Windows.");
    }

    $memory_info = parse_windows_memory_output($output);
    return calculate_windows_memory_info($memory_info);
}

/**
 * Parse Windows memory output from wmic command
 *
 * @param   string  $output  The output from the wmic command
 * @return  array            An array of parsed memory values
 */
function parse_windows_memory_output(string $output): array
{
    $memory_info = [
        'TotalVirtualMemorySize' => 0,
        'TotalVisibleMemorySize' => 0,
        'FreeVirtualMemory' => 0,
        'FreePhysicalMemory' => 0,
        'SizeStoredInPagingFiles' => 0,
        'FreeSpaceInPagingFiles' => 0,
    ];

    // Parse wmic output
    foreach (explode(PHP_EOL, $output) as $line) {
        foreach ($memory_info as $key => $value) {
            if (strpos($line, $key . '=') !== false) {
                $memory_info[$key] = (int)trim(str_replace($key . '=', '', $line));
            }
        }
    }

    return $memory_info;
}

/**
 * Calculate Windows memory information from parsed values
 *
 * @param   array  $memory_info  The parsed memory information
 * @return  array                Calculated memory and swap information
 */
function calculate_windows_memory_info(array $memory_info): array
{
    // Calculate swap information
    $swapTotal = $memory_info['SizeStoredInPagingFiles'];
    $swapFree = $memory_info['FreeSpaceInPagingFiles'];
    $swapUsed = $swapTotal - $swapFree;

    // Ensure non-negative values
    $swapTotal = max(0, $swapTotal);
    $swapFree = max(0, $swapFree);
    $swapUsed = max(0, $swapUsed);

    // Ensure SwapFree and SwapUsed don't exceed SwapTotal
    $swapFree = min($swapFree, $swapTotal);
    $swapUsed = min($swapUsed, $swapTotal);

    // Calculate memory and swap information
    return [
        'MemTotal' => $memory_info['TotalVisibleMemorySize'],
        'MemFree' => $memory_info['FreePhysicalMemory'],
        'MemAvailable' => $memory_info['FreePhysicalMemory'],
        'SwapTotal' => $swapTotal,
        'SwapFree' => $swapFree,
        'SwapUsed' => $swapUsed,
    ];
}

/**
 * Get memory information for Linux systems
 * 
 * file_get_contents() may give open_basedir restriction errors
 * so try shell_exec() if file_get_contents() fails.
 * 
 * If using CyberPanel, and fopen(). To prevent errors like:
 *      Warning: fopen(): open_basedir restriction in effect. File(/proc/meminfo) is not within the allowed path(s): (/tmp:/home/[snip]/:/usr/local/lsws/share/autoindex)
 *      Warning: fopen(/proc/meminfo): Failed to open stream: Operation not permitted
 * 
 * Go to CyberPanel dashboard>List Websites>Select the website>Manage>vHost Conf
 * And edit the phpIniOverride { php_admin_value open_basedir } value.
 * Edit the /usr/local/CyberCP/plogical/vhost.py and the two lines containing /tmp:$VH_ROOT to make this change the default for all new accounts
 * 
 * @link    https://stackoverflow.com/questions/1455379/get-server-ram-with-php
 *
 * @return  array         An array containing Linux memory information
 *
 * @throws  \Exception    If /proc/meminfo cannot be read
 */
function get_linux_memory_info(): array
{
    // Try file_get_contents first
    $contents = @file_get_contents("/proc/meminfo");

    // If file_get_contents fails, try shell_exec
    if ($contents === false) {
        $contents = shell_exec("cat /proc/meminfo");
    }

    // If both methods fail, throw an exception
    if ($contents === false || $contents === null) {
        throw new \Exception("Failed to retrieve memory information on Linux.");
    }

    $lines = explode("\n", $contents);
    $memory_info = [
        'MemTotal' => 0,
        'MemFree' => 0,
        'MemAvailable' => 0,
        'SwapTotal' => 0,
        'SwapFree' => 0,
    ];

    // Parse /proc/meminfo
    foreach ($lines as $line) {
        foreach ($memory_info as $key => $value) {
            // Convert kB to bytes
            if (preg_match('/^' . $key . ':\s+(\d+)\skB$/', $line, $matches)) {
                $memory_info[$key] = (int) $matches[1] * 1024;
            }
        }
    }

    return $memory_info;
}

/**
 * Get memory information for macOS systems
 *
 * @return  array         An array containing macOS memory information
 *
 * @throws  \Exception    If vm_stat command fails
 * @see     get_macos_total_memory
 * @see     get_macos_vm_stat
 */
function get_macos_memory_info(): array
{
    $memory_info = [
        'MemTotal' => 0,
        'MemFree' => 0,
        'MemAvailable' => 0,
        'SwapTotal' => 0,
        'SwapFree' => 0,
    ];

    // Get total memory and vm_stat info
    $memory_info['MemTotal'] = get_macos_total_memory();
    $vm_stat_info = get_macos_vm_stat();

    // Calculate memory and swap information
    if ($vm_stat_info['page_size'] > 0) {
        $memory_info['MemFree'] = $vm_stat_info['mem_free'] * $vm_stat_info['page_size'];
        $memory_info['MemAvailable'] = ($vm_stat_info['mem_free'] + $vm_stat_info['mem_inactive']) * $vm_stat_info['page_size'];
        $memory_info['SwapTotal'] = $vm_stat_info['swap_total'] * $vm_stat_info['page_size'];
        $memory_info['SwapFree'] = $vm_stat_info['swap_free'] * $vm_stat_info['page_size'];
    } else {
        throw new \Exception("Failed to calculate memory information on macOS.");
    }

    return $memory_info;
}

/**
 * Get total memory for macOS
 *
 * @return  int           The total memory in bytes
 *
 * @throws  \Exception    If sysctl command fails
 * @throws  \Exception    If unable to parse total memory information
 */
function get_macos_total_memory(): int
{
    // Get total memory using sysctl
    $output = [];
    exec('sysctl hw.memsize', $output);

    // Handle error
    if (empty($output)) {
        throw new \Exception("Failed to retrieve total memory on macOS.");
    }

    foreach ($output as $line) {
        if (preg_match('/^hw.memsize:\s+(\d+)$/', $line, $matches)) {
            return (int) $matches[1];
        }
    }

    throw new \Exception("Failed to parse total memory information on macOS.");
}

/**
 * Get vm_stat information for macOS
 *
 * @return  array         An array containing vm_stat information
 *
 * @throws  \Exception    If vm_stat command fails
 * @throws  \Exception    If unable to parse vm_stat information
 */
function get_macos_vm_stat(): array
{
    // Get vm_stat information
    $output = [];
    exec('vm_stat', $output);

    // Handle error
    if (empty($output)) {
        throw new \Exception("Failed to retrieve vm_stat information on macOS.");
    }

    $vm_stat_info = [
        'page_size' => 0,
        'mem_free' => 0,
        'mem_inactive' => 0,
        'swap_total' => 0,
        'swap_free' => 0,
    ];

    // Parse vm_stat output
    foreach ($output as $line) {
        if (preg_match('/page size of (\d+) bytes/', $line, $matches)) {
            $vm_stat_info['page_size'] = (int) $matches[1];
        }
        if (preg_match('/Pages free:\s+(\d+)\./', $line, $matches)) {
            $vm_stat_info['mem_free'] = (int) $matches[1];
        }
        if (preg_match('/Pages inactive:\s+(\d+)\./', $line, $matches)) {
            $vm_stat_info['mem_inactive'] = (int) $matches[1];
        }
        if (preg_match('/Anonymous pages:\s+(\d+)\./', $line, $matches)) {
            $vm_stat_info['swap_total'] += (int) $matches[1];
        }
        if (preg_match('/Pages swapped in:\s+(\d+)\./', $line, $matches)) {
            $vm_stat_info['swap_total'] += (int) $matches[1];
        }
        if (preg_match('/Pages swapped out:\s+(\d+)\./', $line, $matches)) {
            $vm_stat_info['swap_free'] += (int) $matches[1];
        }
    }

    if ($vm_stat_info['page_size'] === 0) {
        throw new \Exception("Failed to parse vm_stat information on macOS.");
    }

    return $vm_stat_info;
}

/**
 * Format memory information by converting bytes to human-readable format
 *
 * @param   array  $memory_info  The memory information to format
 * @return  array                The formatted memory information
 *
 * @see     format_bytes
 */
function format_memory_info(array $memory_info): array
{
    return array_map(__NAMESPACE__ . '\format_bytes', $memory_info);
}

/**
 * Get total memory.
 *
 * @param   bool             $format  Whether to format the memory size.
 * @return  int|string|null           Total memory (int if not formatted, string if formatted, or null if missing).
 *
 * @see     get_memory_info
 */
function get_mem_total(bool $format = false): int|string|null
{
    $memory_info = get_memory_info($format);
    return $memory_info['MemTotal'] ?? null;
}

/**
 * Get free memory.
 *
 * @param   bool             $format  Whether to format the memory size.
 * @return  int|string|null           Free memory (int if not formatted, string if formatted, or null if missing).
 *
 * @see     get_memory_info
 */
function get_mem_free(bool $format = false): int|string|null
{
    $memory_info = get_memory_info($format);
    return $memory_info['MemFree'] ?? null;
}

/**
 * Get available memory.
 *
 * @param   bool             $format  Whether to format the memory size.
 * @return  int|string|null           Available memory (int if not formatted, string if formatted, or null if missing).
 *
 * @see     get_memory_info
 */
function get_mem_available(bool $format = false): int|string|null
{
    $memory_info = get_memory_info($format);
    return $memory_info['MemAvailable'] ?? null;
}

/**
 * Get total swap.
 *
 * @param   bool             $format  Whether to format the memory size.
 * @return  int|string|null           Total swap (int if not formatted, string if formatted, or null if missing).
 *
 * @see     get_memory_info
 */
function get_swap_total(bool $format = false): int|string|null
{
    $memory_info = get_memory_info($format);
    return $memory_info['SwapTotal'] ?? null;
}

/**
 * Get free swap.
 *
 * @param   bool             $format  Whether to format the memory size.
 * @return  int|string|null           Free swap (int if not formatted, string if formatted, or null if missing).
 *
 * @see     get_memory_info
 */
function get_swap_free(bool $format = false): int|string|null
{
    $memory_info = get_memory_info($format);
    return $memory_info['SwapFree'] ?? null;
}

/**
 * Get the total memory and swap memory free.
 *
 * @param   bool             $get_available  Whether to get MemAvailable instead of MemFree.
 * @param   bool             $format         Whether to format the memory sizes.
 * @return  int|string|null                  The total free memory and swap memory size (or null if missing).
 *
 * @see     get_memory_info
 * @see     format_bytes
 */
function get_mem_free_total(bool $get_available = false, bool $format = false): int|string|null
{
    // Always get unformatted values
    $memory_info = get_memory_info(false);

    $mem_free_key = $get_available ? 'MemAvailable' : 'MemFree';

    // Ensure both values are present before summing
    if (!isset($memory_info[$mem_free_key]) || !isset($memory_info['SwapFree'])) {
        return null;
    }

    // Now that both are set, sum them
    $total_free = $memory_info[$mem_free_key] + $memory_info['SwapFree'];

    if ($format) {
        $total_free = format_bytes($total_free);
    }

    return $total_free;
}
