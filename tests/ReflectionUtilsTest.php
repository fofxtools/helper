<?php

declare(strict_types=1);

namespace FOfX\Helper;

use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\Attributes\DataProvider;

/**
 * Helper class for testing the ReflectionUtils class
 *
 * Define above ReflectionUtilsTest to avoid Intelephense errors
 */
class TestHelperClass
{
    public function methodWithRequiredParams(string $firstParam, string $secondParam): array
    {
        return [$firstParam, $secondParam];
    }

    public function methodWithNullableParams(string $name, ?string $email = null, ?int $age = null): array
    {
        $params = ['name' => $name];

        if ($email !== null) {
            $params['email'] = $email;
        }

        if ($age !== null) {
            $params['age'] = $age;
        }

        return $params;
    }

    public function methodWithMixedTypes(int $id, string $title, array $data = [], bool $isActive = false): array
    {
        return [
            'id'       => $id,
            'title'    => $title,
            'data'     => $data,
            'isActive' => $isActive,
        ];
    }

    public static function staticMethod(string $param): string
    {
        return $param;
    }

    /**
     * Static method that uses extractBoundArgsFromBacktrace
     */
    public static function staticMethodWithBacktrace(string $param): array
    {
        return ReflectionUtils::extractBoundArgsFromBacktrace(1);
    }
}

/**
 * Helper function for testing extractBoundArgs with functions
 *
 * Define above ReflectionUtilsTest to avoid Intelephense errors
 */
function testHelperFunction(string $name, ?int $age = null): array
{
    $params = ['name' => $name];

    if ($age !== null) {
        $params['age'] = $age;
    }

    return $params;
}

/**
 * Helper function for testing backtracing
 *
 * Define above ReflectionUtilsTest to avoid Intelephense errors
 */
function testHelperFunctionWithBacktrace(string $name, ?int $age = null): array
{
    return ReflectionUtils::extractBoundArgsFromBacktrace(1);
}

class ReflectionUtilsTest extends TestCase
{
    /**
     * Test class for method extraction
     */
    private $testClass;

    /**
     * Set up test fixtures
     */
    protected function setUp(): void
    {
        parent::setUp();
        $this->testClass = new TestHelperClass();
    }

    /**
     * Data provider for getCallableName test
     */
    public static function callableNameProvider(): array
    {
        $testClass = new TestHelperClass();
        $closure   = function () {};
        $invokable = new class () {
            public function __invoke()
            {
            }
        };

        return [
            'string function' => [
                $callable     = 'trim',
                $expectedName = 'trim',
            ],
            'array callable with object' => [
                $callable     = [$testClass, 'methodWithRequiredParams'],
                $expectedName = TestHelperClass::class . '::methodWithRequiredParams',
            ],
            'array callable with class name' => [
                $callable     = [TestHelperClass::class, 'staticMethod'],
                $expectedName = TestHelperClass::class . '::staticMethod',
            ],
            'string callable with class::method' => [
                $callable     = TestHelperClass::class . '::staticMethod',
                $expectedName = TestHelperClass::class . '::staticMethod',
            ],
            'closure' => [
                $callable     = $closure,
                $expectedName = \Closure::class . '::__invoke',
            ],
            'invokable object' => [
                $callable     = $invokable,
                $expectedName = get_class($invokable) . '::__invoke',
            ],
        ];
    }

    /**
     * Test getCallableName with various callable types
     */
    #[DataProvider('callableNameProvider')]
    public function testGetCallableName($callable, $expectedName): void
    {
        $result = ReflectionUtils::getCallableName($callable);
        $this->assertEquals($expectedName, $result);
    }

    /**
     * Data provider for getReflectionFromCallable test
     */
    public static function reflectionFromCallableProvider(): array
    {
        $testClass = new TestHelperClass();
        $closure   = function () {};
        $invokable = new class () {
            public function __invoke()
            {
            }
        };

        return [
            'string function' => [
                $callable     = 'trim',
                $expectedType = \ReflectionFunction::class,
            ],
            'array callable with object' => [
                $callable     = [$testClass, 'methodWithRequiredParams'],
                $expectedType = \ReflectionMethod::class,
            ],
            'array callable with class name' => [
                $callable     = [TestHelperClass::class, 'staticMethod'],
                $expectedType = \ReflectionMethod::class,
            ],
            'string callable with class::method' => [
                $callable     = TestHelperClass::class . '::staticMethod',
                $expectedType = \ReflectionMethod::class,
            ],
            'closure' => [
                $callable     = $closure,
                $expectedType = \ReflectionMethod::class,
            ],
            'invokable object' => [
                $callable     = $invokable,
                $expectedType = \ReflectionMethod::class,
            ],
        ];
    }

