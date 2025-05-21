<?php

declare(strict_types=1);

namespace FOfX\Helper;

use PHPUnit\Framework\TestCase;

class DatetimeTest extends TestCase
{
    /**
     * Test the now function returns the current time
     * without microseconds when $microseconds is FALSE.
     */
    public function test_now_returns_current_time_without_microseconds()
    {
        // Call the now function without microseconds
        $result = now();

        // Assert the result matches the expected format
        $this->assertMatchesRegularExpression('/^\d{4}-\d{2}-\d{2} \d{2}:\d{2}:\d{2}$/', $result);
    }

    /**
     * Test the now function returns the current time
     * with microseconds when $microseconds is TRUE.
     */
    public function test_now_returns_current_time_with_microseconds()
    {
        // Call the now function with microseconds
        $result = now(true);

        // Assert the result matches the expected format
        $this->assertMatchesRegularExpression('/^\d{4}-\d{2}-\d{2} \d{2}:\d{2}:\d{2}\.\d{6}$/', $result);
    }

    /**
     * Test the now function rounds the microseconds
     * correctly when $precision is provided.
     */
    public function test_now_rounds_microseconds_correctly()
    {
        // Call the now function with microseconds and a precision of 3
        $result = now(true, 3);

        // Assert the result matches the expected format with 3 digits of precision
        $this->assertMatchesRegularExpression('/^\d{4}-\d{2}-\d{2} \d{2}:\d{2}:\d{2}\.\d{3}$/', $result);
    }

    /**
     * Test the now function returns the current time
     * without microseconds when $microseconds is TRUE
     * but $precision is set to FALSE.
     */
    public function test_now_returns_microseconds_without_rounding_when_precision_is_false()
    {
        // Call the now function with microseconds but without rounding precision
        $result = now(true, false);

        // Assert the result matches the expected format with full 6 digits of microseconds
        $this->assertMatchesRegularExpression('/^\d{4}-\d{2}-\d{2} \d{2}:\d{2}:\d{2}\.\d{6}$/', $result);
    }

    /**
     * Test the now function handles an invalid precision
     * by returning the full microseconds without rounding.
     */
    public function test_now_handles_invalid_precision()
    {
        // Call the now function with an invalid precision (e.g., 7)
        $result = now(true, 7);

        // Assert the result matches the expected format with full 6 digits of microseconds
        $this->assertMatchesRegularExpression('/^\d{4}-\d{2}-\d{2} \d{2}:\d{2}:\d{2}\.\d{6}$/', $result);
    }

    /**
     * Test the now function handles a custom date format
     * without microseconds.
     */
    public function test_now_handles_custom_date_format_without_microseconds()
    {
        // Define a custom date format
        $format = 'Ymd-His';

        // Call the now function with the custom format
        $result = now(false, false, $format);

        // Assert the result matches the expected custom format
        $this->assertMatchesRegularExpression('/^\d{8}-\d{6}$/', $result);
    }

    /**
     * Test the now function handles a custom date format
     * with microseconds.
     */
    public function test_now_handles_custom_date_format_with_microseconds()
    {
        // Define a custom date format
        $format = 'Ymd-His';

        // Call the now function with the custom format and microseconds
        $result = now(true, false, $format);

        // Assert the result matches the expected custom format with microseconds
        $this->assertMatchesRegularExpression('/^\d{8}-\d{6}\.\d{6}$/', $result);
    }

    /**
     * Test that the today function returns today's date
     * in the correct format 'Y-m-d'.
     */
    public function test_today_returns_correct_date_format()
    {
        // Get the expected date in 'Y-m-d' format
        $expected = date('Y-m-d');

        // Call the today function
        $result = today();

        // Assert that the result matches the expected date
        $this->assertEquals($expected, $result);
    }

    /**
     * Test that the today function returns a valid date string.
     */
    public function test_today_returns_valid_date_string()
    {
        // Call the today function
        $result = today();

        // Assert that the result matches the regular expression for a valid date
        $this->assertMatchesRegularExpression('/^\d{4}-\d{2}-\d{2}$/', $result);
    }

