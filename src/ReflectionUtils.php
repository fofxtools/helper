<?php

declare(strict_types=1);

namespace FOfX\Helper;

/**
 * A class for extracting parameters from callables using Reflection.
 */
class ReflectionUtils
{
    /**
     * Get the name of a callable.
     *
     * @param callable|string|array $callable The callable to get the name of
     *
     * @return string The name of the callable
     */
    public static function getCallableName(callable|string|array $callable): string
    {
        return is_string($callable) ? $callable :
            (is_array($callable) ? (is_object($callable[0]) ? get_class($callable[0]) : $callable[0]) . '::' . $callable[1] :
            (is_object($callable) ? get_class($callable) . '::__invoke' : 'unknown'));
    }

    /**
     * Get a reflection object from a callable.
     *
     * @param callable|string|array $callable The callable to get the reflection of
     *
     * @throws \InvalidArgumentException If a required parameter is missing or callable format is invalid
     *
     * @return \ReflectionFunctionAbstract The reflection object
     */
    public static function getReflectionFromCallable(callable|string|array $callable): \ReflectionFunctionAbstract
    {
        $callableName = self::getCallableName($callable);

        return match (true) {
            is_array($callable)                                   => new \ReflectionMethod($callable[0], $callable[1]),
            is_string($callable) && str_contains($callable, '::') => (function () use ($callable, $callableName) {
                [$class, $method] = explode('::', $callable, 2);

                try {
                    return new \ReflectionMethod($class, $method);
                } catch (\ReflectionException $e) {
                    throw new \InvalidArgumentException(
                        "Invalid callable '{$callableName}': {$e->getMessage()}",
                        0,
                        $e
                    );
                }
            })(),
            is_object($callable) && method_exists($callable, '__invoke') => new \ReflectionMethod($callable, '__invoke'),
            $callable instanceof \Closure                                => new \ReflectionFunction($callable),
            is_string($callable)                                         => new \ReflectionFunction($callable),
            default                                                      => throw new \InvalidArgumentException("Unsupported callable type: '{$callableName}'"),
        };
    }

    /**
     * Get named arguments from a reflection object.
     *
     * @param \ReflectionFunctionAbstract $reflection    The reflection object
     * @param array                       $vars          The variables to extract from
     * @param array                       $excludeParams The parameters to exclude from the result
     * @param bool                        $boundOnly     If true, only include arguments present in $vars
     * @param string                      $callableName  The name of the callable
     *
     * @throws \InvalidArgumentException If a required parameter is missing or callable format is invalid
     *
     * @return array The named arguments
     */
    public static function getNamedArgs(\ReflectionFunctionAbstract $reflection, array $vars, array $excludeParams = [], bool $boundOnly = false, string $callableName = 'anonymous_function'): array
    {
        $namedArgs = [];
        $params    = $reflection->getParameters();

        foreach ($params as $param) {
            $name = $param->getName();

            if (in_array($name, $excludeParams, true)) {
                continue;
            }

            if (array_key_exists($name, $vars)) {
                $value      = $vars[$name];
                $type       = $param->getType();
                $isNullable = $type instanceof \ReflectionNamedType && $type->allowsNull();

                if ($boundOnly) {
                    if ($value !== null || !$isNullable) {
                        $namedArgs[$name] = $value;
                    }
                } else {
                    $namedArgs[$name] = $value;
                }
            } elseif ($boundOnly) {
                // In strict mode, throw if required parameter is missing
                if (!$param->isOptional()) {
                    throw new \InvalidArgumentException("Missing required parameter '\${$name}' for {$callableName}()");
                }
            } else {
                // In lenient mode, fill with default or null
                if ($param->isDefaultValueAvailable()) {
                    $namedArgs[$name] = $param->getDefaultValue();
                } else {
                    $namedArgs[$name] = null;
                }
            }
        }

        return $namedArgs;
    }

    /**
     * Get a backtrace frame.
     *
     * @param int $depth The depth of the frame to get
     *
     * @throws \InvalidArgumentException If the frame cannot be found
     *
     * @return array The backtrace frame
     */
    public static function getBacktraceFrame(int $depth = 1): array
    {
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

        return $frame;
    }

    /**
     * Get a callable from a backtrace frame.
     *
     * @param array $frame The backtrace frame
     *
     * @throws \InvalidArgumentException If the callable cannot be determined from the frame
     *
     * @return callable|string|array The callable
     */
    public static function getCallableFromBacktraceFrame(array $frame): callable|string|array
    {
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

        return $callable;
    }