    /**
     * Test getReflectionFromCallable with various callable types
     */
    #[DataProvider('reflectionFromCallableProvider')]
    public function testGetReflectionFromCallable($callable, $expectedType): void
    {
        $reflection = ReflectionUtils::getReflectionFromCallable($callable);
        $this->assertInstanceOf($expectedType, $reflection);
    }

    /**
     * Data provider for invalid callable tests
     */
    public static function invalidCallableProvider(): array
    {
        return [
            'non-existent function' => [
                $callable         = 'nonExistentFunction',
                $exceptionClass   = \ReflectionException::class,
                $exceptionMessage = 'Function nonExistentFunction() does not exist',
            ],
            'non-existent class method' => [
                $callable         = 'NonExistentClass::method',
                $exceptionClass   = \InvalidArgumentException::class,
                $exceptionMessage = 'Class "NonExistentClass" does not exist',
            ],
            'non-existent method' => [
                $callable         = TestHelperClass::class . '::nonExistentMethod',
                $exceptionClass   = \InvalidArgumentException::class,
                $exceptionMessage = "Method FOfX\Helper\TestHelperClass::nonExistentMethod() does not exist",
            ],
        ];
    }

    /**
     * Test getReflectionFromCallable with invalid callables
     */
    #[DataProvider('invalidCallableProvider')]
    public function testGetReflectionFromCallableInvalid($callable, $exceptionClass, $exceptionMessage): void
    {
        $this->expectException($exceptionClass);
        $this->expectExceptionMessage($exceptionMessage);

        ReflectionUtils::getReflectionFromCallable($callable);
    }

    /**
     * Data provider for getNamedArgs test
     */
    public static function namedArgsProvider(): array
    {
        return [
            'all params provided' => [
                $vars          = ['name' => 'John', 'email' => 'john@example.com', 'age' => 30],
                $excludeParams = [],
                $boundOnly     = false,
                $expectedArgs  = ['name' => 'John', 'email' => 'john@example.com', 'age' => 30],
            ],
            'null values with boundOnly=true' => [
                $vars          = ['name' => 'Jane', 'email' => null, 'age' => null],
                $excludeParams = [],
                $boundOnly     = true,
                $expectedArgs  = ['name' => 'Jane'],
            ],
            'null values with boundOnly=false' => [
                $vars          = ['name' => 'Bob', 'email' => null, 'age' => null],
                $excludeParams = [],
                $boundOnly     = false,
                $expectedArgs  = ['name' => 'Bob', 'email' => null, 'age' => null],
            ],
            'with excludeParams' => [
                $vars          = ['name' => 'Alice', 'email' => 'alice@example.com', 'age' => 25],
                $excludeParams = ['age'],
                $boundOnly     = false,
                $expectedArgs  = ['name' => 'Alice', 'email' => 'alice@example.com'],
            ],
        ];
    }

    /**
     * Test getNamedArgs with various parameter configurations
     */
    #[DataProvider('namedArgsProvider')]
    public function testGetNamedArgs(array $vars, array $excludeParams, bool $boundOnly, array $expectedArgs): void
    {
        $reflection = new \ReflectionMethod($this->testClass, 'methodWithNullableParams');

        $result = ReflectionUtils::getNamedArgs(
            $reflection,
            $vars,
            $excludeParams,
            $boundOnly,
            'test_callable'
        );

        $this->assertEquals($expectedArgs, $result);
    }

    /**
     * Test getNamedArgs with missing required parameter
     */
    public function testGetNamedArgsMissingRequired(): void
    {
        $reflection = new \ReflectionMethod($this->testClass, 'methodWithRequiredParams');
        $vars       = ['firstParam' => 'value1']; // Missing secondParam

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Missing required parameter');

        ReflectionUtils::getNamedArgs(
            $reflection,
            $vars,
            [],
            true, // boundOnly = true will enforce required params
            'test_callable'
        );
    }

    /**
     * Test getBacktraceFrame at different depths
     */
    public function testGetBacktraceFrame(): void
    {
        // Create a deeper call stack to test backtrace properly
        $frame = $this->nestedBacktraceCall();

        // The frame should point to this test method
        $this->assertEquals(__FUNCTION__, $frame['function']);
        $this->assertEquals(__CLASS__, $frame['class']);
    }

