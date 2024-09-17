<?php

namespace FOfX\Helper;

use PHPUnit\Framework\TestCase;

class MemoryTest extends TestCase
{
    /**
     * @var  string
     */
    private $originalMemoryLimit;

    /**
     * Set up the test environment.
     *
     * @return  void
     */
    protected function setUp(): void
    {
        parent::setUp();

        // Store the original memory limit
        $this->originalMemoryLimit = ini_get('memory_limit');
    }

    /**
     * Tear down the test environment.
     *
     * @return  void
     */
    protected function tearDown(): void
    {
        // Restore the original memory limit
        ini_set('memory_limit', $this->originalMemoryLimit);

        parent::tearDown();
    }

    /**
     * Test that set_memory_max sets the memory limit to -1.
     *
     * @return  void
     */
    public function test_set_memory_max_sets_limit_to_unlimited(): void
    {
        // Call the function
        set_memory_max();

        // Assert that the memory limit is set to -1
        $this->assertEquals('-1', ini_get('memory_limit'));
    }

    /**
     * Test that set_memory_max works when the initial limit is already unlimited.
     *
     * @return  void
     */
    public function test_set_memory_max_when_already_unlimited(): void
    {
        // Set the initial limit to unlimited
        ini_set('memory_limit', '-1');

        // Call the function
        set_memory_max();

        // Assert that the memory limit is still -1
        $this->assertEquals('-1', ini_get('memory_limit'));
    }

    /**
     * Test that set_memory_max works with a high initial limit.
     *
     * @return  void
     */
    public function test_set_memory_max_from_high_initial_limit(): void
    {
        // Get current memory usage
        $currentUsage = memory_get_usage(true);

        // Set a high initial limit (double the current usage)
        $highLimit = $currentUsage * 2;
        ini_set('memory_limit', $highLimit);

        // Call the function
        set_memory_max();

        // Assert that the memory limit is set to -1
        $this->assertEquals('-1', ini_get('memory_limit'));
    }

    /**
     * Test that set_memory_max works with the default memory limit.
     *
     * @return  void
     */
    public function test_set_memory_max_from_default_limit(): void
    {
        // Reset to PHP's default memory limit
        ini_restore('memory_limit');

        // Call the function
        set_memory_max();

        // Assert that the memory limit is set to -1
        $this->assertEquals('-1', ini_get('memory_limit'));
    }

    /**
     * Test that the function increases the memory limit when it's below the minimum.
     *
     * @return  void
     */
    public function test_minimum_memory_limit_increases_when_below(): void
    {
        // Set initial memory limit to 128M
        ini_set('memory_limit', '128M');

        // Call the function with a higher limit
        minimum_memory_limit('256M');

        // Assert that the memory limit has been increased
        $this->assertEquals('256M', ini_get('memory_limit'));
    }

    /**
     * Test that the function doesn't change the limit when it's already above the minimum.
     *
     * @return  void
     */
    public function test_minimum_memory_limit_unchanged_when_above(): void
    {
        // Set initial memory limit to 512M
        ini_set('memory_limit', '512M');

        // Call the function with a lower limit
        minimum_memory_limit('256M');

        // Assert that the memory limit remains unchanged
        $this->assertEquals('512M', ini_get('memory_limit'));
    }

    /**
     * Test that the function doesn't change the limit when it's set to unlimited (-1).
     *
     * @return  void
     */
    public function test_minimum_memory_limit_unchanged_when_unlimited(): void
    {
        // Set initial memory limit to unlimited
        ini_set('memory_limit', '-1');

        // Call the function
        minimum_memory_limit('1G');

        // Assert that the memory limit remains unlimited
        $this->assertEquals('-1', ini_get('memory_limit'));
    }

    /**
     * Test that the function accepts integer input.
     *
     * @return  void
     */
    public function test_minimum_memory_limit_accepts_integer_input(): void
    {
        // Set initial memory limit to 128M
        ini_set('memory_limit', '128M');

        // Call the function with an integer (256MB in bytes)
        minimum_memory_limit(268435456);

        // Assert that the memory limit has been increased to 256M
        $this->assertEquals('256M', ini_get('memory_limit'));
    }

