<?php

declare(strict_types=1);

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
