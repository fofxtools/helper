<?php

/**
 * Date and Time Utility Functions
 *
 * This file contains a collection of functions for handling date and time operations.
 * It includes utilities for precise time measurements, date calculations, and time-based
 * operations with support for microsecond precision.
 *
 * Key features:
 * - High-precision time functions
 * - Date difference calculations
 * - Sleep and rate limiting functions
 */

namespace FOfX\Helper;

/**
 * Return the current time
 *
 * In MySQL, SELECT NOW(6); and SELECT CURTIME(6); can be used to get the timestamp with microseconds precision up to 6 digits
 *
 * From php.net:
 * "Microseconds. Note that date() will always generate 000000 since it takes an int parameter, whereas DateTime::format() does support microseconds if DateTime was created with microseconds."
 * Thus, to get microseconds, use: (new \DateTime())->format('Y-m-d H:i:s.u');
 * Similar in speed to date().
 *
 * @link    https://stackoverflow.com/questions/1995562/now-function-in-php
 * @link    https://stackoverflow.com/questions/8979558/mysql-now-function-with-high-precision
 * @link    https://stackoverflow.com/questions/9624284/current-timestamp-in-milliseconds
 * @link    https://www.php.net/manual/en/datetime.format.php
 *
 * @param bool     $includeMicroseconds Whether to append microseconds
 * @param bool|int $precision           If microseconds, how many digits of microseconds precision to round to. Default precision is 6.
 * @param string   $format              The format of the date and time to return.
 *
 * @return string
 */
function now(bool $includeMicroseconds = false, bool|int $precision = false, string $format = 'Y-m-d H:i:s'): string
{
    // If microseconds are not required, use the simple date() function
    if (!$includeMicroseconds) {
        return date($format);
    }

    // Get the current time with microseconds
    $formattedDateTime = (new \DateTime())->format($format . '.u');

    // If precision is false or out of range, return the full microsecond precision
    if ($precision === false || $precision < 0 || $precision >= 6) {
        return $formattedDateTime;
    }

    // Extract the microseconds part
    $microseconds = (int)substr($formattedDateTime, -6);

    // Round the microseconds to the specified precision
    // Use number_format() to re-add 0's deleted by round()
    // Then use ltrim() to remove leading 0 before the decimal point
    $roundedMicroseconds = ltrim(number_format(round($microseconds / 1e6, $precision), $precision), '0');

    // Combine the main part of the datetime with the rounded microseconds
    return substr($formattedDateTime, 0, -7) . $roundedMicroseconds;
}

/**
 * Return today's date
 *
 * @return string
 */
function today(): string
{
    return date('Y-m-d');
}

/**
 * time() returns the timestamp in seconds as an integer. This function returns the time in microseconds as a float.
 *
 * If $microseconds_only is true, it returns only the microseconds portion.
 * Otherwise, it returns the full timestamp with microseconds.
 *
 * @param bool $microseconds_only If true, returns only the microseconds decimal portion of the timestamp.
 *                                Else returns the full timestamp.
 *
 * @return string The timestamp with or without microseconds.
 */
function get_micro_timestamp(bool $microseconds_only = false): string
{
    $datetime = new \DateTime();

    if ($microseconds_only) {
        return $datetime->format('.u');
    }

    return $datetime->format('U.u');
}

/**
 * Sleep for a random amount of time up to the specified number of seconds.
 *
 * This function pauses the execution of the script for a random duration
 * ranging from 0 up to the specified number of seconds.
 *
 * @param float $seconds The maximum number of seconds to sleep. Must be non-negative.
 *
 * @throws \InvalidArgumentException If $seconds is negative.
 *
 * @return void
 */
function rand_sleep(float $seconds): void
{
    if ($seconds < 0) {
        throw new \InvalidArgumentException('The $seconds parameter must be non-negative.');
    }

    $microseconds        = (int)($seconds * 1000000);
    $random_microseconds = mt_rand(0, $microseconds);
    usleep($random_microseconds);
}

/**
 * Sleep for a specified duration in seconds using a float value.
 *
 * This function allows for fractional sleep durations by converting
 * the specified seconds into microseconds and using usleep().
 *
 * @param float $seconds The number of seconds to sleep. Must be non-negative.
 *
 * @throws \InvalidArgumentException If $seconds is negative.
 *
 * @return void
 */
function float_sleep(float $seconds): void
{
    if ($seconds < 0) {
        throw new \InvalidArgumentException('The $seconds parameter must be non-negative.');
    }

    $microseconds = (int)ceil($seconds * 1000000);
    usleep($microseconds);
}

/**
 * Sleep for a calculated duration based on a rate limit.
 *
 * This function calculates the appropriate sleep time based on the given rate limit
 * and period, ensuring that the rate of actions does not exceed the specified limit.
 *
 * @param float $limit  The maximum number of actions per period. Must be greater than zero.
 * @param float $period The length of the period in seconds. Must be greater than zero.
 *
 * @throws \InvalidArgumentException If $limit or $period is less than or equal to zero.
 *
 * @return void
 *
 * @see     float_sleep()
 */
function limit_sleep(float $limit, float $period = 60): void
{
    if ($limit <= 0) {
        throw new \InvalidArgumentException('The $limit parameter must be greater than zero.');
    }

    if ($period <= 0) {
        throw new \InvalidArgumentException('The $period parameter must be greater than zero.');
    }

    $rate = $period / $limit;
    float_sleep($rate);
}