    /**
     * Test that the function rounds up to the nearest MB.
     *
     * @return  void
     */
    public function test_minimum_memory_limit_rounds_up(): void
    {
        // Set initial memory limit to 128M
        ini_set('memory_limit', '128M');

        // Call the function with a non-round number of MB
        minimum_memory_limit('200M');

        // Assert that the memory limit has been rounded up to 200M
        $this->assertEquals('200M', ini_get('memory_limit'));
    }

    /**
     * Test that the function throws an exception for invalid input.
     *
     * @return  void
     */
    public function test_minimum_memory_limit_throws_exception_for_invalid_input(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        minimum_memory_limit('invalid input');
    }

    /**
     * Test that get_memory_size returns a non-negative integer for a simple string.
     *
     * @return  void
     */
    public function test_get_memory_size_returns_non_negative_for_string(): void
    {
        $result = get_memory_size("Hello, World!");
        $this->assertIsInt($result);
        $this->assertGreaterThanOrEqual(0, $result);
    }

    /**
     * Test that get_memory_size returns a larger value for a larger data structure.
     *
     * @return  void
     */
    public function test_get_memory_size_increases_with_data_size(): void
    {
        $smallArray = range(1, 100);
        $largeArray = range(1, 10000);

        $smallSize = get_memory_size($smallArray);
        $largeSize = get_memory_size($largeArray);

        $this->assertGreaterThan($smallSize, $largeSize);
    }

    /**
     * Test that get_memory_size handles nested arrays.
     *
     * @return  void
     */
    public function test_get_memory_size_handles_nested_arrays(): void
    {
        $nestedArray = [
            'a' => [1, 2, 3],
            'b' => ['x' => 'y', 'z' => [4, 5, 6]]
        ];

        $result = get_memory_size($nestedArray);
        $this->assertIsInt($result);
        $this->assertGreaterThan(0, $result);
    }

    /**
     * Test that get_memory_size handles objects.
     *
     * @return  void
     */
    public function test_get_memory_size_handles_objects(): void
    {
        $obj = new \stdClass();
        $obj->name = "Test Object";
        $obj->data = [1, 2, 3];

        $result = get_memory_size($obj);
        $this->assertIsInt($result);
        $this->assertGreaterThan(0, $result);
    }

    /**
     * Test that get_memory_size returns 0 for null.
     *
     * @return  void
     */
    public function test_get_memory_size_returns_zero_for_null(): void
    {
        $result = get_memory_size(null);
        $this->assertEquals(0, $result);
    }

    /**
     * Test that get_memory_size handles resource types.
     *
     * @return  void
     */
    public function test_get_memory_size_handles_resources(): void
    {
        $file = fopen(__FILE__, 'r');
        $result = get_memory_size($file);
        fclose($file);

        $this->assertIsInt($result);
        $this->assertGreaterThanOrEqual(0, $result);
    }

    /**
     * Test that get_memory_size handles closures.
     *
     * @return  void
     */
    public function test_get_memory_size_handles_closures(): void
    {
        $closure = function () {
            return "Hello";
        };
        $result = get_memory_size($closure);

        $this->assertIsInt($result);
        $this->assertGreaterThan(0, $result);
    }

    /**
     * Test that get_memory_size handles circular references.
     *
     * @return  void
     */
    public function test_get_memory_size_handles_circular_references(): void
    {
        $arr1 = ['a' => 1];
        $arr2 = ['b' => 2];
        $arr1['ref'] = &$arr2;
        $arr2['ref'] = &$arr1;

        $result = get_memory_size($arr1);
        $this->assertIsInt($result);
    }

    /**
     * Test get_memory_info function for supported OS.
     */
    public function test_get_memory_info_supported_os()
    {
        $result = get_memory_info();
        $this->assertIsArray($result);
        $this->assertArrayHasKey('MemTotal', $result);
        $this->assertArrayHasKey('MemFree', $result);
        $this->assertArrayHasKey('MemAvailable', $result);
        $this->assertArrayHasKey('SwapTotal', $result);
        $this->assertArrayHasKey('SwapFree', $result);
    }

