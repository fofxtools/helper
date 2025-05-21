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
     * Data provider for testing extraction of arguments from methods
     *
     * @return array
     */
    public static function methodArgsProvider(): array
    {
        return [
            'required params only' => [
                'methodName'    => 'methodWithRequiredParams',
                'args'          => ['firstParam' => 'value1', 'secondParam' => 'value2'],
                'excludeParams' => [],
                'expected'      => ['firstParam' => 'value1', 'secondParam' => 'value2'],
            ],
            'nullable params with values' => [
                'methodName'    => 'methodWithNullableParams',
                'args'          => ['name' => 'John', 'email' => 'john@example.com', 'age' => 30],
                'excludeParams' => [],
                'expected'      => ['name' => 'John', 'email' => 'john@example.com', 'age' => 30],
            ],
            'nullable params with nulls' => [
                'methodName'    => 'methodWithNullableParams',
                'args'          => ['name' => 'Jane', 'email' => null, 'age' => null],
                'excludeParams' => [],
                'expected'      => ['name' => 'Jane'], // null values for nullable params should be excluded
            ],
            'excluding params' => [
                'methodName'    => 'methodWithNullableParams',
                'args'          => ['name' => 'Bob', 'email' => 'bob@example.com', 'age' => 25],
                'excludeParams' => ['age'],
                'expected'      => ['name' => 'Bob', 'email' => 'bob@example.com'],
            ],
            'mixed types' => [
                'methodName'    => 'methodWithMixedTypes',
                'args'          => ['id' => 1, 'title' => 'Test', 'data' => ['key' => 'value'], 'isActive' => true],
                'excludeParams' => [],
                'expected'      => ['id' => 1, 'title' => 'Test', 'data' => ['key' => 'value'], 'isActive' => true],
            ],
        ];
    }

    /**
     * Data provider for testing function extraction
     *
     * @return array
     */
    public static function functionArgsProvider(): array
    {
        return [
            'simple function' => [
                'args'          => ['name' => 'John', 'age' => 30],
                'excludeParams' => [],
                'expected'      => ['name' => 'John', 'age' => 30],
            ],
            'with nulls' => [
                'args'          => ['name' => 'Jane', 'age' => null],
                'excludeParams' => [],
                'expected'      => ['name' => 'Jane'], // null values for nullable params should be excluded
            ],
            'with exclusions' => [
                'args'          => ['name' => 'Bob', 'age' => 25],
                'excludeParams' => ['age'],
                'expected'      => ['name' => 'Bob'],
            ],
        ];
    }

    /**
     * Data provider for testing error cases
     *
     * @return array
     */
    public static function errorCasesProvider(): array
    {
        return [
            'missing required param' => [
                'methodName'               => 'methodWithRequiredParams',
                'args'                     => ['firstParam' => 'value1'], // Missing secondParam
                'excludeParams'            => [],
                'expectedException'        => \InvalidArgumentException::class,
                'expectedExceptionMessage' => "Missing required parameter '\$secondParam'",
            ],
            'invalid callable' => [
                'methodName'               => 'nonExistentMethod',
                'args'                     => [],
                'excludeParams'            => [],
                'expectedException'        => \ReflectionException::class,
                'expectedExceptionMessage' => 'Method FOfX\Helper\TestHelperClass::nonExistentMethod() does not exist',
            ],
        ];
    }

    /**
     * Data provider for testing __METHOD__ style callables
     *
     * @return array
     */
    public static function methodStringProvider(): array
    {
        return [
            'static method with __METHOD__ style' => [
                'methodString'  => 'FOfX\Helper\TestHelperClass::staticMethod',
                'args'          => ['param' => 'test'],
                'excludeParams' => [],
                'expected'      => ['param' => 'test'],
            ],
            'instance method with __METHOD__ style' => [
                'methodString'  => 'FOfX\Helper\TestHelperClass::methodWithNullableParams',
                'args'          => ['name' => 'Alice', 'email' => 'alice@example.com', 'age' => 25],
                'excludeParams' => [],
                'expected'      => ['name' => 'Alice', 'email' => 'alice@example.com', 'age' => 25],
            ],
            'nullable params with __METHOD__ style' => [
                'methodString'  => 'FOfX\Helper\TestHelperClass::methodWithNullableParams',
                'args'          => ['name' => 'Bob', 'email' => null],
                'excludeParams' => [],
                'expected'      => ['name' => 'Bob'], // null email should be excluded
            ],
            'with exclusions in __METHOD__ style' => [
                'methodString'  => 'FOfX\Helper\TestHelperClass::methodWithMixedTypes',
                'args'          => ['id' => 123, 'title' => 'Test Title', 'data' => ['a' => 1], 'isActive' => true],
                'excludeParams' => ['isActive', 'data'],
                'expected'      => ['id' => 123, 'title' => 'Test Title'],
            ],
        ];
    }

    /**
     * Data provider for testing invalid __METHOD__ style callables
     *
     * @return array
     */
    public static function invalidMethodStringProvider(): array
    {
        return [
            'non-existent class' => [
                'methodString'             => 'NonExistentClass::someMethod',
                'expectedException'        => \InvalidArgumentException::class,
                'expectedExceptionMessage' => 'Class "NonExistentClass" does not exist',
            ],
            'non-existent method' => [
                'methodString'             => 'FOfX\Helper\TestHelperClass::nonExistentMethod',
                'expectedException'        => \InvalidArgumentException::class,
                'expectedExceptionMessage' => 'Method FOfX\Helper\TestHelperClass::nonExistentMethod() does not exist',
            ],
        ];
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
        $this->expectExceptionMessage('No stack frame at depth 100');

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
        $result = testHelperFunctionWithBacktrace('function-test', 25);

        $this->assertEquals(['name' => 'function-test', 'age' => 25], $result);
    }
}
