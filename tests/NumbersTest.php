<?php

declare(strict_types=1);

namespace FOfX\Helper\Tests;

use FOfX\Helper\Numbers;
use PHPUnit\Framework\TestCase;

/**
 * Test case for the Numbers class.
 */
class NumbersTest extends TestCase
{
    /**
     * @var Numbers
     */
    private Numbers $numbers;

    /**
     * Set up the test environment.
     */
    protected function setUp(): void
    {
        $this->numbers = new Numbers();
    }

    /**
     * Test convertIntegerToWords with various integer inputs.
     *
     * @dataProvider integerProvider
     */
    public function testConvertIntegerToWords(int $input, string $expected): void
    {
        $this->assertEquals($expected, $this->numbers->convertIntegerToWords($input));
    }

    /**
     * Data provider for integer conversion tests.
     *
     * @return array
     */
    public static function integerProvider(): array
    {
        return [
            [0, 'zero'],
            [1, 'one'],
            [21, 'twenty-one'],
            [100, 'one hundred'],
            [118, 'one hundred eighteen'],
            [200, 'two hundred'],
            [219, 'two hundred nineteen'],
            [800, 'eight hundred'],
            [801, 'eight hundred one'],
            [1316, 'one thousand three hundred sixteen'],
            [1000000, 'one million'],
            [2000000, 'two million'],
            [3000200, 'three million two hundred'],
            [700000000000, 'seven hundred billion'],
            [-54, 'minus fifty-four'],
            [-1000000, 'minus one million'],
        ];
    }

    /**
     * Test convertFloatToWords with various float inputs.
     *
     * @dataProvider floatProvider
     */
    public function testConvertFloatToWords(float $input, string $expected): void
    {
        $this->assertEquals($expected, $this->numbers->convertFloatToWords($input));
    }

    /**
     * Data provider for float conversion tests.
     *
     * @return array
     */
    public static function floatProvider(): array
    {
        return [
            [0.0, 'zero'],
            [1.1, 'one point one'],
            [21.21, 'twenty-one point two one'],
            [100.001, 'one hundred point zero zero one'],
            [-1.1, 'minus one point one'],
            [0.001, 'zero point zero zero one'],
            [1000000.000001, 'one million point zero zero zero zero zero one'],
        ];
    }

    /**
     * Test convertFloatToWords with decimal limit.
     */
    public function testConvertFloatToWordsWithDecimalLimit(): void
    {
        $this->assertEquals(
            'one point two three',
            $this->numbers->convertFloatToWords(1.23456, '-', ' ', ' ', 'minus ', ' point ', 2)
        );
    }

    /**
     * Test numberToWords with various inputs.
     *
     * @dataProvider numberToWordsProvider
     */
    public function testNumberToWords(int|float $input, string $expected): void
    {
        $this->assertEquals($expected, $this->numbers->numberToWords($input));
    }

    /**
     * Data provider for numberToWords tests.
     *
     * @return array
     */
    public static function numberToWordsProvider(): array
    {
        return [
            [0, 'zero'],
            [1, 'one'],
            [-1, 'minus one'],
            [1.1, 'one point one'],
            [100, 'one hundred'],
            [-100.001, 'minus one hundred point zero zero one'],
            [1000000, 'one million'],
            [1000000.000001, 'one million point zero zero zero zero zero one'],
        ];
    }

    /**
     * Test convertFloatToWords with scientific notation input that PHP converts automatically.
     */
    public function testConvertFloatToWordsWithAutoConvertedScientificNotation(): void
    {
        $this->assertEquals('one million', $this->numbers->convertFloatToWords(1e6));
        $this->assertEquals('one million', $this->numbers->convertFloatToWords(1E6));
    }

    /**
     * Test convertFloatToWords with scientific notation input that remains in scientific notation.
     */
    public function testConvertFloatToWordsWithNonConvertedScientificNotation(): void
    {
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Scientific notation is not supported.');
        $this->numbers->convertFloatToWords(1e-6);
    }

    /**
     * Test numberToWords with scientific notation input that PHP converts automatically.
     */
    public function testNumberToWordsWithAutoConvertedScientificNotation(): void
    {
        $this->assertEquals('one million', $this->numbers->numberToWords(1e6));
        $this->assertEquals('one million', $this->numbers->numberToWords(1E6));
    }

    /**
     * Test numberToWords with scientific notation input that remains in scientific notation.
     */
    public function testNumberToWordsWithNonConvertedScientificNotation(): void
    {
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Scientific notation is not supported.');
        $this->numbers->numberToWords(1e-6);
    }

    /**
     * Test convertFloatToWords with string scientific notation input.
     */
    public function testConvertFloatToWordsWithStringScientificNotation(): void
    {
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Scientific notation is not supported.');
        $this->numbers->convertFloatToWords(PHP_FLOAT_MAX);
    }

    /**
     * Test numberToWords with string scientific notation input.
     */
    public function testNumberToWordsWithStringScientificNotation(): void
    {
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Scientific notation is not supported.');
        $this->numbers->numberToWords(PHP_FLOAT_MAX);
    }

    /**
     * Test convertIntegerToWords with maximum integer value.
     */
    public function testConvertIntegerToWordsWithMaxInteger(): void
    {
        $maxInt = PHP_INT_MAX;
        $result = $this->numbers->convertIntegerToWords($maxInt);

        $this->assertStringStartsWith('nine quintillion', $result);
        $this->assertStringEndsWith('seven', $result);
    }

    /**
     * Test convertFloatToWords with regular float values.
     *
     * @dataProvider regularFloatProvider
     */
    public function testConvertFloatToWordsWithRegularFloats(float $input, string $expected): void
    {
        $this->assertEquals($expected, $this->numbers->convertFloatToWords($input));
    }

    /**
     * Data provider for regular float conversion tests.
     *
     * @return array
     */
    public static function regularFloatProvider(): array
    {
        return [
            [0.1, 'zero point one'],
            [1.23, 'one point two three'],
            [999.999, 'nine hundred ninety-nine point nine nine nine'],
            [-0.5, 'minus zero point five'],
        ];
    }
}
