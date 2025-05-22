<?php

declare(strict_types=1);

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