    /**
     * Map positional arguments to named arguments.
     *
     * @param \ReflectionFunctionAbstract $reflection The reflection object
     * @param array                       $vars       The variables to extract from
     *
     * @return array The named arguments
     */
    public static function mapPositionalArgsToNamedArgs(\ReflectionFunctionAbstract $reflection, array $vars): array
    {
        $named = [];
        foreach ($reflection->getParameters() as $i => $param) {
            if (array_key_exists($i, $vars)) {
                $named[$param->getName()] = $vars[$i];
            }
        }

        return $named;
    }

    /**
     * Extract argument values from a function or method, optionally enforcing binding rules.
     *
     * @param callable|string|array $callable      Function name, closure, invokable object, [$object, 'method'], 'Class::method' or __METHOD__
     * @param array                 $vars          Associative array of local variables (typically from get_defined_vars())
     * @param array                 $excludeParams Parameter names to exclude from result
     * @param bool                  $boundOnly     If true, only include arguments present in $vars; otherwise include all, using defaults or nulls
     *
     * @return array Associative array of parameter name => value
     */
    public static function extractArgs(
        callable|string|array $callable,
        array $vars,
        array $excludeParams = [],
        bool $boundOnly = false
    ): array {
        $callableName = self::getCallableName($callable);
        $reflection   = self::getReflectionFromCallable($callable);
        $args         = self::getNamedArgs($reflection, $vars, $excludeParams, $boundOnly, $callableName);

        return $args;
    }

    /**
     * Extract bound argument values from a function or method, skipping nulls for nullable parameters.
     *
     * @param callable|string|array $callable      Function name, closure, invokable object, [$object, 'method'], 'Class::method' or __METHOD__
     * @param array                 $vars          Associative array of local variables (typically from get_defined_vars())
     * @param array                 $excludeParams Parameter names to exclude from result
     *
     * @return array Associative array of parameter name => value
     */
    public static function extractBoundArgs(callable|string|array $callable, array $vars, array $excludeParams = []): array
    {
        $callableName = self::getCallableName($callable);
        $reflection   = self::getReflectionFromCallable($callable);
        $args         = self::getNamedArgs($reflection, $vars, $excludeParams, true, $callableName);

        return $args;
    }

    /**
     * Inspect the call-stack, resolve the caller's signature, and return arguments.
     *
     * @param int   $depth         How far up the stack to look (1 = immediate caller)
     * @param array $excludeParams Parameter names to exclude from result
     * @param bool  $boundOnly     If true, include only arguments actually supplied; otherwise include all (using defaults/nulls)
     *
     * @return array Associative array of parameter name => value
     */
    public static function extractArgsFromBacktrace(int $depth = 1, array $excludeParams = [], bool $boundOnly = false): array
    {
        // Adjust depth to account for the getBacktraceFrame call
        $depth++;
        $frame        = self::getBacktraceFrame($depth);
        $callable     = self::getCallableFromBacktraceFrame($frame);
        $callableName = self::getCallableName($callable);
        $reflection   = self::getReflectionFromCallable($callable);
        $frameArgs    = $frame['args'] ?? [];
        $vars         = self::mapPositionalArgsToNamedArgs($reflection, $frameArgs);
        $args         = self::getNamedArgs($reflection, $vars, $excludeParams, $boundOnly, $callableName);

        return $args;
    }

    /**
     * Extract bound argument values from the call-stack
     *
     * @param int   $depth         How far up the stack to look (1 = immediate caller)
     * @param array $excludeParams Parameter names to exclude from result
     *
     * @return array Associative array of parameter name => value
     */
    public static function extractBoundArgsFromBacktrace(int $depth = 1, array $excludeParams = []): array
    {
        // Adjust depth to account for the getBacktraceFrame call
        $depth++;
        $frame        = self::getBacktraceFrame($depth);
        $callable     = self::getCallableFromBacktraceFrame($frame);
        $callableName = self::getCallableName($callable);
        $reflection   = self::getReflectionFromCallable($callable);
        $frameArgs    = $frame['args'] ?? [];
        $vars         = self::mapPositionalArgsToNamedArgs($reflection, $frameArgs);
        $args         = self::getNamedArgs($reflection, $vars, $excludeParams, true, $callableName);

        return $args;
    }
}
