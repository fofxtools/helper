<?php

namespace FOfX\Helper;

use PHPUnit\Framework\TestCase;

class MathTest extends TestCase
{
    /**
     * Test that random_probability returns a float
     */
    public function test_random_probability_returns_float()
    {
        $result = random_probability();
        $this->assertIsFloat($result);
    }

    /**
     * Test that random_probability returns a value between 0 and 1
     */
    public function test_random_probability_between_zero_and_one()
    {
        $result = random_probability();
        $this->assertGreaterThanOrEqual(0, $result);
        $this->assertLessThan(1, $result);
    }

    /**
     * Test that random_probability never returns 1
     */
    public function test_random_probability_never_returns_one()
    {
        // Run the function multiple times to increase confidence
        for ($i = 0; $i < 1000; $i++) {
            $result = random_probability();
            $this->assertNotEquals(1, $result);
        }
    }

    /**
     * Test the distribution of random_probability
     */
    public function test_random_probability_distribution()
    {
        $buckets    = array_fill(0, 10, 0);
        $iterations = 100000;

        for ($i = 0; $i < $iterations; $i++) {
            $value  = random_probability();
            $bucket = min(floor($value * 10), 9);
            $buckets[$bucket]++;
        }

        // Check that each bucket has roughly the expected number of values
        $expectedPerBucket = $iterations / 10;
        // 10% tolerance
        $tolerance = $expectedPerBucket * 0.1;

        foreach ($buckets as $count) {
            $this->assertEqualsWithDelta($expectedPerBucket, $count, $tolerance);
        }
    }

    /**
     * Test that rand_float returns a float
     */
    public function test_rand_float_returns_float()
    {
        $result = rand_float(0, 1);
        $this->assertIsFloat($result);
    }

    /**
     * Test that rand_float returns a value within the specified range
     */
    public function test_rand_float_within_range()
    {
        $min    = -5.5;
        $max    = 10.5;
        $result = rand_float($min, $max);
        $this->assertGreaterThanOrEqual($min, $result);
        $this->assertLessThanOrEqual($max, $result);
    }

    /**
     * Test rand_float with min and max being the same value
     */
    public function test_rand_float_min_equals_max()
    {
        $value  = 3.14;
        $result = rand_float($value, $value);
        $this->assertEquals($value, $result);
    }

    /**
     * Test rand_float with very small range
     */
    public function test_rand_float_small_range()
    {
        $min    = 1.0;
        $max    = 1.0000000001;
        $result = rand_float($min, $max);
        $this->assertGreaterThanOrEqual($min, $result);
        $this->assertLessThanOrEqual($max, $result);
    }

    /**
     * Test rand_float with very large range
     */
    public function test_rand_float_large_range()
    {
        $min    = -(PHP_FLOAT_MAX / 2.01);
        $max    = PHP_FLOAT_MAX / 2.01;
        $result = rand_float($min, $max);
        $this->assertGreaterThanOrEqual($min, $result);
        $this->assertLessThanOrEqual($max, $result);
    }

