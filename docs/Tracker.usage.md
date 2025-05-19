# Tracker Usage Guide

The `Tracker` class helps you monitor script execution time, memory usage, and bandwidth. It's perfect for performance monitoring and debugging.

## AutoStart Feature

You can enable automatic tracking by setting `autoStartTracker` to `true` in your `config/helper.config.php`:

```php
return [
    'autoStartTracker' => true,
    // ... other config options ...
];
```

When autoStart is enabled, you don't need to manually get the Tracker instance. The global Singleton will be initialized automatically during autoloading.

## Basic Usage

Here's a simple example of tracking script execution time:

```php
require_once __DIR__ . '/../vendor/autoload.php';

use FOfX\Helper\Tracker;

// Get the Tracker instance
$tracker = Tracker::getInstance();

// Start tracking a section
Tracker::scriptTimer("MySection", "start");

// Your code here...
sleep(1);

// End tracking
Tracker::scriptTimer("MySection", "end");

// Print the results
Tracker::trackerEnd();
```

## Multiple Sections

You can track multiple sections in your code:

```php
require_once __DIR__ . '/../vendor/autoload.php';

use FOfX\Helper\Tracker;

$tracker = Tracker::getInstance();

// Track database operations
Tracker::scriptTimer("Database", "start");
// ... database code ...
Tracker::scriptTimer("Database", "end");

// Track API calls
Tracker::scriptTimer("API", "start");
// ... API code ...
Tracker::scriptTimer("API", "end");

// Print all results
Tracker::trackerEnd();
```

## Bandwidth and Memory Tracking

Track bandwidth and memory usage changes:

```php
require_once __DIR__ . '/../vendor/autoload.php';

use FOfX\Helper\Tracker;

$tracker = Tracker::getInstance();

// Start memory tracking
Tracker::scriptTimer("MemoryCheck", "start");

// Your memory-intensive operations here...
$largeArray = range(1, 1000000);

// HTTP request
$url = 'https://www.example.com';
$response = file_get_contents($url);
$bytes = strlen($response);
echo "Bytes for $url: $bytes\n";

// End tracking
Tracker::scriptTimer("MemoryCheck", "end");

// Print results with $printAllArrays true
Tracker::trackerEnd(true);
```

## Example Output

When you call `trackerEnd()`, you'll see output similar to this:

```
Array
(
    [Main] => Array
        (
            [Start] => 1747692775.0152
            [End] => 1747692776.139
            [Elapsed] => 1.12378
        )

    [MySection] => Array
        (
            [Start] => 1747692775.0483
            [End] => 1747692776.0831
            [Elapsed] => 1.03484
        )

)
```

### printAllArrays - trackerEnd(true)

If you call `trackerEnd(true)` (with `$printAllArrays` passed as `true`) you'll see output similar to:

```
Array
(
    [Main] => Array
        (
            [timer] => Array
                (
                    [Start] => 1747692862.6643
                    [End] => 1747692862.9358
                    [Elapsed] => 0.27156
                )

            [bandwidth] => Array
                (
                    [Start] => 1.46 GB
                    [End] => 1.46 GB
                    [Net] => 32.36 KB
                )

            [memory] => Array
                (
                    [Start] => 2.07 MB
                    [End] => 20.07 MB
                    [Diff] => 18 MB
                )

            [peak memory] => Array
                (
                    [Start] => 2.27 MB
                    [End] => 20.09 MB
                    [Diff] => 17.82 MB
                )

        )

    [MemoryCheck] => Array
        (
            [timer] => Array
                (
                    [Start] => 1747692862.7043
                    [End] => 1747692862.8929
                    [Elapsed] => 0.18858
                )

            [bandwidth] => Array
                (
                    [Start] => 1.46 GB
                    [End] => 1.46 GB
                    [Net] => 31.3 KB
                )

            [memory] => Array
                (
                    [Start] => 2.07 MB
                    [End] => 20.07 MB
                    [Diff] => 18 MB
                )

            [peak memory] => Array
                (
                    [Start] => 2.27 MB
                    [End] => 20.09 MB
                    [Diff] => 17.82 MB
                )

        )

)
```

## Common Use Cases

1. **API Performance**: Track API call durations
2. **Database Queries**: Monitor query execution times
3. **Memory Leaks**: Track memory usage in long-running scripts
4. **Function Profiling**: Measure performance of specific functions
5. **Request Timing**: Track total request processing time 