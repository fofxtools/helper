<?php

declare(strict_types=1);

require_once __DIR__ . '/../vendor/autoload.php';

use FOfX\Helper\ReflectionUtils;

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
