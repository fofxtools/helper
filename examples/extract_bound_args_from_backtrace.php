<?php

declare(strict_types=1);

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