    /**
     * Test the today function when the system timezone is set to UTC.
     */
    public function test_today_function_with_utc_timezone()
    {
        // Set the timezone to UTC
        date_default_timezone_set('UTC');

        // Get the expected date in 'Y-m-d' format
        $expected = date('Y-m-d');

        // Call the today function
        $result = today();

        // Assert that the result matches the expected date
        $this->assertEquals($expected, $result);

        // Reset the timezone to default
        date_default_timezone_set(ini_get('date.timezone'));
    }

    /**
     * Test the today function with a non-default timezone.
     */
    public function test_today_function_with_non_default_timezone()
    {
        // Set the timezone to 'America/New_York'
        date_default_timezone_set('America/New_York');

        // Get the expected date in 'Y-m-d' format
        $expected = date('Y-m-d');

        // Call the today function
        $result = today();

        // Assert that the result matches the expected date
        $this->assertEquals($expected, $result);

        // Reset the timezone to default
        date_default_timezone_set(ini_get('date.timezone'));
    }

    /**
     * Test that get_micro_timestamp returns the full timestamp
     * with microseconds when $microseconds_only is FALSE.
     */
    public function test_get_micro_timestamp_returns_full_timestamp_with_microseconds()
    {
        // Call the function with $microseconds_only set to FALSE
        $result = get_micro_timestamp(false);

        // Assert that the result matches the expected format of a full timestamp with microseconds
        $this->assertMatchesRegularExpression('/^\d{10}\.\d{6}$/', $result);
    }

    /**
     * Test that get_micro_timestamp returns only the microseconds
     * portion when $microseconds_only is TRUE.
     */
    public function test_get_micro_timestamp_returns_only_microseconds()
    {
        // Call the function with $microseconds_only set to TRUE
        $result = get_micro_timestamp(true);

        // Assert that the result matches the expected format of just the microseconds portion
        $this->assertMatchesRegularExpression('/^\.\d{6}$/', $result);
    }

    /**
     * Test that get_micro_timestamp correctly handles edge cases
     * such as a timestamp with 0 microseconds.
     */
    public function test_get_micro_timestamp_handles_zero_microseconds()
    {
        // Create a DateTime object with a specific timestamp
        $datetime = \DateTime::createFromFormat('U.u', '1234567890.000000');

        // Assert that the full timestamp includes zero microseconds
        $this->assertEquals('1234567890.000000', $datetime->format('U.u'));

        // Assert that the microseconds portion is zero
        $this->assertEquals('.000000', $datetime->format('.u'));
    }

    /**
     * Test that get_micro_timestamp does not break with an invalid date format.
     * This is more of a stability test to ensure the function behaves predictably.
     */
    public function test_get_micro_timestamp_with_invalid_date_format()
    {
        // Simulate an invalid date format handling
        $result = get_micro_timestamp(false);

        // Assert that the result is still a valid timestamp with microseconds
        $this->assertMatchesRegularExpression('/^\d{10}\.\d{6}$/', $result);
    }

    /**
     * Test that rand_sleep does not throw an exception
     * for a valid non-negative $seconds input.
     */
    public function test_rand_sleep_with_valid_input()
    {
        $seconds = .005;

        // Use a small sleep time for testing
        $start = microtime(true);
        rand_sleep($seconds);
        $end = microtime(true);

        // Assert that the sleep duration is within the expected range
        $this->assertGreaterThanOrEqual(0, $end - $start);

        // Allow a small margin for system overhead
        $this->assertLessThanOrEqual($seconds + 0.04, $end - $start);
    }

    /**
     * Test that rand_sleep does not sleep at all
     * when $seconds is 0.
     */
    public function test_rand_sleep_with_zero_seconds()
    {
        $seconds = 0.0;

        $start = microtime(true);
        rand_sleep($seconds);
        $end = microtime(true);

        // Assert that the sleep duration is effectively zero
        $this->assertLessThan(0.01, $end - $start);
    }

    /**
     * Test that rand_sleep throws an InvalidArgumentException
     * when $seconds is negative.
     */
    public function test_rand_sleep_with_negative_seconds()
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('The $seconds parameter must be non-negative.');