    /**
     * Test parse_windows_memory_output function with valid input.
     */
    public function test_parse_windows_memory_output_valid_input()
    {
        $input = "TotalVirtualMemorySize=8388608" . PHP_EOL .
            "TotalVisibleMemorySize=4194304" . PHP_EOL .
            "FreeVirtualMemory=6291456" . PHP_EOL .
            "FreePhysicalMemory=2097152" . PHP_EOL;
        $result = parse_windows_memory_output($input);

        $this->assertEquals([
            'TotalVirtualMemorySize' => 8388608,
            'TotalVisibleMemorySize' => 4194304,
            'FreeVirtualMemory' => 6291456,
            'FreePhysicalMemory' => 2097152,
        ], $result);
    }

    /**
     * Test parse_windows_memory_output function with invalid input.
     */
    public function test_parse_windows_memory_output_invalid_input()
    {
        $input = "InvalidKey=123" . PHP_EOL . "AnotherInvalidKey=456" . PHP_EOL;
        $result = parse_windows_memory_output($input);

        $this->assertEquals([
            'TotalVirtualMemorySize' => 0,
            'TotalVisibleMemorySize' => 0,
            'FreeVirtualMemory' => 0,
            'FreePhysicalMemory' => 0,
        ], $result);
    }

    /**
     * Test calculate_windows_memory_info function.
     */
    public function test_calculate_windows_memory_info()
    {
        $input = [
            'TotalVirtualMemorySize' => 8388608,
            'TotalVisibleMemorySize' => 4194304,
            'FreeVirtualMemory' => 6291456,
            'FreePhysicalMemory' => 2097152,
        ];
        $result = calculate_windows_memory_info($input);

        $this->assertEquals([
            'MemTotal' => 4194304,
            'MemFree' => 2097152,
            'MemAvailable' => 2097152,
            'SwapTotal' => 4194304,
            'SwapFree' => 4194304,
        ], $result);
    }

    /**
     * Test format_memory_info function.
     */
    public function test_format_memory_info()
    {
        $input = [
            'MemTotal' => 4194304,
            'MemFree' => 2097152,
            'MemAvailable' => 3145728,
            'SwapTotal' => 8388608,
            'SwapFree' => 6291456,
        ];
        $result = format_memory_info($input);

        $this->assertIsArray($result);
        $this->assertStringEndsWith('B', $result['MemTotal']);
        $this->assertStringEndsWith('B', $result['MemFree']);
        $this->assertStringEndsWith('B', $result['MemAvailable']);
        $this->assertStringEndsWith('B', $result['SwapTotal']);
        $this->assertStringEndsWith('B', $result['SwapFree']);
    }

    /**
     * Test get_mem_total function.
     */
    public function test_get_mem_total()
    {
        $result = get_mem_total();
        $this->assertIsInt($result);

        $formattedResult = get_mem_total(true);
        $this->assertIsString($formattedResult);
        $this->assertStringEndsWith('B', $formattedResult);
    }

    /**
     * Test get_mem_free function.
     */
    public function test_get_mem_free()
    {
        $result = get_mem_free();
        $this->assertIsInt($result);

        $formattedResult = get_mem_free(true);
        $this->assertIsString($formattedResult);
        $this->assertStringEndsWith('B', $formattedResult);
    }

    /**
     * Test get_mem_available function.
     */
    public function test_get_mem_available()
    {
        $result = get_mem_available();
        $this->assertIsInt($result);

        $formattedResult = get_mem_available(true);
        $this->assertIsString($formattedResult);
        $this->assertStringEndsWith('B', $formattedResult);
    }

    /**
     * Test get_swap_total function.
     */
    public function test_get_swap_total()
    {
        $result = get_swap_total();
        $this->assertIsInt($result);

        $formattedResult = get_swap_total(true);
        $this->assertIsString($formattedResult);
        $this->assertStringEndsWith('B', $formattedResult);
    }

    /**
     * Test get_swap_free function.
     */
    public function test_get_swap_free()
    {
        $result = get_swap_free();
        $this->assertIsInt($result);

        $formattedResult = get_swap_free(true);
        $this->assertIsString($formattedResult);
        $this->assertStringEndsWith('B', $formattedResult);
    }

    /**
     * Test get_mem_free_total function.
     */
    public function test_get_mem_free_total()
    {
        $result = get_mem_free_total();
        $this->assertIsInt($result);

        $formattedResult = get_mem_free_total(false, true);
        $this->assertIsString($formattedResult);
        $this->assertStringEndsWith('B', $formattedResult);

        $availableResult = get_mem_free_total(true);
        $this->assertIsInt($availableResult);
    }
}
