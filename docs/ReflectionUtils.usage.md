# ReflectionUtils Usage Guide

The `ReflectionUtils` class provides utilities for extracting bound parameters from methods and functions.

## extractBoundArgs

See the full example in [examples/extract_bound_args.php](../examples/extract_bound_args.php)

Extracts named argument values from callables (methods, functions, closures), automatically:

- Enforcing required parameters
- Excluding `null` values for nullable parameters
- Letting you exclude parameters explicitly

```php
require_once __DIR__ . '/../vendor/autoload.php';

use FOfX\Helper\ReflectionUtils;

function apiRequest(string $endpoint, ?array $params = null, ?int $limit = null, bool $debug = false)
{
    $args = ReflectionUtils::extractArgs(
        __FUNCTION__,
        get_defined_vars(),
        ['debug']
    );

    echo "apiRequest - Args:\n";
    print_r($args);

    $boundArgs = ReflectionUtils::extractBoundArgs(
        __FUNCTION__,
        get_defined_vars(),
        ['debug']
    );

    echo "apiRequest - Bound args:\n";
    print_r($boundArgs);
}

class Demo
{
    public function someMethod(string $required, ?string $optional = null, ?int $limit = null, array $options = [])
    {
        $args = ReflectionUtils::extractArgs(
            __METHOD__,
            get_defined_vars(),
            ['options']
        );

        echo "someMethod - Args:\n";
        print_r($args);

        $boundArgs = ReflectionUtils::extractBoundArgs(
            __METHOD__,
            get_defined_vars(),
            ['options']
        );

        echo "someMethod - Bound args:\n";
        print_r($boundArgs);
    }
}

// Run examples
apiRequest('/users', null, null, true);
// Output:
// apiRequest - Args:
// [ 'endpoint' => '/users', 'params' => null, 'limit' => null ]
// apiRequest - Bound args:
// [ 'endpoint' => '/users' ]
// Note: 'params' and 'limit' are null and filtered from bound args; 'debug' is excluded by name

apiRequest('/users', ['abc']);
// Output:
// apiRequest - Args:
// [ 'endpoint' => '/users', 'params' => [...], 'limit' => null ]
// apiRequest - Bound args:
// [ 'endpoint' => '/users', 'params' => [...] ]

$demo = new Demo();
$demo->someMethod('abc', null, null, ['xyz']);
// Output:
// someMethod - Args:
// [ 'required' => 'abc', 'optional' => null, 'limit' => null ]
// someMethod - Bound args:
// [ 'required' => 'abc' ]
// Note: 'optional' and 'limit' are null and filtered from bound args; 'options' is excluded by name

$demo->someMethod('abc', '123');
// Output:
// someMethod - Args:
// [ 'required' => 'abc', 'optional' => '123', 'limit' => null ]
// someMethod - Bound args:
// [ 'required' => 'abc', 'optional' => '123' ]
// Note: 'limit' is null and filtered from bound args; 'options' is excluded by name
```

You can use either the `__METHOD__` magic constant or `[$this, __FUNCTION__]` for class methods.

Key features:
- Throws exceptions for missing required parameters
- Works with instance methods, static methods, functions, and closures
- Automatically excludes null values for nullable parameters
- Allows excluding parameters via the third argument
- Supports both `[$this, __FUNCTION__]` and `__METHOD__` formats

## extractBoundArgsFromBacktrace

See the full example in [examples/extract_bound_args_from_backtrace.php](../examples/extract_bound_args_from_backtrace.php)

Extracts bound arguments using PHP's backtrace system, allowing you to access parameters from any level in the call stack:

```php
require_once __DIR__ . '/../vendor/autoload.php';

use FOfX\Helper\ReflectionUtils;

class BacktraceDemo
{
    protected const EXCLUDED_ARGUMENTS = ['debug', 'extra'];

    protected function argsHelper(array $extraExclude = []): array
    {
        $exclude = array_merge(self::EXCLUDED_ARGUMENTS, $extraExclude);

        return ReflectionUtils::extractArgsFromBacktrace(2, $exclude);
    }

    protected function boundArgsHelper(array $extraExclude = []): array
    {
        $exclude = array_merge(self::EXCLUDED_ARGUMENTS, $extraExclude);

        return ReflectionUtils::extractBoundArgsFromBacktrace(2, $exclude);
    }

    public function run(string $query, ?string $lang = null, ?int $limit = null, bool $debug = false, array $extra = []): void
    {
        $params = ReflectionUtils::extractArgs(
            __METHOD__,
            get_defined_vars(),
            self::EXCLUDED_ARGUMENTS
        );

        echo "run() - Params:\n";
        print_r($params);

        $boundParams = ReflectionUtils::extractBoundArgs(
            __METHOD__,
            get_defined_vars(),
            self::EXCLUDED_ARGUMENTS
        );

        echo "run() - Bound params:\n";
        print_r($boundParams);
    }

    public function runWithArgsHelper(string $query, ?string $lang = null, ?int $limit = null, bool $debug = false, array $extra = []): void
    {
        $params = $this->argsHelper();

        print_r($params);
    }

    public function runWithBoundArgsHelper(string $query, ?string $lang = null, ?int $limit = null, bool $debug = false, array $extra = []): void
    {
        $params = $this->boundArgsHelper();

        print_r($params);
    }
}

// Run examples
$backtraceDemo = new BacktraceDemo();

echo "run()\n";
$backtraceDemo->run('pizza', null, null, true, ['extra' => 'test']);

echo "\nrunWithArgsHelper()\n";
$backtraceDemo->runWithArgsHelper('pizza', null, null, true, ['extra' => 'test']);

echo "\nrunWithBoundArgsHelper()\n";
$backtraceDemo->runWithBoundArgsHelper('pizza', null, null, true, ['extra' => 'test']);

echo "\nrun()\n";
$backtraceDemo->run('burger', null, 10, false, []);

echo "\nrunWithArgsHelper()\n";
$backtraceDemo->runWithArgsHelper('burger', null, 10, false, []);

echo "\nrunWithBoundArgsHelper()\n";
$backtraceDemo->runWithBoundArgsHelper('burger', null, 10, false, []);

/*
Expected Output:

run()
run() - Params:
Array
(
    [query] => pizza
    [lang] =>
    [limit] =>
)
run() - Bound params:
Array
(
    [query] => pizza
)
Explanation: 'query' is the only non-null, non-excluded parameter. 'lang' and 'limit' are null and filtered from bound params.

runWithArgsHelper()
Array
(
    [query] => pizza
    [lang] =>
    [limit] =>
)
Explanation: extractArgsFromBacktrace includes all parameters (even null/default values), excluding only 'debug' and 'extra'.

runWithBoundArgsHelper()
Array
(
    [query] => pizza
)
Explanation: Only non-null and non-excluded values are included.

run()
run() - Params:
Array
(
    [query] => burger
    [lang] =>
    [limit] => 10
)
run() - Bound params:
Array
(
    [query] => burger
    [limit] => 10
)
Explanation: 'lang' is still null, so it's skipped in bound params. 'limit' is now set and included.

runWithArgsHelper()
Array
(
    [query] => burger
    [lang] =>
    [limit] => 10
)
Explanation: All declared parameters are shown, including nulls. 'lang' is included even though it is null.

runWithBoundArgsHelper()
Array
(
    [query] => burger
    [limit] => 10
)
Explanation: Only 'query' and 'limit' are non-null and not filtered. 'lang' is null, so it's skipped.
*/
```

Key features:
- Works with any depth in the call stack
- Automatically handles both functions and methods
- Throws exceptions for invalid depths or inaccessible frames
- Preserves parameter names and types
- Useful for debugging and logging 

## Logging Example with Monolog

You can use `extractArgs()` and `extractBoundArgs()` with [Monolog](https://github.com/Seldaek/monolog) to automatically capture structured logs from your functions and methods.

Here’s a basic example that logs all parameters passed to a function or class method:

```php
require_once __DIR__ . '/../vendor/autoload.php';

use FOfX\Helper\ReflectionUtils;
use Monolog\Logger;
use Monolog\Handler\StreamHandler;
use Monolog\Level;

// Logger to stdout
$log = new Logger('reflection');
$log->pushHandler(new StreamHandler('php://stdout', Level::Debug));

// Test function
function demoFunction(string $name, ?int $age = null, bool $active = true): void
{
    $args  = ReflectionUtils::extractArgs(__FUNCTION__, get_defined_vars());
    $bound = ReflectionUtils::extractBoundArgs(__FUNCTION__, get_defined_vars());

    global $log;
    $log->info('extractArgs()', $args);
    $log->info('extractBoundArgs()', $bound);
}

// Test class
class Demo
{
    public function run(string $name, ?int $age = null, bool $active = true): void
    {
        $args  = ReflectionUtils::extractArgs(__METHOD__, get_defined_vars());
        $bound = ReflectionUtils::extractBoundArgs(__METHOD__, get_defined_vars());

        global $log;
        $log->info('extractArgs() from ' . __METHOD__, $args);
        $log->info('extractBoundArgs() from ' . __METHOD__, $bound);
    }
}

// Run tests
demoFunction('Alice');
demoFunction('Bob', 30, false);
demoFunction('Charlie', null, true);

$demo = new Demo();
$demo->run('Dana');
$demo->run('Eve', 25, false);
$demo->run('Frank', null, true);
```

### Sample Output

```
reflection.INFO: extractArgs(): {"name":"Alice","age":null,"active":true}
reflection.INFO: extractBoundArgs(): {"name":"Alice","active":true}
reflection.INFO: extractArgs() {"name":"Bob","age":30,"active":false} []
reflection.INFO: extractBoundArgs() {"name":"Bob","age":30,"active":false} []
reflection.INFO: extractArgs() from Demo::run: {"name":"Charlie","age":null,"active":true}
reflection.INFO: extractBoundArgs() from Demo::run: {"name":"Charlie","active":true}
reflection.INFO: extractArgs() from Demo::run {"name":"Dana","age":null,"active":true} []
reflection.INFO: extractBoundArgs() from Demo::run {"name":"Dana","active":true} []
reflection.INFO: extractArgs() from Demo::run {"name":"Eve","age":25,"active":false} []
reflection.INFO: extractBoundArgs() from Demo::run {"name":"Eve","age":25,"active":false} []
reflection.INFO: extractArgs() from Demo::run {"name":"Frank","age":null,"active":true} []
reflection.INFO: extractBoundArgs() from Demo::run {"name":"Frank","active":true} []

```

This allows you to dynamically and consistently log the values received by any callable with zero boilerplate.