    /**
     * Helper method for backtrace that calls another method
     */
    private function nestedBacktraceCall(): array
    {
        return $this->helperMethodForBacktraceFrame(3); // Use depth 3 to get testGetBacktraceFrame
    }

    /**
     * Helper method to test backtracing
     */
    private function helperMethodForBacktraceFrame(int $depth = 1): array
    {
        // Get frame at specified depth
        return ReflectionUtils::getBacktraceFrame($depth);
    }

    /**
     * Test getBacktraceFrame with invalid depth
     */
    public function testGetBacktraceFrameInvalidDepth(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        // Use a more flexible regex pattern that will match the error message regardless of the specific depth number
        $this->expectExceptionMessageMatches('/No stack frame at depth \d+ \(max depth: \d+\)/');

        ReflectionUtils::getBacktraceFrame(100);
    }

    /**
     * Data provider for getCallableFromBacktraceFrame test
     */
    public static function backtraceFrameProvider(): array
    {
        $testObj = new TestHelperClass();

        return [
            'instance method' => [
                $frame = [
                    'function' => 'methodWithRequiredParams',
                    'class'    => TestHelperClass::class,
                    'object'   => $testObj,
                ],
                $expectedCallable = [$testObj, 'methodWithRequiredParams'],
            ],
            'static method' => [
                $frame = [
                    'function' => 'staticMethod',
                    'class'    => TestHelperClass::class,
                ],
                $expectedCallable = TestHelperClass::class . '::staticMethod',
            ],
            'function' => [
                $frame = [
                    'function' => 'testHelperFunction',
                ],
                $expectedCallable = 'testHelperFunction',
            ],
        ];
    }

    /**
     * Test getCallableFromBacktraceFrame with different frame types
     */
    #[DataProvider('backtraceFrameProvider')]
    public function testGetCallableFromBacktraceFrame(array $frame, $expectedCallable): void
    {
        $callable = ReflectionUtils::getCallableFromBacktraceFrame($frame);

        if (is_array($expectedCallable) && is_array($callable)) {
            $this->assertSame($expectedCallable[0], $callable[0]);
            $this->assertEquals($expectedCallable[1], $callable[1]);
        } else {
            $this->assertEquals($expectedCallable, $callable);
        }
    }

    /**
     * Test getCallableFromBacktraceFrame with invalid frame
     */
    public function testGetCallableFromBacktraceFrameInvalid(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Unable to determine callable from stack frame');

        // Create an empty frame with the function key defined to avoid warnings
        ReflectionUtils::getCallableFromBacktraceFrame(['function' => null]);
    }

    /**
     * Test mapPositionalArgsToNamedArgs
     */
    public function testMapPositionalArgsToNamedArgs(): void
    {
        $reflection = new \ReflectionMethod($this->testClass, 'methodWithRequiredParams');
        $vars       = ['value1', 'value2'];

        $result = ReflectionUtils::mapPositionalArgsToNamedArgs($reflection, $vars);

        $expected = [
            'firstParam'  => 'value1',
            'secondParam' => 'value2',
        ];

        $this->assertEquals($expected, $result);
    }

    /**
     * Test mapPositionalArgsToNamedArgs with missing arguments
     */
    public function testMapPositionalArgsToNamedArgsWithMissingArgs(): void
    {
        $reflection = new \ReflectionMethod($this->testClass, 'methodWithMixedTypes');
        $vars       = [123, 'Test Title']; // Missing the last two arguments

        $result = ReflectionUtils::mapPositionalArgsToNamedArgs($reflection, $vars);

        $expected = [
            'id'    => 123,
            'title' => 'Test Title',
        ];

        $this->assertEquals($expected, $result);
    }

    /**
     * Data provider for testing method args
     */
    public static function methodArgsProvider(): array
    {
        return [
            'required params only' => [
                $methodName    = 'methodWithRequiredParams',
                $args          = ['firstParam' => 'value1', 'secondParam' => 'value2'],
                $excludeParams = [],
                $expected      = ['firstParam' => 'value1', 'secondParam' => 'value2'],
            ],
            'nullable params with values' => [
                $methodName    = 'methodWithNullableParams',
                $args          = ['name' => 'John', 'email' => 'john@example.com', 'age' => 30],
                $excludeParams = [],
                $expected      = ['name' => 'John', 'email' => 'john@example.com', 'age' => 30],
            ],
            'nullable params with nulls' => [
                $methodName    = 'methodWithNullableParams',
                $args          = ['name' => 'Jane', 'email' => null, 'age' => null],
                $excludeParams = [],
                $expected      = ['name' => 'Jane'], // null values for nullable params should be excluded
            ],
            'excluding params' => [
                $methodName    = 'methodWithNullableParams',
                $args          = ['name' => 'Bob', 'email' => 'bob@example.com', 'age' => 25],
                $excludeParams = ['age'],
                $expected      = ['name' => 'Bob', 'email' => 'bob@example.com'],
            ],
            'mixed types' => [
                $methodName    = 'methodWithMixedTypes',
                $args          = ['id' => 1, 'title' => 'Test', 'data' => ['key' => 'value'], 'isActive' => true],
                $excludeParams = [],
                $expected      = ['id' => 1, 'title' => 'Test', 'data' => ['key' => 'value'], 'isActive' => true],
            ],
        ];
    }

