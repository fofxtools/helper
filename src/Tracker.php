<?php

/**
 * Performance Tracking and Profiling Class
 *
 * This file defines the Tracker class, which provides functionality
 * for tracking execution time, memory usage, and other performance metrics.
 *
 * @package  FOfX\Helper
 */

namespace FOfX\Helper;

/**
 * A Singleton class for storing and managing performance metrics.
 * 
 * Key features:
 * - Script execution time tracking
 * - Memory usage monitoring
 * - Bandwidth usage tracking
 * - IP address checking and proxy handling
 * 
 * Usage:
 *      Tracker::scriptTimer("Main", "start");
 *      Tracker::scriptTimer("Main", "end");
 *      Tracker::trackerEnd();
 * 
 * Note: By default, it uses date_default_timezone_set() to set the time zone based on config.php.
 *       This can be modified by passing $setDefaultTimezone = false to the constructor.
 * 
 * Note: get_network_stats() contains bandwidth information for the whole server, not just the executing script.
 */
class Tracker
{
    // Singleton instance
    private static ?Tracker $instance = null;

    // Private properties
    private $calcBandwidth       = true;
    private $pid                 = false;
    private $trackMemory         = true;
    private $configFile          = '';
    private $setDefaultTimezone  = true;
    private $defaultTimezone     = null;
    private $checkIPs            = [];
    private $proxyIPs            = [];
    private $timerArray          = [];
    private $bandwidthArray      = [];
    private $currentMemoryArray  = [];
    private $peakMemoryArray     = [];
    private $dataArray           = [];
    private $configData          = null;

    /**
     * The constructor.
     * It is private to prevent direct instantiation, since it is a singleton.
     * Use Tracker::getInstance() to get the single global instance.
     * 
     * @param  bool     $calcBandwidth
     * @param  mixed    $pid
     * @param  bool     $trackMemory
     * @param  ?string  $configFile
     * @param  bool     $setDefaultTimezone
     */
    private function __construct(
        bool $calcBandwidth = true,
        mixed $pid = false,
        bool $trackMemory = true,
        ?string $configFile = 'config/config.php',
        bool $setDefaultTimezone = true
    ) {
        $this->calcBandwidth = $calcBandwidth;
        $this->pid = $pid;
        $this->trackMemory = $trackMemory;
        $this->setDefaultTimezone = $setDefaultTimezone;

        $this->configFile = resolve_config_file_path($configFile);
        $this->configData = $this->configFile ? load_config($this->configFile) : [];

        $this->applyConfigSettings();

        $this->scriptTimerImpl("Main", "start");
    }

    /**
     * Check if Singleton instance exists
     * 
     * @return  bool    Returns true if the Singleton instance exists, false otherwise
     */
    public static function isInitialized(): bool
    {
        return self::$instance !== null;
    }