/**
 * Calculate the difference between two DateTime objects, in seconds, to microseconds accuracy.
 *
 * This function computes the difference between two DateTime objects, returning the result
 * in seconds with microsecond precision. Optionally, the absolute value of the difference can be returned.
 *
 * Based on comment by thflori which contains:
 * var_dump((float)$longTimeAgo->format('U.u') - (float)(new DateTime())->format('U.u'));
 *
 * @link     https://www.php.net/manual/en/dateinterval.format.php
 *
 * @param \DateTime $start_date      The start DateTime object.
 * @param \DateTime $end_date        The end DateTime object.
 * @param bool      $return_absolute If true, returns the absolute value of the difference. Defaults to false.
 *
 * @return float The difference between the two DateTime objects in seconds.
 *
 * @example
 * $start_date = new DateTime('2010-01-01');
 * $end_date = new DateTime('2010-02-01 12:35:12.53');
 * echo "startDate: " . $start_date->format('Y-m-d H:i:s') . PHP_EOL;
 * echo "endDate: " . $end_date->format('Y-m-d H:i:s') . PHP_EOL;
 * $diff = Helper\datetime_diff($start_date, $end_date, false);
 * echo "Helper\datetime_diff(startDate, endDate, false): $diff" . PHP_EOL;
 * $diff = Helper\datetime_diff($start_date, $end_date, true);
 * echo "Helper\datetime_diff(startDate, endDate, true): $diff" . PHP_EOL;
 * echo "Helper\datetime_diff() with endDate and startDate flipped:" . PHP_EOL;
 * $diff = Helper\datetime_diff($end_date, $start_date, false);
 * echo "Helper\datetime_diff(endDate, startDate, false): $diff" . PHP_EOL;
 * $diff = Helper\datetime_diff($end_date, $start_date, true);
 * echo "Helper\datetime_diff(endDate, startDate, true): $diff" . PHP_EOL;
 */
function datetime_diff(\DateTime $start_date, \DateTime $end_date, bool $return_absolute = true): float
{
    $microtime1 = (float)$start_date->format('U.u');
    $microtime2 = (float)$end_date->format('U.u');

    $seconds = $microtime2 - $microtime1;

    if ($return_absolute) {
        $seconds = abs($seconds);
    }

    return $seconds;
}

/**
 * Calculates the normalized time difference between two dates across different timezones.
 *
 * This function assumes UTC timezone if no timezone is specified in the input strings.
 * Optionally, it returns the absolute difference or the difference as a fraction of days.
 *
 * @link     https://stackoverflow.com/questions/72746531/php-how-can-one-find-the-time-difference-in-seconds-between-values-2022-06-24t/72747023
 * @link     https://www.php.net/manual/en/datetime.formats.php
 *
 * @param string $start_date
 * @param string $end_date
 * @param bool   $return_as_absolute If true, returns an absolute value rather than the net difference.
 * @param bool   $convert_to_days    If true, returns as fraction of number of days instead of microseconds
 *
 * @throws \Exception
 *
 * @return float
 *
 * @see      datetime_diff()
 * @see      convert_to_days()
 *
 * @example
 * echo (Helper\normalized_date_diff("2020-01-01 11:30:15.071830840", "2020-06-15 15:45:30.004234253") / 60 / 60 / 24) . " days";
 * @example
 * $date1 = "2020-06-20 15:00:00";
 * $date2 = "2020-06-20 15:00:00 Asia/Tokyo";
 * $difference = Helper\normalized_date_diff($date1, $date2);
 * $difference_flipped_days = Helper\normalized_date_diff($date2, $date1, false, true);
 * echo "Helper\\normalized_date_diff($date1, $date2): " . $difference . PHP_EOL;
 * echo "Helper\\normalized_date_diff($date2, $date1, false, true): " . $difference_flipped_days . PHP_EOL;
 */
function normalized_date_diff(string $start_date, string $end_date, bool $return_as_absolute = false, bool $convert_to_days = false): float
{
    // By adding ", new \DateTimeZone('UTC')" then the UTC default timezone
    // will only be applied if the input string doesn't explicitly contain one.
    try {
        $start_dateTime = new \DateTime($start_date, new \DateTimeZone('UTC'));
        $end_dateTime   = new \DateTime($end_date, new \DateTimeZone('UTC'));
    } catch (\Exception $e) {
        throw new \Exception('Invalid date string provided: ' . $e->getMessage());
    }

    // $start_dateTime->getTimestamp(); will return an int. Seconds, rather than microseconds.
    // Must use ->format('U.u') to get microseconds. So use datetime_diff().
    $difference = datetime_diff($start_dateTime, $end_dateTime, $return_as_absolute);

    // Optionally convert the result from seconds to days
    return $convert_to_days ? convert_to_days($difference) : $difference;
}

/**
 * Converts time from seconds to days.
 *
 * This function takes time in seconds and converts it to days.
 *
 * @param float $seconds The time in seconds.
 *
 * @return float The time in days.
 */
function convert_to_days(float $seconds): float
{
    // Convert seconds to days by dividing by the number of seconds in a day.
    return $seconds / (24 * 60 * 60);
}