    /**
     * Data provider for testing extraction of arguments with boundOnly
     */
    public static function extractArgsProvider(): array
    {
        return [
            'boundOnly=true, required and optional params' => [
                $methodName    = 'methodWithMixedTypes',
                $args          = ['id' => 1, 'title' => 'Test Title'],
                $excludeParams = [],
                $boundOnly     = true,
                $expected      = ['id' => 1, 'title' => 'Test Title'], // Only supplied args included
            ],
            'boundOnly=false, required and optional params' => [
                $methodName    = 'methodWithMixedTypes',
                $args          = ['id' => 1, 'title' => 'Test Title'],
                $excludeParams = [],
                $boundOnly     = false,
                $expected      = ['id' => 1, 'title' => 'Test Title', 'data' => [], 'isActive' => false], // All params included with defaults
            ],
            'exclude some params' => [
                $methodName    = 'methodWithMixedTypes',
                $args          = ['id' => 1, 'title' => 'Test Title', 'data' => ['key' => 'value'], 'isActive' => true],
                $excludeParams = ['isActive'],
                $boundOnly     = false,
                $expected      = ['id' => 1, 'title' => 'Test Title', 'data' => ['key' => 'value']], // isActive excluded
            ],
        ];
    }

    /**
     * Test extractArgs with different boundOnly values
     */
    #[DataProvider('extractArgsProvider')]
    public function testExtractArgs(string $methodName, array $args, array $excludeParams, bool $boundOnly, array $expected): void
    {
        $result = ReflectionUtils::extractArgs(
            [$this->testClass, $methodName],
            $args,
            $excludeParams,
            $boundOnly
        );

        $this->assertEquals($expected, $result);
    }

    #[DataProvider('methodArgsProvider')]
    public function testExtractBoundArgsFromMethods(string $methodName, array $args, array $excludeParams, array $expected): void
    {
        $result = ReflectionUtils::extractBoundArgs(
            [$this->testClass, $methodName],
            $args,
            $excludeParams
        );

        $this->assertEquals($expected, $result);
    }

    /**
     * Data provider for testing function extraction
     */
    public static function functionArgsProvider(): array
    {
        return [
            'simple function' => [
                $args          = ['name' => 'John', 'age' => 30],
                $excludeParams = [],
                $expected      = ['name' => 'John', 'age' => 30],
            ],
            'with nulls' => [
                $args          = ['name' => 'Jane', 'age' => null],
                $excludeParams = [],
                $expected      = ['name' => 'Jane'], // null values for nullable params should be excluded
            ],
            'with exclusions' => [
                $args          = ['name' => 'Bob', 'age' => 25],
                $excludeParams = ['age'],
                $expected      = ['name' => 'Bob'],
            ],
        ];
    }

    #[DataProvider('functionArgsProvider')]
    public function testExtractBoundArgsFromFunctions(array $args, array $excludeParams, array $expected): void
    {
        $result = ReflectionUtils::extractBoundArgs(
            'FOfX\Helper\testHelperFunction',
            $args,
            $excludeParams
        );

        $this->assertEquals($expected, $result);
    }

    /**
     * Data provider for testing error cases
     */
    public static function errorCasesProvider(): array
    {
        return [
            'missing required param' => [
                $methodName               = 'methodWithRequiredParams',
                $args                     = ['firstParam' => 'value1'], // Missing secondParam
                $excludeParams            = [],
                $expectedException        = \InvalidArgumentException::class,
                $expectedExceptionMessage = "Missing required parameter '\$secondParam'",
            ],
            'invalid callable' => [
                $methodName               = 'nonExistentMethod',
                $args                     = [],
                $excludeParams            = [],
                $expectedException        = \ReflectionException::class,
                $expectedExceptionMessage = 'Method FOfX\Helper\TestHelperClass::nonExistentMethod() does not exist',
            ],
        ];
    }