        // Call the function with a negative value to trigger the exception
        rand_sleep(-1.0);
    }

    /**
     * Test that rand_sleep can handle a very small positive value
     * for $seconds.
     */
    public function test_rand_sleep_with_small_positive_value()
    {
        $seconds = 0.0001;

        $start = microtime(true);
        rand_sleep($seconds);
        $end = microtime(true);

        $actual_sleep_duration = $end - $start;

        // Assert that the sleep duration is at least 0 (it could be 0)
        $this->assertGreaterThanOrEqual(0, $actual_sleep_duration);

        // Assert that the sleep duration does not exceed the specified seconds
        // Allow for some system overhead time
        $this->assertLessThanOrEqual($seconds + 0.04, $actual_sleep_duration);
    }

    /**
     * Test that float_sleep does not throw an exception
     * for a valid non-negative $seconds input.
     */
    public function test_float_sleep_with_valid_input()
    {
        $seconds = 0.05;

        // Record the start time
        $start = microtime(true);

        // Call the float_sleep function
        float_sleep($seconds);

        // Record the end time
        $end = microtime(true);

        // Calculate the actual sleep duration
        $actual_sleep_duration = $end - $start;

        // Assert that the sleep duration is within the expected range
        $this->assertGreaterThanOrEqual($seconds, $actual_sleep_duration + 0.04);
        $this->assertLessThanOrEqual($seconds + 0.04, $actual_sleep_duration);
    }

    /**
     * Test that float_sleep does not sleep at all
     * when $seconds is 0.
     */
    public function test_float_sleep_with_zero_seconds()
    {
        $seconds = 0.0;

        // Record the start time
        $start = microtime(true);

        // Call the float_sleep function
        float_sleep($seconds);

        // Record the end time
        $end = microtime(true);

        // Calculate the actual sleep duration
        $actual_sleep_duration = $end - $start;

        // Assert that the sleep duration is effectively zero
        $this->assertLessThan(0.04, $actual_sleep_duration);
    }

    /**
     * Test that float_sleep throws an InvalidArgumentException
     * when $seconds is negative.
     */
    public function test_float_sleep_with_negative_seconds()
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('The $seconds parameter must be non-negative.');

        // Call the function with a negative value to trigger the exception
        float_sleep(-1.0);
    }

    /**
     * Test that float_sleep can handle a very small positive value
     * for $seconds.
     */
    public function test_float_sleep_with_small_positive_value()
    {
        $seconds = 0.001;

        // Record the start time
        $start = microtime(true);

        // Call the float_sleep function
        float_sleep($seconds);

        // Record the end time
        $end = microtime(true);

        // Calculate the actual sleep duration
        $actual_sleep_duration = $end - $start;

        // Assert that the sleep duration is within the expected range
        $this->assertGreaterThanOrEqual($seconds, $actual_sleep_duration + 0.04);
        $this->assertLessThanOrEqual($seconds + 0.04, $actual_sleep_duration);
    }

    /**
     * Test that float_sleep can handle a value close to the upper limit
     * of 0.05 seconds.
     */
    public function test_float_sleep_with_upper_limit_value()
    {
        $seconds = 0.05;

        // Record the start time
        $start = microtime(true);

        // Call the float_sleep function
        float_sleep($seconds);

        // Record the end time
        $end = microtime(true);

        // Calculate the actual sleep duration
        $actual_sleep_duration = $end - $start;

        // Assert that the sleep duration is within the expected range
        $this->assertGreaterThanOrEqual($seconds, $actual_sleep_duration + 0.04);
        $this->assertLessThanOrEqual($seconds + 0.04, $actual_sleep_duration);
    }

    /**
     * Test that limit_sleep correctly calculates the sleep duration
     * based on the provided limit and period, ensuring the sleep time
     * is no more than 0.05 seconds.
     */
    public function test_limit_sleep_with_valid_input()
    {
        $limit  = 20.0;
        $period = 1.0;

        // Calculate the expected sleep time
        $expected_sleep_time = $period / $limit;

        // Ensure the expected sleep time is within the acceptable range
        $this->assertLessThanOrEqual(0.05, $expected_sleep_time);

        // Record the start time
        $start = microtime(true);

        // Call the limit_sleep function
        limit_sleep($limit, $period);

        // Record the end time
        $end = microtime(true);

        // Calculate the actual sleep duration
        $actual_sleep_duration = $end - $start;

        // Assert that the sleep duration is within the expected range
        $this->assertGreaterThanOrEqual($expected_sleep_time - 0.04, $actual_sleep_duration);
        $this->assertLessThanOrEqual($expected_sleep_time + 0.04, $actual_sleep_duration);
    }

    /**
     * Test that limit_sleep works correctly with the default period.
     * The expected sleep time is capped to no more than 0.05 seconds.
     */
    public function test_limit_sleep_with_default_period()
    {
        $limit = 1200.0;

        // The default period is 60 seconds
        $expected_sleep_time = 60.0 / $limit;

        // Ensure the expected sleep time is within the acceptable range
        $this->assertLessThanOrEqual(0.05, $expected_sleep_time);

        // Record the start time
        $start = microtime(true);

        // Call the limit_sleep function with the default period
        limit_sleep($limit);

        // Record the end time
        $end = microtime(true);

        // Calculate the actual sleep duration
        $actual_sleep_duration = $end - $start;

        // Assert that the sleep duration is within the expected range
        $this->assertGreaterThanOrEqual($expected_sleep_time - 0.04, $actual_sleep_duration);
        $this->assertLessThanOrEqual($expected_sleep_time + 0.04, $actual_sleep_duration);
    }

    /**
     * Test that limit_sleep handles a large limit,
     * resulting in minimal sleep time, ensuring no more than 0.05 seconds.
     */
    public function test_limit_sleep_with_large_limit()
    {
        $limit  = 1000.0;
        $period = 1.0;

        // Calculate the expected sleep time
        $expected_sleep_time = $period / $limit;

        // Ensure the expected sleep time is within the acceptable range
        $this->assertLessThanOrEqual(0.05, $expected_sleep_time);

        // Record the start time
        $start = microtime(true);

        // Call the limit_sleep function
        limit_sleep($limit, $period);

        // Record the end time
        $end = microtime(true);

        // Calculate the actual sleep duration
        $actual_sleep_duration = $end - $start;

        // Assert that the sleep duration is within the expected range
        $this->assertGreaterThanOrEqual($expected_sleep_time - 0.06, $actual_sleep_duration);
        $this->assertLessThanOrEqual($expected_sleep_time + 0.06, $actual_sleep_duration);
    }

    /**
     * Test that limit_sleep handles a small limit,
     * resulting in a sleep time capped at 0.05 seconds.
     */
    public function test_limit_sleep_with_small_limit()
    {
        $limit  = 20.0;
        $period = 1.0;

        // Calculate the expected sleep time
        $expected_sleep_time = $period / $limit;

        // Ensure the expected sleep time is within the acceptable range
        $this->assertLessThanOrEqual(0.05, $expected_sleep_time);

        // Record the start time
        $start = microtime(true);

        // Call the limit_sleep function
        limit_sleep($limit, $period);

        // Record the end time
        $end = microtime(true);

        // Calculate the actual sleep duration
        $actual_sleep_duration = $end - $start;

        // Assert that the sleep duration is within the expected range
        $this->assertGreaterThanOrEqual($expected_sleep_time - 0.05, $actual_sleep_duration);
        $this->assertLessThanOrEqual($expected_sleep_time + 0.05, $actual_sleep_duration);
    }

    /**
     * Test that limit_sleep throws an InvalidArgumentException
     * when the limit is less than or equal to zero.
     */
    public function test_limit_sleep_with_zero_or_negative_limit()
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('The $limit parameter must be greater than zero.');

        // Test with zero limit
        limit_sleep(0);

        // Test with a negative limit
        limit_sleep(-5.0);
    }

    /**
     * Test that limit_sleep throws an InvalidArgumentException
     * when the period is less than or equal to zero.
     */
    public function test_limit_sleep_with_zero_or_negative_period()
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('The $period parameter must be greater than zero.');

        // Test with zero period
        limit_sleep(10.0, 0);

        // Test with a negative period
        limit_sleep(10.0, -60.0);
    }

    /**
     * Test that datetime_diff correctly calculates the difference
     * between two DateTime objects, returning a positive difference.
     */
    public function test_datetime_diff_with_positive_difference()
    {
        $startDate = new \DateTime('2023-01-01 00:00:00.000000');
        $endDate   = new \DateTime('2023-01-01 00:00:01.500000');

        // The expected difference is 1.5 seconds
        $expectedDifference = 1.5;

        // Call the datetime_diff function
        $diff = datetime_diff($startDate, $endDate, true);

        // Assert that the difference is as expected
        $this->assertEquals($expectedDifference, $diff);
    }

    /**
     * Test that datetime_diff correctly calculates the difference
     * between two DateTime objects, returning a negative difference
     * when return_absolute is set to false.
     */
    public function test_datetime_diff_with_negative_difference()
    {
        $startDate = new \DateTime('2023-01-01 00:00:01.500000');
        $endDate   = new \DateTime('2023-01-01 00:00:00.000000');

        // The expected difference is -1.5 seconds when not using absolute value
        $expectedDifference = -1.5;

        // Call the datetime_diff function with return_absolute as false
        $diff = datetime_diff($startDate, $endDate, false);

        // Assert that the difference is as expected
        $this->assertEquals($expectedDifference, $diff);
    }

    /**
     * Test that datetime_diff returns the absolute difference
     * between two DateTime objects when return_absolute is set to true.
     */
    public function test_datetime_diff_with_absolute_difference()
    {
        $startDate = new \DateTime('2023-01-01 00:00:01.500000');
        $endDate   = new \DateTime('2023-01-01 00:00:00.000000');

        // The expected absolute difference is 1.5 seconds
        $expectedDifference = 1.5;

        // Call the datetime_diff function with return_absolute as true
        $diff = datetime_diff($startDate, $endDate, true);

        // Assert that the difference is as expected
        $this->assertEquals($expectedDifference, $diff);
    }

    /**
     * Test that datetime_diff returns zero when both DateTime objects
     * are exactly the same.
     */
    public function test_datetime_diff_with_identical_dates()
    {
        $date = new \DateTime('2023-01-01 00:00:00.000000');

        // The expected difference is 0 seconds
        $expectedDifference = 0.0;

        // Call the datetime_diff function with identical dates
        $diff = datetime_diff($date, $date, true);

        // Assert that the difference is as expected
        $this->assertEquals($expectedDifference, $diff);
    }

    /**
     * Test that datetime_diff handles microsecond-level differences
     * accurately, with a tolerance for floating-point precision.
     */
    public function test_datetime_diff_with_microsecond_difference()
    {
        $startDate = new \DateTime('2023-01-01 00:00:00.000000');
        $endDate   = new \DateTime('2023-01-01 00:00:00.000500');

        // The expected difference is 0.0005 seconds
        $expectedDifference = 0.0005;

        // Call the datetime_diff function
        $diff = datetime_diff($startDate, $endDate, true);

        // Assert that the difference is as expected within a tolerance
        $this->assertEqualsWithDelta($expectedDifference, $diff, 0.00001);
    }

    /**
     * Test that datetime_diff correctly handles when the startDate is
     * after the endDate, returning a positive difference when using absolute value.
     */
    public function test_datetime_diff_with_start_date_after_end_date()
    {
        $startDate = new \DateTime('2023-01-01 00:00:02.000000');
        $endDate   = new \DateTime('2023-01-01 00:00:01.000000');

        // The expected absolute difference is 1 second
        $expectedDifference = 1.0;

        // Call the datetime_diff function
        $diff = datetime_diff($startDate, $endDate, true);

        // Assert that the difference is as expected
        $this->assertEquals($expectedDifference, $diff);
    }

    /**
     * Test that normalized_date_diff correctly calculates the difference
     * between two dates in seconds.
     */
    public function test_normalized_date_diff_with_valid_dates()
    {
        $start_date = '2023-01-01 00:00:00';
        $end_date   = '2023-01-01 12:00:00';

        // The expected difference is 12 hours, or 43200 seconds
        $expected_difference = 43200.0;

        // Call the function
        $difference = normalized_date_diff($start_date, $end_date);

        // Assert that the difference is as expected
        $this->assertEquals($expected_difference, $difference);
    }

    /**
     * Test that normalized_date_diff returns the absolute difference
     * between two dates when return_as_absolute is true.
     */
    public function test_normalized_date_diff_with_absolute_difference()
    {
        $start_date = '2023-01-01 12:00:00';
        $end_date   = '2023-01-01 00:00:00';

        // The expected absolute difference is 12 hours, or 43200 seconds
        $expected_difference = 43200.0;

        // Call the function with return_as_absolute as true
        $difference = normalized_date_diff($start_date, $end_date, true);

        // Assert that the difference is as expected
        $this->assertEquals($expected_difference, $difference);
    }

    /**
     * Test that normalized_date_diff correctly calculates the difference
     * in days when convert_to_days is true.
     */
    public function test_normalized_date_diff_with_days_conversion()
    {
        $start_date = '2023-01-01 00:00:00';
        $end_date   = '2023-01-02 00:00:00';

        // The expected difference is 1 day
        $expected_difference_in_days = 1.0;

        // Call the function with convert_to_days as true
        $difference = normalized_date_diff($start_date, $end_date, false, true);

        // Assert that the difference is as expected
        $this->assertEquals($expected_difference_in_days, $difference);
    }

    /**
     * Test that normalized_date_diff throws an exception for invalid date strings.
     */
    public function test_normalized_date_diff_with_invalid_date_string()
    {
        $this->expectException(\Exception::class);

        // Call the function with an invalid date string
        normalized_date_diff('invalid-date', '2023-01-01 12:00:00');
    }

    /**
     * Test that normalized_date_diff correctly handles dates with different timezones.
     */
    public function test_normalized_date_diff_with_different_timezones()
    {
        $start_date = '2023-01-01 00:00:00 UTC';
        $end_date   = '2023-01-01 12:00:00 Asia/Tokyo';

        // The expected difference, accounting for the timezone difference, is 3 hours, or 10800 seconds
        $expected_difference = 10800.0;

        // Call the function
        $difference = normalized_date_diff($start_date, $end_date);

        // Assert that the difference is as expected
        $this->assertEquals($expected_difference, $difference);
    }

    /**
     * Test that normalized_date_diff returns zero when both dates are the same.
     */
    public function test_normalized_date_diff_with_identical_dates()
    {
        $date = '2023-01-01 00:00:00';

        // The expected difference is 0 seconds
        $expected_difference = 0.0;

        // Call the function with identical dates
        $difference = normalized_date_diff($date, $date);

        // Assert that the difference is as expected
        $this->assertEquals($expected_difference, $difference);
    }

    /**
     * Test that convert_to_days correctly converts seconds to days.
     */
    public function test_convert_to_days_with_valid_input()
    {
        // 1 day in seconds (24 * 60 * 60)
        $seconds = 86400;

        // The expected result is 1 day
        $expected_days = 1.0;

        // Call the function
        $days = convert_to_days($seconds);

        // Assert that the conversion is as expected
        $this->assertEquals($expected_days, $days);
    }

    /**
     * Test that convert_to_days correctly handles zero seconds.
     */
    public function test_convert_to_days_with_zero_input()
    {
        $seconds = 0.0;

        // The expected result is 0 days
        $expected_days = 0.0;

        // Call the function
        $days = convert_to_days($seconds);

        // Assert that the conversion is as expected
        $this->assertEquals($expected_days, $days);
    }

    /**
     * Test that convert_to_days correctly handles negative seconds.
     */
    public function test_convert_to_days_with_negative_input()
    {
        // -1 day in seconds
        $seconds = -86400;

        // The expected result is -1 day
        $expected_days = -1.0;

        // Call the function
        $days = convert_to_days($seconds);

        // Assert that the conversion is as expected
        $this->assertEquals($expected_days, $days);
    }

    /**
     * Test that convert_to_days handles a very small positive input,
     * ensuring that it doesn't incorrectly round to zero.
     */
    public function test_convert_to_days_with_small_positive_input()
    {
        // 1 second
        $seconds = 1;

        // The expected result is approximately 1/86400 days (1 second / 86,400 seconds in a day)
        $expected_days = 1 / 86400;

        // Call the function
        $days = convert_to_days($seconds);

        // Assert that the conversion is as expected
        $this->assertEqualsWithDelta($expected_days, $days, 0.0000001);
    }

    /**
     * Test that convert_to_days handles a very large positive input,
     * verifying that the function correctly scales with large numbers.
     */
    public function test_convert_to_days_with_large_positive_input()
    {
        // 100 days in seconds (100 * 24 * 60 * 60)
        $seconds = 8640000;

        // The expected result is 100 days
        $expected_days = 100.0;

        // Call the function
        $days = convert_to_days($seconds);

        // Assert that the conversion is as expected
        $this->assertEquals($expected_days, $days);
    }
}