    /**
     * Get the single instance of the Tracker class
     * 
     * @return  self
     */
    public static function getInstance(): self
    {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    /**
     * Reset the Singleton instance
     *
     * @return  void
     */
    public static function reset(): void
    {
        self::$instance = null;
    }

    /**
     * Static facade for scriptTimer method
     * 
     * @param   string  $scriptName
     * @param   string  $action
     * @return  void
     */
    public static function scriptTimer(string $scriptName, string $action = "start"): void
    {
        self::getInstance()->scriptTimerImpl($scriptName, $action);
    }

    /**
     * Implementation of scriptTimer method
     * 
     * @param   string  $scriptName
     * @param   string  $action
     * @return  void
     */
    private function scriptTimerImpl(string $scriptName, string $action = "start"): void
    {
        $action = strtolower($action);

        $this->timerArray[$scriptName][$action === 'end' ? 'End' : 'Start'] = microtime(true);

        $this->handleBandwidthTracking($scriptName, $action);
        $this->handleMemoryTracking($scriptName, $action);

        if ($action === 'end') {
            $this->timerArray[$scriptName]['Elapsed'] = round(
                $this->timerArray[$scriptName]['End'] - $this->timerArray[$scriptName]['Start'],
                5
            );
        }
    }

    /**
     * Applies the configuration settings to the current instance.
     * 
     * @return  void
     */
    private function applyConfigSettings(): void
    {
        if ($this->setDefaultTimezone && !empty($this->configData['tracker']['defaultTimezone'])) {
            $this->defaultTimezone = $this->configData['tracker']['defaultTimezone'];
            date_default_timezone_set($this->defaultTimezone);
        }
        if (!empty($this->configData['tracker']['checkIPs'])) {
            $this->checkIPs = explode(',', $this->configData['tracker']['checkIPs']);
        }
        if (!empty($this->configData['tracker']['proxyIPs'])) {
            $this->proxyIPs = explode(',', $this->configData['tracker']['proxyIPs']);
        }
    }

    /**
     * Tracks and records bandwidth usage for the script section.
     * 
     * @param   string  $scriptName  The name of the script section.
     * @param   string  $action      Specifies whether this starts or ends the timer.
     * @return  void
     * @see     get_network_stats
     */
    private function handleBandwidthTracking(string $scriptName, string $action): void
    {
        if (!$this->calcBandwidth) {
            return;
        }

        $networkStats = get_network_stats($this->pid);
        $firstKey = array_key_first($networkStats);

        $this->bandwidthArray[$scriptName][$action === 'end' ? 'End' : 'Start'] =
            $networkStats[$firstKey]['Receive'] + $networkStats[$firstKey]['Transmit'];

        if ($action === 'end') {
            $this->bandwidthArray[$scriptName]['Net'] =
                $this->bandwidthArray[$scriptName]['End'] - $this->bandwidthArray[$scriptName]['Start'];
        }
    }

    /**
     * Tracks and records memory usage for the script section.
     * 
     * @param   string  $scriptName  The name of the script section.
     * @param   string  $action      Specifies whether this starts or ends the timer.
     * @return  void
     */
    private function handleMemoryTracking(string $scriptName, string $action): void
    {
        if (!$this->trackMemory) {
            return;
        }

        $currentMemory = memory_get_usage();
        $peakMemory = memory_get_peak_usage();

        $this->currentMemoryArray[$scriptName][$action === 'end' ? 'End' : 'Start'] = $currentMemory;
        $this->peakMemoryArray[$scriptName][$action === 'end' ? 'End' : 'Start'] = $peakMemory;

        if ($action === 'end') {
            $this->currentMemoryArray[$scriptName]['Diff'] =
                $this->currentMemoryArray[$scriptName]['End'] - $this->currentMemoryArray[$scriptName]['Start'];
            $this->peakMemoryArray[$scriptName]['Diff'] =
                $this->peakMemoryArray[$scriptName]['End'] - $this->peakMemoryArray[$scriptName]['Start'];
        }
    }

    /**
     * Static facade for scriptTimerElapsed method
     * 
     * @param   string  $scriptName  The name of the script section.
     * @return  float                The elapsed time in seconds, rounded to 5 decimal places.
     */
    public static function scriptTimerElapsed(string $scriptName = 'Main'): float
    {
        return self::getInstance()->scriptTimerElapsedImpl($scriptName);
    }

    /**
     * Implementation of scriptTimerElapsed method
     * 
     * @param   string                     $scriptName  The name of the script section.
     * @return  float                                   The elapsed time in seconds, rounded to 5 decimal places.
     * @throws  \InvalidArgumentException
     */
    private function scriptTimerElapsedImpl(string $scriptName = 'Main'): float
    {
        if (!isset($this->timerArray[$scriptName]['Start'])) {
            throw new \InvalidArgumentException("Invalid script name or uninitialized timer: {$scriptName}");
        }

        return round(microtime(true) - $this->timerArray[$scriptName]['Start'], 5);
    }

    /**
     * Static facade for scriptBandwidthUsage method
     * 
     * @param   string  $scriptName  The name of the script section.
     * @return  int                  The net bandwidth used in bytes.
     */
    public static function scriptBandwidthUsage(string $scriptName = 'Main'): int
    {
        return self::getInstance()->scriptBandwidthUsageImpl($scriptName);
    }

    /**
     * Implementation of scriptBandwidthUsage method
     * 
     * @param   string                     $scriptName  The name of the script section.
     * @return  int                                     The net bandwidth used in bytes.
     * @throws  \InvalidArgumentException
     */
    private function scriptBandwidthUsageImpl(string $scriptName = 'Main'): int
    {
        if (!isset($this->bandwidthArray[$scriptName]['Start'])) {
            throw new \InvalidArgumentException("Invalid script name or uninitialized bandwidth tracker: {$scriptName}");
        }

        $networkStats = get_network_stats($this->pid);
        $firstKey = array_key_first($networkStats);

        return $networkStats[$firstKey]['Receive'] + $networkStats[$firstKey]['Transmit']
            - $this->bandwidthArray[$scriptName]['Start'];
    }

    /**
     * Static facade for createDataArray method
     * 
     * @param   bool  $formatBytes  Indicates whether to format the bytes values.
     * @return  void
     */
    public static function createDataArray(bool $formatBytes = true): void
    {
        self::getInstance()->createDataArrayImpl($formatBytes);
    }

    /**
     * Implementation of createDataArray method
     * 
     * @param   bool  $formatBytes  Indicates whether to format the bytes values.
     * @return  void
     */
    private function createDataArrayImpl(bool $formatBytes = true): void
    {
        $combinedArray = [];
        $scriptNames = array_keys($this->timerArray);

        foreach ($scriptNames as $name) {
            $combinedArray[$name]['timer'] = $this->timerArray[$name];

            $this->processDataArray($combinedArray[$name], 'bandwidth', $this->bandwidthArray, $name, $formatBytes);
            $this->processDataArray($combinedArray[$name], 'memory', $this->currentMemoryArray, $name, $formatBytes);
            $this->processDataArray($combinedArray[$name], 'peak memory', $this->peakMemoryArray, $name, $formatBytes);
        }

        $this->dataArray = $combinedArray;
    }

    /**
     * Process and format a specific data array if it exists.
     * 
     * @param   array   $combinedArray  The combined array being built.
     * @param   string  $key            The key under which the data should be stored.
     * @param   array   $sourceArray    The source array to check and format.
     * @param   string  $name           The script name key.
     * @param   bool    $formatBytes    Indicates whether to format the bytes values.
     * @return  void
     */
    private function processDataArray(array &$combinedArray, string $key, array $sourceArray, string $name, bool $formatBytes): void
    {
        if (isset_array($sourceArray[$name])) {
            $combinedArray[$key] = $formatBytes ? format_bytes_array($sourceArray[$name]) : $sourceArray[$name];
        }
    }

    /**
     * Static facade for printData method
     * 
     * @param   bool  $formatBytes  Indicates whether to format the bytes values.
     * @return  void
     */
    public static function printData(bool $formatBytes = true): void
    {
        self::getInstance()->printDataImpl($formatBytes);
    }

    /**
     * Implementation of printData method
     * 
     * @param   bool  $formatBytes  Indicates whether to format the bytes values.
     * @return  void
     */
    private function printDataImpl(bool $formatBytes = true): void
    {
        $this->createDataArrayImpl($formatBytes);
        print_r($this->dataArray);
    }

    /**
     * End main Tracker section and print
     * 
     * @param   bool  $printAllArrays  Whether to print all arrays
     * @param   bool  $formatBytes     Whether to format bytes
     * @return  void
     */
    public static function trackerEnd(bool $printAllArrays = false, bool $formatBytes = true): void
    {
        $tracker = self::getInstance();
        $tracker->scriptTimerImpl("Main", "end");
        if ($printAllArrays) {
            $tracker->printDataImpl($formatBytes);
        } else {
            print_r($tracker->timerArray);
        }
    }

    /**
     * End timer and print bandwidth
     * 
     * @return  void
     */
    public static function bandwidthEnd(): void
    {
        $tracker = self::getInstance();
        $tracker->scriptTimerImpl("Main", "end");
        print_r($tracker->bandwidthArray);
    }

    /**
     * Throw new Exception with the given message and Tracker scriptTimer info
     * 
     * @param   string      $message  The status message to pass to new Exception
     * @return  void
     * @throws  \Exception
     */
    public static function throwTracker(string $message = ''): void
    {
        $tracker = self::getInstance();
        $tracker->scriptTimerImpl("Main", "end");
        $message .= PHP_EOL . print_r($tracker->timerArray, true);
        throw new \Exception($message);
    }

    /**
     * Get the checkIPs property
     *
     * @return  array    The array of IP addresses to check
     */
    public static function getCheckIPs(): array
    {
        return self::getInstance()->checkIPs;
    }

    /**
     * Get the proxyIPs property
     *
     * @return  array    The array of proxy IP addresses
     */
    public static function getProxyIPs(): array
    {
        return self::getInstance()->proxyIPs;
    }

    /**
     * Check if any of the given IP address(es) are in the checkIPs array
     * Checks current remote address by default
     * 
     * @param   string|array|null  $ips               The IP address(es) to check. If null, it uses the current remote address.
     *                                                Can be a string for a single IP or an array for multiple IPs.
     * @param   bool               $includeLocalhost  If true, automatically adds '127.0.0.1' to the checkIPs before validation.
     * @return  bool                                  Returns true if any given IP is in checkIPs (and optionally localhost), false otherwise.
     * @see     get_remote_addr
     */
    public static function inCheckIPs($ips = null, bool $includeLocalhost = true): bool
    {
        $instance = self::getInstance();

        $checkIPs = $instance->checkIPs;
        if ($includeLocalhost) {
            $checkIPs[] = '127.0.0.1';
        }

        if ($ips === null) {
            $ips = get_remote_addr();
        }

        // Convert to array if it's a string
        $ips = (array)$ips;

        foreach ($ips as $ip) {
            // If any IP is in the list, return true
            if (in_array($ip, $checkIPs, true)) {
                return true;
            }
        }

        // No IPs are in the list
        return false;
    }
}