    #[DataProvider('errorCasesProvider')]
    public function testExtractBoundArgsErrors(string $methodName, array $args, array $excludeParams, string $expectedException, string $expectedExceptionMessage): void
    {
        $this->expectException($expectedException);
        $this->expectExceptionMessage($expectedExceptionMessage);

        ReflectionUtils::extractBoundArgs(
            [$this->testClass, $methodName],
            $args,
            $excludeParams
        );
    }

    public function testExtractBoundArgsFromStaticMethod(): void
    {
        $result = ReflectionUtils::extractBoundArgs(
            [TestHelperClass::class, 'staticMethod'],
            ['param' => 'test'],
            []
        );

        $this->assertEquals(['param' => 'test'], $result);
    }

    public function testExtractBoundArgsFromStringClassMethod(): void
    {
        $result = ReflectionUtils::extractBoundArgs(
            'FOfX\Helper\TestHelperClass::staticMethod',
            ['param' => 'test'],
            []
        );

        $this->assertEquals(['param' => 'test'], $result);
    }

    /**
     * Data provider for testing __METHOD__ style callables
     */
    public static function methodStringProvider(): array
    {
        return [
            'static method with __METHOD__ style' => [
                $methodString  = 'FOfX\Helper\TestHelperClass::staticMethod',
                $args          = ['param' => 'test'],
                $excludeParams = [],
                $expected      = ['param' => 'test'],
            ],
            'instance method with __METHOD__ style' => [
                $methodString  = 'FOfX\Helper\TestHelperClass::methodWithNullableParams',
                $args          = ['name' => 'Alice', 'email' => 'alice@example.com', 'age' => 25],
                $excludeParams = [],
                $expected      = ['name' => 'Alice', 'email' => 'alice@example.com', 'age' => 25],
            ],
            'nullable params with __METHOD__ style' => [
                $methodString  = 'FOfX\Helper\TestHelperClass::methodWithNullableParams',
                $args          = ['name' => 'Bob', 'email' => null],
                $excludeParams = [],
                $expected      = ['name' => 'Bob'], // null email should be excluded
            ],
            'with exclusions in __METHOD__ style' => [
                $methodString  = 'FOfX\Helper\TestHelperClass::methodWithMixedTypes',
                $args          = ['id' => 123, 'title' => 'Test Title', 'data' => ['a' => 1], 'isActive' => true],
                $excludeParams = ['isActive', 'data'],
                $expected      = ['id' => 123, 'title' => 'Test Title'],
            ],
        ];
    }

    #[DataProvider('methodStringProvider')]
    public function testExtractBoundArgsWithMethodString(string $methodString, array $args, array $excludeParams, array $expected): void
    {
        $result = ReflectionUtils::extractBoundArgs(
            $methodString,
            $args,
            $excludeParams
        );

        $this->assertEquals($expected, $result);
    }

    /**
     * Data provider for testing invalid __METHOD__ style callables
     */
    public static function invalidMethodStringProvider(): array
    {
        return [
            'non-existent class' => [
                $methodString             = 'NonExistentClass::someMethod',
                $expectedException        = \InvalidArgumentException::class,
                $expectedExceptionMessage = 'Class "NonExistentClass" does not exist',
            ],
            'non-existent method' => [
                $methodString             = 'FOfX\Helper\TestHelperClass::nonExistentMethod',
                $expectedException        = \InvalidArgumentException::class,
                $expectedExceptionMessage = 'Method FOfX\Helper\TestHelperClass::nonExistentMethod() does not exist',
            ],
        ];
    }

    #[DataProvider('invalidMethodStringProvider')]
    public function testExtractBoundArgsWithInvalidMethodString(string $methodString, string $expectedException, string $expectedExceptionMessage): void
    {
        $this->expectException($expectedException);
        $this->expectExceptionMessage($expectedExceptionMessage);

        ReflectionUtils::extractBoundArgs(
            $methodString,
            ['param' => 'test'],
            []
        );
    }

    public function testExtractBoundArgsFromClosure(): void
    {
        $closure = function (string $param): string {
            return $param;
        };

        $result = ReflectionUtils::extractBoundArgs(
            $closure,
            ['param' => 'test'],
            []
        );

        $this->assertEquals(['param' => 'test'], $result);
    }

