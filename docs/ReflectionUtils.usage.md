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

function apiRequest(string $endpoint, ?array $params = null, bool $debug = false)
{
    $requestParams = ReflectionUtils::extractBoundArgs(
        __FUNCTION__,
        get_defined_vars(),
        ['debug']
    );

    print_r($requestParams);
}

class Demo
{
    public function someMethod(string $required, ?string $optional = null, array $options = [])
    {
        $params = ReflectionUtils::extractBoundArgs(
            __METHOD__,
            get_defined_vars(),
            ['options']
        );

        print_r($params);
    }
}

// Run examples
apiRequest('/users', null, true);
// Output: ['endpoint' => '/users']
// Note: Since $params is null and 'debug' is excluded, they won't be included in the result

(new Demo())->someMethod('abc', 'xyz');
// Output: ['required' => 'abc', 'optional' => 'xyz']
// Note: null values for nullable parameters are automatically excluded
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

class BacktraceDemo
{
    protected const EXCLUDED_ARGUMENTS = ['debug', 'extra'];

    protected function extractParamsHelper(array $extraExclude = []): array
    {
        $exclude = array_merge(self::EXCLUDED_ARGUMENTS, $extraExclude);
        $params  = ReflectionUtils::extractBoundArgsFromBacktrace(2, $exclude);

        return $params;
    }

    public function run(string $query, ?string $lang = null, bool $debug = false, array $extra = []): void
    {
        $params = ReflectionUtils::extractBoundArgs(
            __METHOD__,
            get_defined_vars(),
            self::EXCLUDED_ARGUMENTS
        );

        print_r($params);
    }

    public function runWithHelper(string $query, ?string $lang = null, bool $debug = false, array $extra = []): void
    {
        $params = $this->extractParamsHelper();

        print_r($params);
    }
}

$backtraceDemo = new BacktraceDemo();
$backtraceDemo->run('pizza', 'en', true, ['extra' => 'test']);
// Output: [['query' => 'pizza', 'lang' => 'en']]
$backtraceDemo->runWithHelper('pizza', 'en', true, ['extra' => 'test']);
// Output: [['query' => 'pizza', 'lang' => 'en']]
```

Key features:
- Works with any depth in the call stack
- Automatically handles both functions and methods
- Throws exceptions for invalid depths or inaccessible frames
- Preserves parameter names and types
- Useful for debugging and logging 