    /**
     * Test that rand_float throws an exception when min is greater than max
     */
    public function test_rand_float_min_greater_than_max()
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('random_float() - Error: $min must be less than or equal to $max.');
        rand_float(10, 5);
    }

    /**
     * Test the distribution of rand_float
     */
    public function test_rand_float_distribution()
    {
        $min        = 0;
        $max        = 10;
        $iterations = 100000;
        $buckets    = array_fill(0, 10, 0);

        for ($i = 0; $i < $iterations; $i++) {
            $value  = rand_float($min, $max);
            $bucket = min(floor($value), 9);
            $buckets[$bucket]++;
        }

        // Check that each bucket has roughly the expected number of values
        $expectedPerBucket = $iterations / 10;
        $tolerance         = $expectedPerBucket * 0.1; // 10% tolerance

        foreach ($buckets as $count) {
            $this->assertEqualsWithDelta($expectedPerBucket, $count, $tolerance);
        }
    }

    /**
     * Test that hashed_probability returns a float
     */
    public function test_hashed_probability_returns_float()
    {
        $result = hashed_probability('test');
        $this->assertIsFloat($result);
    }

    /**
     * Test that hashed_probability returns a value between 0 (inclusive) and 1 (exclusive)
     */
    public function test_hashed_probability_range()
    {
        $result = hashed_probability('test');
        $this->assertGreaterThanOrEqual(0, $result);
        $this->assertLessThan(1, $result);
    }

    /**
     * Test that hashed_probability returns consistent results for the same input
     */
    public function test_hashed_probability_consistency()
    {
        $input   = 'test_string';
        $result1 = hashed_probability($input);
        $result2 = hashed_probability($input);
        $this->assertEquals($result1, $result2);
    }

    /**
     * Test that hashed_probability returns different results for different inputs
     */
    public function test_hashed_probability_different_inputs()
    {
        $result1 = hashed_probability('input1');
        $result2 = hashed_probability('input2');
        $this->assertNotEquals($result1, $result2);
    }

    /**
     * Test hashed_probability with empty string input
     */
    public function test_hashed_probability_empty_string()
    {
        $result = hashed_probability('');
        $this->assertIsFloat($result);
        $this->assertGreaterThanOrEqual(0, $result);
        $this->assertLessThan(1, $result);
    }

    /**
     * Test hashed_probability with very long string input
     */
    public function test_hashed_probability_long_string()
    {
        $longString = str_repeat('a', 1000000);
        $result     = hashed_probability($longString);
        $this->assertIsFloat($result);
        $this->assertGreaterThanOrEqual(0, $result);
        $this->assertLessThan(1, $result);
    }

    /**
     * Test hashed_probability with special characters
     */
    public function test_hashed_probability_special_characters()
    {
        $specialChars = '!@#$%^&*()_+{}[]|:;<>?,./';
        $result       = hashed_probability($specialChars);
        $this->assertIsFloat($result);
        $this->assertGreaterThanOrEqual(0, $result);
        $this->assertLessThan(1, $result);
    }

    /**
     * Test hashed_probability with Unicode characters
     */
    public function test_hashed_probability_unicode()
    {
        $unicodeString = 'こんにちは世界';
        $result        = hashed_probability($unicodeString);
        $this->assertIsFloat($result);
        $this->assertGreaterThanOrEqual(0, $result);
        $this->assertLessThan(1, $result);
    }

    /**
     * Test that hashed_probability never returns 1
     */
    public function test_hashed_probability_never_one()
    {
        // Test a large number of random strings
        for ($i = 0; $i < 1000; $i++) {
            $randomString = bin2hex(random_bytes(16));
            $result       = hashed_probability($randomString);
            $this->assertNotEquals(1, $result);
        }
    }

    /**
     * Test the distribution of hashed_probability
     */
    public function test_hashed_probability_distribution()
    {
        $buckets    = array_fill(0, 10, 0);
        $iterations = 100000;

        for ($i = 0; $i < $iterations; $i++) {
            $randomString = bin2hex(random_bytes(16));
            $value        = hashed_probability($randomString);
            $bucket       = min(floor($value * 10), 9);
            $buckets[$bucket]++;
        }

        // Check that each bucket has roughly the expected number of values
        $expectedPerBucket = $iterations / 10;
        $tolerance         = $expectedPerBucket * 0.1; // 10% tolerance

        foreach ($buckets as $count) {
            $this->assertEqualsWithDelta($expectedPerBucket, $count, $tolerance);
        }
    }

    /**
     * Test that the function returns a consistent element for a given string and array.
     */
    public function test_hashed_array_element_returns_consistent_result()
    {
        $string = 'consistent_input';
        $array  = ['Apple', 'Banana', 'Cherry', 'Date'];

        $result1 = hashed_array_element($string, $array);
        $result2 = hashed_array_element($string, $array);

        $this->assertEquals($result1, $result2);
    }

    /**
     * Test that the function returns different elements for different strings.
     */
    public function test_hashed_array_element_returns_different_results_for_different_strings()
    {
        $string1 = 'input_one';
        $string2 = 'input_two';
        $array   = ['Apple', 'Banana', 'Cherry', 'Date'];

        $result1 = hashed_array_element($string1, $array);
        $result2 = hashed_array_element($string2, $array);

        $this->assertNotEquals($result1, $result2);
    }

    /**
     * Test that the function returns an element for an array with one item.
     */
    public function test_hashed_array_element_single_element_array()
    {
        $string = 'single_item';
        $array  = ['OnlyItem'];

        $result = hashed_array_element($string, $array);

        $this->assertEquals('OnlyItem', $result);
    }

    /**
     * Test that the function throws an exception when the array is empty.
     */
    public function test_hashed_array_element_throws_exception_for_empty_array()
    {
        $this->expectException(\InvalidArgumentException::class);

        $string = 'empty_array';
        $array  = [];

        hashed_array_element($string, $array);
    }

    /**
     * Test that the function handles an array with multiple identical elements.
     */
    public function test_hashed_array_element_with_duplicate_elements()
    {
        $string = 'duplicate_elements';
        $array  = ['Apple', 'Banana', 'Banana', 'Banana', 'Cherry'];

        $result = hashed_array_element($string, $array);

        $this->assertContains($result, $array);
    }

    /**
     * Test that the function returns an element for a "dirty" string with special characters.
     */
    public function test_hashed_array_element_with_special_characters_in_string()
    {
        $string = '!@#$%^&*()_+[]{};:,.<>?';
        $array  = ['Apple', 'Banana', 'Cherry', 'Date'];

        $result = hashed_array_element($string, $array);

        $this->assertContains($result, $array);
    }

    /**
     * Test that the function works with an array of different data types.
     */
    public function test_hashed_array_element_with_mixed_data_types()
    {
        $string = 'mixed_data_types';
        $array  = ['Apple', 123, 45.67, true, null];

        $result = hashed_array_element($string, $array);

        $this->assertContains($result, $array);
    }

    /**
     * Test that hashed_array_element produces a roughly even distribution
     */
    public function test_hashed_array_element_distribution()
    {
        $array      = ['Apples', 'Bananas', 'Oranges', 'Pears', 'Pineapples'];
        $counts     = [];
        $iterations = 100000;

        // Run the function multiple times and count the occurrences of each element
        for ($i = 0; $i < $iterations; $i++) {
            $input          = 'Test string ' . $i;
            $value          = hashed_array_element($input, $array);
            $counts[$value] = ($counts[$value] ?? 0) + 1;
        }

        // Calculate the expected count for each element
        $expectedCount = $iterations / count($array);

        // Allow for a 5% deviation from the expected count
        $allowedDeviation = $expectedCount * 0.05;

        // Check that the count for each element is within the allowed deviation
        foreach ($array as $element) {
            $this->assertArrayHasKey($element, $counts, "Element '$element' was never selected");
            $this->assertEqualsWithDelta(
                $expectedCount,
                $counts[$element],
                $allowedDeviation,
                "Distribution for element '$element' is outside the expected range"
            );
        }

        // Additional check: ensure all elements in $counts are from the original array
        $this->assertEmpty(array_diff(array_keys($counts), $array), 'Unexpected elements were selected');
    }

    /**
     * @test
     */
    public function test_expected_duplicates_returns_zero_for_sample_size_less_than_two()
    {
        $this->assertEquals(0, expected_duplicates(10, 1));
        $this->assertEquals(0, expected_duplicates(10, 0));
    }

    /**
     * @test
     */
    public function test_expected_duplicates_calculates_expected_duplicates_correctly()
    {
        $this->assertEqualsWithDelta(0.43820750088024, expected_duplicates(100, 10), 0.0000001);
    }

    /**
     * @test
     */
    public function test_expected_duplicates_handles_custom_sample_probability()
    {
        $this->assertEqualsWithDelta(-3.3193, expected_duplicates(10, 5, 0.3), 0.0001);
    }

    /**
     * @test
     */
    public function test_expected_duplicates_throws_exception_for_invalid_total_objects()
    {
        $this->expectException(\InvalidArgumentException::class);
        expected_duplicates(0, 5);
    }

    /**
     * @test
     */
    public function test_expected_duplicates_throws_exception_for_negative_sample_size()
    {
        $this->expectException(\InvalidArgumentException::class);
        expected_duplicates(10, -1);
    }

    /**
     * @test
     */
    public function test_expected_duplicates_throws_exception_for_invalid_sample_probability()
    {
        $this->expectException(\InvalidArgumentException::class);
        expected_duplicates(10, 5, 1.5);
    }

    /**
     * Test sample_duplicates with small numbers.
     *
     * @return void
     */
    public function test_sample_duplicates_small_numbers(): void
    {
        $result = sample_duplicates(10, 5, 1000);

        // Due to randomness, we use a range instead of an exact value
        $this->assertGreaterThanOrEqual(0.8, $result);
        $this->assertLessThanOrEqual(1.0, $result);
    }

    /**
     * Test sample_duplicates with medium numbers.
     *
     * @return void
     */
    public function test_sample_duplicates_medium_numbers(): void
    {
        $result = sample_duplicates(100, 10, 1000);

        $this->assertGreaterThanOrEqual(0.3, $result);
        $this->assertLessThanOrEqual(0.6, $result);
    }

    /**
     * Test sample_duplicates with large numbers.
     *
     * @return void
     */
    public function test_sample_duplicates_large_numbers(): void
    {
        $result = sample_duplicates(1000, 100, 1000);

        $this->assertGreaterThanOrEqual(4.5, $result);
        $this->assertLessThanOrEqual(5.2, $result);
    }

    /**
     * Test sample_duplicates when sample size is greater than total objects.
     *
     * @return void
     */
    public function test_sample_duplicates_sample_size_greater_than_total(): void
    {
        $result = sample_duplicates(50, 60, 1000);

        $this->assertGreaterThanOrEqual(24.5, $result);
        $this->assertLessThanOrEqual(25.2, $result);
    }

    /**
     * Test sample_duplicates with very large number of total objects.
     *
     * @return void
     */
    public function test_sample_duplicates_very_large_total_objects(): void
    {
        $result = sample_duplicates(1000000, 10, 100);

        // With very large total objects and small sample size, duplicates are extremely rare
        $this->assertEqualsWithDelta(0.0, $result, 0.01);
    }

    /**
     * Test sample_duplicates when sample size equals total objects.
     *
     * @return void
     */
    public function test_sample_duplicates_sample_size_equals_total(): void
    {
        $result = sample_duplicates(10, 10, 10000);

        $this->assertGreaterThanOrEqual(3.3, $result);
        $this->assertLessThanOrEqual(3.6, $result);
    }

    /**
     * Test that sample_duplicates throws an exception for invalid input.
     *
     * @return void
     */
    public function test_sample_duplicates_invalid_input(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        sample_duplicates(0, 5, 1000);
    }

    /**
     * Test that sample_duplicates returns a float.
     *
     * @return void
     */
    public function test_sample_duplicates_return_type(): void
    {
        $result = sample_duplicates(10, 5, 1000);
        $this->assertIsFloat($result);
    }

    /**
     * Test estimation with small population and sample size.
     *
     * @return void
     */
    public function test_estimate_total_from_duplicates_small(): void
    {
        $actual_total        = 100;
        $sample_size         = 20;
        $expected_duplicates = expected_duplicates($actual_total, $sample_size);

        $estimated_total = estimate_total_from_duplicates($sample_size, $expected_duplicates);

        $this->assertEquals($actual_total, $estimated_total);
    }

    /**
     * Test estimation with medium population and sample size.
     *
     * @return void
     */
    public function test_estimate_total_from_duplicates_medium(): void
    {
        $actual_total        = 1000;
        $sample_size         = 100;
        $expected_duplicates = expected_duplicates($actual_total, $sample_size);

        $estimated_total = estimate_total_from_duplicates($sample_size, $expected_duplicates);

        $this->assertEquals($actual_total, $estimated_total);
    }

    /**
     * Test estimation with larger population and sample size.
     *
     * @return void
     */
    public function test_estimate_total_from_duplicates_large(): void
    {
        $actual_total        = 5000;
        $sample_size         = 200;
        $expected_duplicates = expected_duplicates($actual_total, $sample_size);

        $estimated_total = estimate_total_from_duplicates($sample_size, $expected_duplicates);

        $this->assertEquals($actual_total, $estimated_total);
    }

    /**
     * Test estimation with very small sample size relative to population.
     *
     * @return void
     */
    public function test_estimate_total_from_duplicates_small_sample(): void
    {
        $actual_total        = 10000;
        $sample_size         = 10;
        $expected_duplicates = expected_duplicates($actual_total, $sample_size);

        $estimated_total = estimate_total_from_duplicates($sample_size, $expected_duplicates);

        // For very small sample sizes, the estimation might not be possible
        $this->assertNull($estimated_total);
    }

    /**
     * Test that the function throws an exception for invalid sample size.
     *
     * @return void
     */
    public function test_estimate_total_from_duplicates_invalid_sample_size(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Sample size must be positive and duplicates must be non-negative.');

        estimate_total_from_duplicates(0, 5);
    }

    /**
     * Test that the function throws an exception for negative duplicates.
     *
     * @return void
     */
    public function test_estimate_total_from_duplicates_negative_duplicates(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Sample size must be positive and duplicates must be non-negative.');

        estimate_total_from_duplicates(10, -1);
    }

    /**
     * Test estimation with fractional duplicates.
     *
     * @return void
     */
    public function test_estimate_total_from_duplicates_fractional_duplicates(): void
    {
        $sample_size = 50;
        $duplicates  = 2.3734; // This is close to the expected duplicates for actual_total = 500

        $estimated_total = estimate_total_from_duplicates($sample_size, $duplicates);

        // The estimated total should be close to 500
        $this->assertGreaterThanOrEqual(495, $estimated_total);
        $this->assertLessThanOrEqual(505, $estimated_total);
    }

    /**
     * Test the minimum difference in an array of positive numbers.
     */
    public function test_minimum_difference_in_positive_numbers_array()
    {
        $array  = [0.05, 0.085, 0.15, 0.01, 1.2, 0.7, 0.64];
        $result = array_minimum_difference($array, true);
        $this->assertEqualsWithDelta(0.035, $result, 0.00001);
    }

    /**
     * Test the minimum difference in an array with mixed positive and negative numbers.
     */
    public function test_minimum_difference_in_mixed_numbers_array()
    {
        $array  = [0.05, -0.085, 0.15, -0.01, 1.2, 0.7, -0.64];
        $result = array_minimum_difference($array);
        $this->assertEqualsWithDelta(0.06, $result, 0.00001);
    }

    /**
     * Test the minimum difference in an array with negative numbers and ensure_nonnegative set to true.
     * Expecting an InvalidArgumentException to be thrown.
     */
    public function test_minimum_difference_with_negative_numbers_and_ensure_nonnegative()
    {
        $this->expectException(\InvalidArgumentException::class);
        $array = [0.05, -0.085, 0.15, -0.01, 1.2, 0.7, -0.64];
        array_minimum_difference($array, true);
    }

    /**
     * Test the function with an empty array.
     * Expecting an InvalidArgumentException to be thrown.
     */
    public function test_empty_array_throws_invalid_argument_exception()
    {
        $this->expectException(\InvalidArgumentException::class);
        $array = [];
        array_minimum_difference($array);
    }

    /**
     * Test the minimum difference in an array with all elements being the same.
     */
    public function test_minimum_difference_in_array_with_identical_elements()
    {
        $array  = [1, 1, 1, 1];
        $result = array_minimum_difference($array);
        $this->assertEquals(0, $result);
    }

    /**
     * Test the minimum difference in an array with non-numeric elements.
     * Expecting an InvalidArgumentException to be thrown.
     */
    public function test_minimum_difference_with_non_numeric_elements()
    {
        $this->expectException(\InvalidArgumentException::class);
        $array = [0.05, 'string', 0.15];
        array_minimum_difference($array);
    }

    /**
     * Test the minimum difference in an array with only one element.
     * Expecting an InvalidArgumentException to be thrown.
     */
    public function test_minimum_difference_with_one_element()
    {
        $this->expectException(\InvalidArgumentException::class);
        $array = [0.05];
        array_minimum_difference($array);
    }

    /**
     * Test basic functionality of array_random_element_weighted.
     */
    public function test_array_random_element_weighted_basic_functionality()
    {
        // Set up test data
        $array   = ['a', 'b', 'c'];
        $weights = [1, 1, 1];

        // Run the function multiple times
        $results = [];
        for ($i = 0; $i < 1000; $i++) {
            $results[] = array_random_element_weighted($array, $weights);
        }

        // Assert that all elements are present in the results
        $this->assertContains('a', $results);
        $this->assertContains('b', $results);
        $this->assertContains('c', $results);
    }

    /**
     * Test array_random_element_weighted with uneven weights.
     */
    public function test_array_random_element_weighted_uneven_weights()
    {
        // Set up test data
        $array   = ['a', 'b', 'c'];
        $weights = [1, 2, 7];

        // Run the function multiple times
        $results = array_fill_keys($array, 0);
        $trials  = 10000;
        for ($i = 0; $i < $trials; $i++) {
            $result = array_random_element_weighted($array, $weights);
            $results[$result]++;
        }

        // Assert that the distribution roughly matches the weights
        $this->assertGreaterThan($results['a'], $results['b']);
        $this->assertGreaterThan($results['b'], $results['c']);
        $this->assertLessThan($trials * 0.15, $results['a']);
        $this->assertGreaterThan($trials * 0.60, $results['c']);
    }

    /**
     * Test array_random_element_weighted with a single element.
     */
    public function test_array_random_element_weighted_single_element()
    {
        // Set up test data
        $array   = ['a'];
        $weights = [1];

        // Assert that the function always returns the single element
        $this->assertSame('a', array_random_element_weighted($array, $weights));
    }

    /**
     * Test array_random_element_weighted with empty arrays.
     */
    public function test_array_random_element_weighted_empty_arrays()
    {
        // Set up test data
        $array   = [];
        $weights = [];

        // Assert that the function throws an exception for empty arrays
        $this->expectException(\InvalidArgumentException::class);
        array_random_element_weighted($array, $weights);
    }

    /**
     * Test array_random_element_weighted with mismatched array lengths.
     */
    public function test_array_random_element_weighted_mismatched_lengths()
    {
        // Set up test data
        $array   = ['a', 'b', 'c'];
        $weights = [1, 2];

        // Assert that the function throws an exception for mismatched lengths
        $this->expectException(\InvalidArgumentException::class);
        array_random_element_weighted($array, $weights);
    }

    /**
     * Test array_random_element_weighted with negative weights.
     */
    public function test_array_random_element_weighted_negative_weights()
    {
        // Set up test data
        $array   = ['a', 'b', 'c'];
        $weights = [1, -2, 3];

        // Assert that the function throws an exception for negative weights
        $this->expectException(\InvalidArgumentException::class);
        array_random_element_weighted($array, $weights, true);
    }

    /**
     * Test array_random_element_weighted with non-numeric weights.
     */
    public function test_array_random_element_weighted_non_numeric_weights()
    {
        // Set up test data
        $array   = ['a', 'b', 'c'];
        $weights = [1, 'two', 3];

        // Assert that the function throws an exception for non-numeric weights
        $this->expectException(\InvalidArgumentException::class);
        array_random_element_weighted($array, $weights, true);
    }

    /**
     * Test array_random_element_weighted with very large weights.
     */
    public function test_array_random_element_weighted_large_weights()
    {
        // Set up test data
        $array   = ['a', 'b', 'c'];
        $weights = [PHP_INT_MAX, PHP_INT_MAX, PHP_INT_MAX];

        // Run the function multiple times
        $results = [];
        for ($i = 0; $i < 1000; $i++) {
            $results[] = array_random_element_weighted($array, $weights);
        }

        // Assert that all elements are present in the results
        $this->assertContains('a', $results);
        $this->assertContains('b', $results);
        $this->assertContains('c', $results);
    }

    /**
     * Test array_random_element_weighted with floating-point weights.
     */
    public function test_array_random_element_weighted_float_weights()
    {
        // Set up test data
        $array   = ['a', 'b', 'c'];
        $weights = [0.1, 0.2, 0.7];

        // Run the function multiple times
        $results = array_fill_keys($array, 0);
        $trials  = 10000;
        for ($i = 0; $i < $trials; $i++) {
            $result = array_random_element_weighted($array, $weights);
            $results[$result]++;
        }

        // Assert that the distribution roughly matches the weights
        $this->assertGreaterThan($results['a'], $results['b']);
        $this->assertGreaterThan($results['b'], $results['c']);
        $this->assertLessThan($trials * 0.15, $results['a']);
        $this->assertGreaterThan($trials * 0.60, $results['c']);
    }

    /**
     * Test basic functionality of array_weighted_sort.
     */
    public function test_array_weighted_sort_basic_functionality()
    {
        // Set up test data
        $array   = ['a', 'b', 'c'];
        $weights = [1, 1, 1];

        // Run the function
        $result = array_weighted_sort($array, $weights);

        // Assert that all elements are present in the result
        $this->assertCount(3, $result);
        $this->assertContains('a', $result);
        $this->assertContains('b', $result);
        $this->assertContains('c', $result);
    }

    /**
     * Test array_weighted_sort with uneven weights.
     */
    public function test_array_weighted_sort_uneven_weights()
    {
        // Set up test data
        $array   = ['a', 'b', 'c'];
        $weights = [1, 2, 7];

        // Run the function multiple times
        $positions = array_fill_keys($array, array_fill(0, 3, 0));
        $trials    = 1000;
        for ($i = 0; $i < $trials; $i++) {
            $result = array_weighted_sort($array, $weights);
            foreach ($result as $index => $value) {
                $positions[$value][$index]++;
            }
        }

        // Assert that 'c' appears more often in the first position
        $this->assertGreaterThan($positions['a'][0], $positions['c'][0]);
        $this->assertGreaterThan($positions['b'][0], $positions['c'][0]);
    }

    /**
     * Test array_weighted_sort with a single element.
     */
    public function test_array_weighted_sort_single_element()
    {
        // Set up test data
        $array   = ['a'];
        $weights = [1];

        // Assert that the function returns the single element array unchanged
        $this->assertSame($array, array_weighted_sort($array, $weights));
    }

    /**
     * Test array_weighted_sort with empty arrays.
     */
    public function test_array_weighted_sort_empty_arrays()
    {
        // Set up test data
        $array   = [];
        $weights = [];

        // Assert that the function returns an empty array
        $this->assertSame([], array_weighted_sort($array, $weights));
    }

    /**
     * Test array_weighted_sort with mismatched array lengths.
     */
    public function test_array_weighted_sort_mismatched_lengths()
    {
        // Set up test data
        $array   = ['a', 'b', 'c'];
        $weights = [1, 2];

        // Assert that the function throws an exception for mismatched lengths
        $this->expectException(\InvalidArgumentException::class);
        array_weighted_sort($array, $weights);
    }

    /**
     * Test array_weighted_sort with negative weights.
     */
    public function test_array_weighted_sort_negative_weights()
    {
        // Set up test data
        $array   = ['a', 'b', 'c'];
        $weights = [1, -2, 3];

        // Assert that the function throws an exception for negative weights
        $this->expectException(\InvalidArgumentException::class);
        array_weighted_sort($array, $weights, true);
    }

    /**
     * Test array_weighted_sort with non-numeric weights.
     */
    public function test_array_weighted_sort_non_numeric_weights()
    {
        // Set up test data
        $array   = ['a', 'b', 'c'];
        $weights = [1, 'two', 3];

        // Assert that the function throws an exception for non-numeric weights
        $this->expectException(\InvalidArgumentException::class);
        array_weighted_sort($array, $weights, true);
    }

    /**
     * Test array_weighted_sort with very large weights.
     */
    public function test_array_weighted_sort_large_weights()
    {
        // Set up test data
        $array   = ['a', 'b', 'c'];
        $weights = [PHP_INT_MAX, PHP_INT_MAX, PHP_INT_MAX];

        // Run the function
        $result = array_weighted_sort($array, $weights);

        // Assert that all elements are present in the result
        $this->assertCount(3, $result);
        $this->assertContains('a', $result);
        $this->assertContains('b', $result);
        $this->assertContains('c', $result);
    }

    /**
     * Test array_weighted_sort with floating-point weights.
     */
    public function test_array_weighted_sort_float_weights()
    {
        // Set up test data
        $array   = ['a', 'b', 'c'];
        $weights = [0.1, 0.2, 0.7];

        // Run the function multiple times
        $positions = array_fill_keys($array, array_fill(0, 3, 0));
        $trials    = 1000;
        for ($i = 0; $i < $trials; $i++) {
            $result = array_weighted_sort($array, $weights);
            foreach ($result as $index => $value) {
                $positions[$value][$index]++;
            }
        }

        // Assert that 'c' appears more often in the first position
        $this->assertGreaterThan($positions['a'][0], $positions['c'][0]);
        $this->assertGreaterThan($positions['b'][0], $positions['c'][0]);
    }

    /**
     * Test array_weighted_sort maintains relative order for equal weights.
     */
    public function test_array_weighted_sort_equal_weights_stability()
    {
        // Set up test data
        $array   = ['a', 'b', 'c', 'd'];
        $weights = [1, 1, 1, 1];

        // Run the function multiple times
        $order_preserved = 0;
        $trials          = 1000;
        for ($i = 0; $i < $trials; $i++) {
            $result = array_weighted_sort($array, $weights);
            if (
                array_search('a', $result) < array_search('b', $result) &&
                array_search('b', $result) < array_search('c', $result) &&
                array_search('c', $result) < array_search('d', $result)
            ) {
                $order_preserved++;
            }
        }

        // Assert that the original order is sometimes preserved
        $this->assertGreaterThan(0, $order_preserved);
        // But not always (which would indicate no shuffling)
        $this->assertLessThan($trials, $order_preserved);
    }

    /**
     * Test basic functionality of array_dot_product.
     */
    public function test_array_dot_product_basic_functionality()
    {
        // Set up test data
        $array = [2 => 3, 4 => 1, 6 => 2];

        // Run the function
        $result = array_dot_product($array);

        // Assert the correct result
        $this->assertEquals(22, $result);
    }

    /**
     * Test array_dot_product with zero values.
     */
    public function test_array_dot_product_zero_values()
    {
        // Set up test data
        $array = [0 => 5, 5 => 0, 3 => 2];

        // Run the function
        $result = array_dot_product($array);

        // Assert the correct result
        $this->assertEquals(6, $result);
    }

    /**
     * Test array_dot_product with negative values.
     */
    public function test_array_dot_product_negative_values()
    {
        // Set up test data
        $array = [-2 => 3, 4 => -1, -3 => -2];

        // Run the function
        $result = array_dot_product($array);

        // Assert the correct result
        $this->assertEquals(-4, $result);
    }

    /**
     * Test array_dot_product with floating point numbers.
     */
    public function test_array_dot_product_floating_point()
    {
        // Set up test data
        $array = ['1.5' => 2, '2.7' => 3, '0.5' => 4];

        // Run the function
        $result = array_dot_product($array);

        // Calculate the expected result
        $expected = (1.5 * 2) + (2.7 * 3) + (0.5 * 4);

        // Assert the correct result
        $this->assertEqualsWithDelta($expected, $result, 0.0001, 'The result is not within the expected delta.');
    }

    /**
     * Test array_dot_product with a single element.
     */
    public function test_array_dot_product_single_element()
    {
        // Set up test data
        $array = [5 => 2];

        // Run the function
        $result = array_dot_product($array);

        // Assert the correct result
        $this->assertEquals(10, $result);
    }

    /**
     * Test array_dot_product with an empty array.
     */
    public function test_array_dot_product_empty_array()
    {
        // Set up test data
        $array = [];

        // Assert that the function throws an exception for an empty array
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Input array cannot be empty.');
        array_dot_product($array);
    }

    /**
     * Test array_dot_product with non-numeric keys.
     */
    public function test_array_dot_product_non_numeric_keys()
    {
        // Set up test data
        $array = ['a' => 2, 3 => 4, 'b' => 1];

        // Assert that the function throws an exception for non-numeric keys
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('All array elements must be numeric.');
        array_dot_product($array);
    }

    /**
     * Test array_dot_product with non-numeric values.
     */
    public function test_array_dot_product_non_numeric_values()
    {
        // Set up test data
        $array = [2 => 'a', 3 => 4, 5 => 'b'];

        // Assert that the function throws an exception for non-numeric values
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('All array elements must be numeric.');
        array_dot_product($array);
    }

    /**
     * Test array_dot_product with large numbers.
     */
    public function test_array_dot_product_large_numbers()
    {
        // Set up test data
        $array = [PHP_INT_MAX => 1, PHP_INT_MAX - 1 => 2];

        // Run the function
        $result = array_dot_product($array);

        // Assert the correct result
        $expectedResult = (float)PHP_INT_MAX + 2 * (float)(PHP_INT_MAX - 1);
        $this->assertEquals($expectedResult, $result);
    }

    /**
     * Test array_dot_product with string numeric values.
     */
    public function test_array_dot_product_string_numeric()
    {
        // Set up test data
        $array = ['2' => '3', '4' => '1', '6' => '2'];

        // Run the function
        $result = array_dot_product($array);

        // Assert the correct result
        $this->assertEquals(22, $result);
    }
}
