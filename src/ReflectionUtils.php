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

    /**
     * Inspect the call-stack, resolve the caller's signature, and return bound arguments.
     * This automatically determines the calling method or function from the backtrace.
     *
     * @param int   $depth         How far up the stack to look (1 = immediate caller)
     * @param array $excludeParams Parameter names to exclude from result
     *
     * @throws \InvalidArgumentException If the trace depth is invalid or a callable cannot be built from the frame
     * @throws \ReflectionException      If reflection fails (bubbles up)
     *
     * @return array Associative array of parameter name => value
     */
    public static function extractBoundArgsFromBacktrace(int $depth = 1, array $excludeParams = []): array
    {
        // Get backtrace with object instances to properly build callables
        $trace = debug_backtrace(DEBUG_BACKTRACE_PROVIDE_OBJECT, $depth + 1);

        if (!isset($trace[$depth])) {
            $maxDepth  = count($trace) - 1;
            $available = array_map(function ($frame) {
                $func = $frame['function'];

                // Normalize closures and invokables for readability
                if ($func === '{closure}') {
                    $func = '[closure]';
                } elseif ($func === '__invoke') {
                    $func = '[__invoke]';
                }

                return isset($frame['class'])
                    ? $frame['class'] . '::' . $func
                    : $func;
            }, $trace);

            throw new \InvalidArgumentException(sprintf(
                'No stack frame at depth %d (max depth: %d). Available call stack: %s',
                $depth,
                $maxDepth,
                json_encode($available)
            ));
        }

        $frame = $trace[$depth];

        // Build callable from the frame information
        if (isset($frame['class'])) {
            // Method call (instance or static)
            if (isset($frame['object'])) {
                // Instance method call
                $callable = [$frame['object'], $frame['function']];
            } else {
                // Static method call
                $callable = $frame['class'] . '::' . $frame['function'];
            }
        } elseif ($frame['function']) {
            // Function, closure, or invokable object
            $callable = $frame['function'];
        } else {
            throw new \InvalidArgumentException(
                'Unable to determine callable from stack frame'
            );
        }

        // Map positional arguments to parameter names using reflection
        $reflection = is_array($callable)
            ? new \ReflectionMethod($callable[0], $callable[1])
            : (is_string($callable) && str_contains($callable, '::')
                ? (function () use ($callable) {
                    [$class, $method] = explode('::', $callable, 2);

                    return new \ReflectionMethod($class, $method);
                })()
                : new \ReflectionFunction($callable));

        $namedArgs = [];
        $params    = $reflection->getParameters();
        $args      = $frame['args'] ?? [];

        foreach ($params as $idx => $param) {
            $name = $param->getName();

            // Skip if caller didn't supply this argument
            if (!array_key_exists($idx, $args)) {
                continue;
            }

            $value      = $args[$idx];
            $type       = $param->getType();
            $isNullable = $type instanceof \ReflectionNamedType && $type->allowsNull();

            // Only include if not null, or if the param is not nullable
            if ($value !== null || !$isNullable) {
                $namedArgs[$name] = $value;
            }
        }

        // Delegate to the existing extractBoundArgs for final filtering and validation
        return self::extractBoundArgs(
            $callable,
            $namedArgs,
            $excludeParams
        );
    }
}
