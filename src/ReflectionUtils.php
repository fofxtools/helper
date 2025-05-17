<?php

declare(strict_types=1);

/**
 * Utility class for extracting parameters from callables using Reflection.
 */
final class ReflectionUtils
{
    /**
     * Extract bound argument values from a function or method, skipping nulls for nullable parameters.
     *
     * @param callable|string|array $callable      Function name, closure, invokable object, [$object, 'method'], 'Class::method' or __METHOD__
     * @param array                 $vars          Associative array of local variables (typically from get_defined_vars())
     * @param array                 $excludeParams Parameter names to exclude from result
     *
     * @throws \ReflectionException      If the function or method doesn't exist
     * @throws \InvalidArgumentException If a required parameter is missing or callable format is invalid
     *
     * @return array Associative array of parameter name => value
     */
    public static function extractBoundArgs(callable|string|array $callable, array $vars, array $excludeParams = []): array
    {
        $reflection = match (true) {
            is_array($callable)                                   => new \ReflectionMethod($callable[0], $callable[1]),
            is_string($callable) && str_contains($callable, '::') => (function () use ($callable) {
                [$class, $method] = explode('::', $callable, 2);

                try {
                    return new \ReflectionMethod($class, $method);
                } catch (\ReflectionException $e) {
                    throw new \InvalidArgumentException(
                        "Invalid callable '{$callable}': {$e->getMessage()}",
                        0,
                        $e
                    );
                }
            })(),
            is_string($callable)                                         => new \ReflectionFunction($callable),
            $callable instanceof \Closure                                => new \ReflectionFunction($callable),
            is_object($callable) && method_exists($callable, '__invoke') => new \ReflectionMethod($callable, '__invoke'),
            default                                                      => throw new \InvalidArgumentException('Unsupported callable type')
        };

        $callableName = is_string($callable) ? $callable :
            (is_array($callable) ? (is_object($callable[0]) ? get_class($callable[0]) : $callable[0]) . '::' . $callable[1] :
            (is_object($callable) ? get_class($callable) . '::__invoke' : 'unknown'));

        $out = [];

        foreach ($reflection->getParameters() as $param) {
            $name = $param->getName();

            if (in_array($name, $excludeParams, true)) {
                continue;
            }

            if (!array_key_exists($name, $vars)) {
                if (!$param->isOptional()) {
                    throw new \InvalidArgumentException("Missing required parameter '\${$name}' for {$callableName}()");
                }

                continue;
            }

            $value      = $vars[$name];
            $type       = $param->getType();
            $isNullable = $type instanceof \ReflectionNamedType && $type->allowsNull();

            // Only include if not null, or if the param is not nullable
            if ($value !== null || !$isNullable) {
                $out[$name] = $value;
            }
        }

        return $out;
    }
}