    /**
     * Tests the actual __METHOD__ magic constant usage
     * In a real method, __METHOD__ would be the actual method name
     */
    public function testExtractBoundArgsWithActualMethodConstant(): void
    {
        // Our current method is ReflectionUtilsTest::testExtractBoundArgsWithActualMethodConstant
        // But for testing purposes, we'll use a known class::method
        $mockMethodConstant = 'FOfX\Helper\TestHelperClass::staticMethod';

        $result = ReflectionUtils::extractBoundArgs(
            $mockMethodConstant, // This would normally be __METHOD__ in real usage
            ['param' => 'value from magic constant'],
            []
        );

        $this->assertEquals(['param' => 'value from magic constant'], $result);
    }

    /**
     * Test extractArgsFromBacktrace with customized boundOnly parameter
     */
    public function testExtractArgsFromBacktrace(): void
    {
        // Test with boundOnly = false (include all parameters)
        $result = $this->helperMethodForExtractArgsFromBacktrace('test-keyword', 42, false);

        // Should include all parameters with defaults
        $this->assertEquals([
            'keyword'   => 'test-keyword',
            'amount'    => 42,
            'boundOnly' => false,
        ], $result);

        // Test with boundOnly = true (include only supplied parameters)
        $result = $this->helperMethodForExtractArgsFromBacktrace('another-test', null, true);

        // Should only include non-null parameters and the boundOnly parameter itself
        $this->assertEquals([
            'keyword'   => 'another-test',
            'boundOnly' => true,  // boundOnly is included because it's not null
        ], $result);
    }

    /**
     * Helper method for testing extractArgsFromBacktrace
     */
    private function helperMethodForExtractArgsFromBacktrace(string $keyword, ?int $amount = null, bool $boundOnly = false): array
    {
        return ReflectionUtils::extractArgsFromBacktrace(1, [], $boundOnly);
    }

    /**
     * Test extractBoundArgsFromBacktrace with immediate caller
     */
    public function testExtractBoundArgsFromBacktraceImmediateCaller(): void
    {
        $result = $this->helperMethodWithBacktrace('test', 'email@example.com', 42);

        $this->assertEquals(['keyword' => 'test', 'locationName' => 'email@example.com'], $result);
    }

    /**
     * Test extractBoundArgsFromBacktrace with deeper caller
     */
    public function testExtractBoundArgsFromBacktraceDeeper(): void
    {
        $result = $this->wrapperForBacktraceTest();

        // This test expects the parameters passed from wrapperForBacktraceTest to deeperMethodWithBacktrace
        $this->assertEquals(['keyword' => 'deeper-test'], $result);
    }

    /**
     * Helper method that calls extractBoundArgsFromBacktrace
     */
    private function helperMethodWithBacktrace(string $keyword, ?string $locationName = null, int $amount = 1): array
    {
        return ReflectionUtils::extractBoundArgsFromBacktrace(1, ['amount']);
    }

    /**
     * Helper method that calls another method with backtrace
     */
    private function wrapperForBacktraceTest(): array
    {
        return $this->deeperMethodWithBacktrace('deeper-test');
    }

    /**
     * Helper method that calls extractBoundArgsFromBacktrace with depth=2
     */
    private function deeperMethodWithBacktrace(string $keyword, ?string $locationName = null): array
    {
        return ReflectionUtils::extractBoundArgsFromBacktrace(1);
    }

    /**
     * Test extractBoundArgsFromBacktrace with invalid depth
     */
    public function testExtractBoundArgsFromBacktraceInvalidDepth(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        // Use a more flexible regex pattern that will match the error message regardless of the specific depth number
        $this->expectExceptionMessageMatches('/No stack frame at depth \d+ \(max depth: \d+\)/');

        ReflectionUtils::extractBoundArgsFromBacktrace(100);
    }

    /**
     * Test static method call with extractBoundArgsFromBacktrace
     */
    public function testExtractBoundArgsFromBacktraceStaticMethod(): void
    {
        $result = TestHelperClass::staticMethodWithBacktrace('static-test');

        $this->assertEquals(['param' => 'static-test'], $result);
    }

    /**
     * Test extractBoundArgsFromBacktrace with function call
     */
    public function testExtractBoundArgsFromBacktraceFunction(): void
    {
        // Use call_user_func to avoid linter errors while still calling the function
        $result = call_user_func(
            __NAMESPACE__ . '\\testHelperFunctionWithBacktrace',
            'function-test',
            25
        );

        $this->assertEquals(['name' => 'function-test', 'age' => 25], $result);
    }
}
