<?php

/**
 * Mathematical Utility Functions
 *
 * This file provides a set of mathematical utility functions.
 * It includes operations for probability calculations, random number generation,
 * and other mathematical operations.
 *
 * Key features:
 * - Probability and randomization functions
 * - Hashing-based selection functions
 * - Numeric type checking and conversions
 */

declare(strict_types=1);

namespace FOfX\Helper;

/**
 * Generate a cryptographically secure random probability between 0 (inclusive) and 1 (exclusive).
 *
 * This function uses random_int() which is suitable for cryptographic purposes,
 * providing a higher level of randomness and security compared to mt_rand().
 * The function guarantees that the returned value is strictly less than 1.
 *
 * @throws \Exception If it was not possible to gather sufficient entropy
 *
 * @return float A random probability between 0 (inclusive) and 1 (exclusive)
 */
function random_probability(): float
{
    return random_int(0, PHP_INT_MAX - 1) / PHP_INT_MAX;
}

/**
 * Generate a random float within a specified range.
 *
 * This function uses random_probability() to generate a cryptographically secure
 * random float between the specified minimum and maximum values
 *
 * @param float $min The minimum value of the range
 * @param float $max The maximum value of the range
 *
 * @throws \InvalidArgumentException If $min is greater than $max
 *
 * @return float A random float between $min and $max
 *
 * @see     random_probability()
 */
function rand_float(float $min, float $max): float
{
    if ($min > $max) {
        throw new \InvalidArgumentException('random_float() - Error: $min must be less than or equal to $max.');
    }

    $diff         = $max - $min;
    $randomScalar = random_probability();

    return $min + ($randomScalar * $diff);
}

/**
 * Convert a string into a probability between 0 (inclusive) and 1 (exclusive).
 *
 * This function allows for consistent randomness based on the input string.
 * It uses SHA-256 hashing to generate a probability value.
 *
 * @param string $input The input string to convert to a probability
 *
 * @return float A probability value between [0, 1)
 */
function hashed_probability(string $input): float
{
    $hash = hash('sha256', $input);
    // SHA-256 produces a 64-character hex string
    $maxHashValue = str_repeat('f', 64);

    $numerator = hexdec($hash);
    // Add 1 to avoid division by zero and ensure result < 1
    $denominator = hexdec($maxHashValue) + 1;

    return $numerator / $denominator;
}

/**
 * Select an array element based on a hashed probability.
 *
 * This function uses hashed_probability() to generate a consistent random index
 * for the given array based on the input string. It ensures that the selection
 * is uniformly distributed across all array elements.
 *
 * Must use floor() and count($keys) rather than round() and count($keys) - 1.
 * Otherwise with round(), the first and last indexes only get half the weights as the other values.
 * To avoid issues with the selected index being out of range, the hashed_probability() must be less than 1.0.
 *
 * @param string $string The input string used to generate the hash
 * @param array  $array  The array from which to select an element
 *
 * @throws \InvalidArgumentException If the input array is empty
 *
 * @return mixed The selected element from the array
 *
 * @see      hashed_probability()
 *
 * @example
 * $string = "Hello world.";
 * $array = ["Apples", "Bananas", "Oranges", "Pears", "Pineapples"];
 * $counts = [];
 * for ($i = 0; $i < 100000; $i++) {
 *     $value = Helper\hashed_array_element($string . $i, $array);
 *     if (!isset($counts[$value])) $counts[$value] = 1;
 *     else $counts[$value]++;
 * }
 * ksort($counts);
 * print_r($counts);
 */
function hashed_array_element(string $string, array $array): mixed
{
    if (empty($array)) {
        throw new \InvalidArgumentException('The input array must not be empty.');
    }

    // Hashed probability should be in the range [0, 1)
    $probability = hashed_probability($string);
    $keys        = array_keys($array);

    // Since the probability is less than 1, we can use count($keys) rather than count($keys) - 1
    // And floor() rather than round()
    $max   = count($keys);
    $index = floor($probability * $max);
    $value = $array[$keys[$index]];

    return $value;
}

/**
 * Calculates the expected number of duplicates in a sample.
 *
 * This function uses the formula for expected duplicates in a sampling scenario
 * where each object has an equal probability of being sampled, and sampling is
 * done with replacement.
 *
 * @link     https://math.stackexchange.com/questions/1988021/how-to-compute-the-expected-number-of-duplicates
 *
 * @param int        $total_objects      The total number of objects in the population.
 * @param int        $sample_size        The number of objects being sampled.
 * @param float|null $sample_probability The probability of each object being sampled. Default is 1 / $total_objects.
 *
 * @throws \InvalidArgumentException If input parameters are invalid.
 *
 * @return float The expected number of duplicates.
 *
 * @example
 * $total_objects = 100;
 * $sample_size = 10;
 * $expected_duplicates = Helper\expected_duplicates($total_objects, $sample_size);
 * echo "Expected duplicates: " . $expected_duplicates;
 * // Output: Expected duplicates: 0.43820750088024
 */
function expected_duplicates(int $total_objects, int $sample_size, ?float $sample_probability = null): float
{
    // Validate input parameters
    if ($total_objects <= 0 || $sample_size < 0) {
        throw new \InvalidArgumentException('Total objects must be positive and sample size must be non-negative.');
    }

    // Base case: no duplicates possible if sample size is less than 2
    if ($sample_size < 2) {
        return 0.0;
    }

    // If no sample probability is provided, assume uniform distribution
    $sample_probability = $sample_probability ?? (1 / $total_objects);

    // Validate sample probability
    if ($sample_probability <= 0 || $sample_probability > 1) {
        throw new \InvalidArgumentException('Sample probability must be between 0 and 1.');
    }

    // Calculate the sum of probabilities that each object is not sampled
    // This is equivalent to: sum((1 - p)^k for _ in range(n))
    $sum = array_sum(array_fill(0, $total_objects, pow(1 - $sample_probability, $sample_size)));

    // Calculate expected duplicates:
    // E[duplicates] = k - n + sum((1 - p)^k for _ in range(n))
    // where k is sample_size, n is total_objects, and p is sample_probability
    return $sample_size - $total_objects + $sum;
}

/**
 * Samples the number of duplicates in random selections.
 *
 * Samples objects from a pool of total objects, with replacement.
 *
 * @param int $total_objects The total number of unique objects available.
 * @param int $sample_size   The number of objects randomly selected in each trial.
 * @param int $trials        The number of trials to conduct. Default is 1.
 *
 * @throws \InvalidArgumentException If input parameters are invalid.
 *
 * @return float The average number of duplicates across all trials.
 *
 * @example
 * $total_objects = 100;
 * $sample_size = 10;
 * $trials = 1000;
 * $average_duplicates = Helper\sample_duplicates($total_objects, $sample_size, $trials);
 * echo "Average duplicates: " . $average_duplicates . PHP_EOL;
 */
function sample_duplicates(int $total_objects, int $sample_size, int $trials = 1): float
{
    // Validate input parameters
    if ($total_objects <= 0 || $sample_size <= 0 || $trials <= 0) {
        throw new \InvalidArgumentException('All parameters must be positive integers.');
    }

    $duplicates = [];

    // Conduct sampling trials
    for ($i = 0; $i < $trials; $i++) {
        // Generate a random sample
        $sample = [];
        for ($j = 0; $j < $sample_size; $j++) {
            $sample[] = random_int(0, $total_objects - 1);
        }

        // Calculate number of duplicates in this trial
        $duplicates[] = $sample_size - count(array_unique($sample));
    }

    // Calculate and return the average number of duplicates
    return array_sum($duplicates) / $trials;
}

/**
 * Estimates the total population size based on the sample size and the number of duplicates observed.
 *
 * This function guesses the total population size by incrementing from 1 upwards
 * until the expected number of duplicates matches or exceeds the observed duplicates.
 * The result is only an estimate and may be significantly off depending on the argument values.
 *
 * @param int       $sample_size         The number of objects being sampled.
 * @param int|float $observed_duplicates The number of duplicates observed in the sample.
 *
 * @throws \InvalidArgumentException If input parameters are invalid.
 *
 * @return ?int The estimated total population size,
 *              or null if the estimate could not be made.
 *
 * @see      expected_duplicates
 *
 * @example
 * $n_objects = 100000;
 * $k_sample = 2000;
 * $expected = Helper\expected_duplicates($n_objects, $k_sample);
 * echo "expected_duplicates($n_objects, $k_sample): " . $expected . PHP_EOL;
 * $sampled = Helper\sample_duplicates($n_objects, $k_sample);
 * echo "sample_duplicates($n_objects, $k_sample): " . $sampled . PHP_EOL;
 * $estimate = Helper\estimate_total_from_duplicates($k_sample, $expected);
 * echo "estimate_total_from_duplicates($k_sample, $expected): " . $estimate . PHP_EOL;
 * // This takes over a minute and gives an estimate of 104544
 */
function estimate_total_from_duplicates(int $sample_size, int|float $observed_duplicates): ?int
{
    // Validate input parameters
    if ($sample_size <= 0 || $observed_duplicates < 0) {
        throw new \InvalidArgumentException('Sample size must be positive and duplicates must be non-negative.');
    }

    // Start guessing the total population size from 1 upwards
    for ($population_estimate = 1; $population_estimate < $sample_size * 100; $population_estimate++) {
        $expected_duplicates = expected_duplicates($population_estimate, $sample_size);

        if ($expected_duplicates <= $observed_duplicates) {
            return $population_estimate;
        }
    }

    // Return null if no suitable estimate was found
    return null;
}

/**
 * Find the minimum difference between elements in an array.
 *
 * @param array $array
 * @param bool  $ensure_nonnegative If set, all elements must be non-negative.
 *
 * @throws \InvalidArgumentException
 *
 * @return float
 *
 * @see      array_is_positive_numeric
 * @see      array_is_numeric
 *
 * @example
 * $array = [.05, .085, .15, .01, 1.2, .7, .64];
 * $min_diff = Helper\array_minimum_difference($array, true);
 * echo "array_minimum_difference(): $min_diff" . PHP_EOL;
 */
function array_minimum_difference(array $array, bool $ensure_nonnegative = false): float
{
    // Validate input
    if (empty($array)) {
        throw new \InvalidArgumentException('Error: The array is empty.');
    }
    if (count($array) < 2) {
        throw new \InvalidArgumentException('Error: The array must contain at least two elements.');
    }
    if ($ensure_nonnegative && !array_is_positive_numeric($array)) {
        throw new \InvalidArgumentException('Error: The array must contain only non-negative numbers.');
    } elseif (!array_is_numeric($array)) {
        throw new \InvalidArgumentException('Error: The array must contain only numbers.');
    }

    $values = array_values($array);
    sort($values);

    $differences = [];
    for ($i = 0; $i < count($values) - 1; $i++) {
        $difference    = $values[$i + 1] - $values[$i];
        $differences[] = $difference;
    }

    return min($differences);
}

/**
 * Get a random array element based on weights from another array.
 *
 * @link     https://w-shadow.com/blog/2008/12/10/fast-weighted-random-choice-in-php/
 *
 * @param array $array                     The array of elements to choose from.
 * @param array $weights                   The array of weights corresponding to the elements.
 * @param bool  $validate_positive_weights Whether to validate that all weights are positive.
 *
 * @throws \InvalidArgumentException If the arrays have mismatched counts or weights are invalid.
 *
 * @return mixed The randomly selected element.
 *
 * @see      array_is_positive_numeric()
 * @see      rand_float()
 *
 * @example
 * $array = [0.05, 0.085, 0.15, 0.01, 1.2, 0.7, 0.64];
 * $weights = [10, 2, 5, 10, 20, 5, 30];
 * $counts = [];
 * $trials = array_sum($weights);
 * for ($i = 0; $i < $trials; $i++) {
 *     $value = (string) Helper\array_random_element_weighted($array, $weights, true);
 *     $counts[$value] = ($counts[$value] ?? 0) + 1;
 * }
 * print_r($counts);
 */
function array_random_element_weighted(array $array, array $weights, bool $validate_positive_weights = false): mixed
{
    if (empty($array) || empty($weights)) {
        throw new \InvalidArgumentException('Error: The array and weights can not be empty.');
    }

    // Get the count of elements in both arrays
    $array_count   = count($array);
    $weights_count = count($weights);
    // Check if the counts of both arrays match
    if ($array_count !== $weights_count) {
        throw new \InvalidArgumentException(
            "Count mismatch: array ({$array_count}) does not match weights ({$weights_count})."
        );
    }

    // Validate that all weights are positive if the flag is set
    // Can potentially slow down function significantly, so $validate_positive_weights should probably be false
    if ($validate_positive_weights && !array_is_positive_numeric($weights)) {
        throw new \InvalidArgumentException('All weights must be positive.');
    }

    // Calculate the total weight
    $total_weight = array_sum($weights);

    // Use rand_float() to find the random point in the array
    $random_point = rand_float(0, $total_weight);

    // Initialize the accumulated weight
    $accumulated_weight = 0;

    // Iterate through the weights to find the selected element
    foreach ($weights as $index => $weight) {
        $accumulated_weight += $weight;
        if ($accumulated_weight >= $random_point) {
            // We've found our element, return it
            return $array[$index];
        }
    }

    // This point should never be reached if the weights sum to a positive value
    // If we get here, something went wrong with our calculation
    throw new \RuntimeException('Failed to select a weighted random element.');
}

/**
 * Create an array sorted randomly by weights from another array.
 *
 * If you only need one item, it is faster to use array_random_element_weighted(),
 * since that uses break once the item is found.
 *
 * @param array $array             The array to be sorted.
 * @param array $weights           The array of weights corresponding to the elements.
 * @param bool  $validate_positive Whether to validate with array_is_positive_numeric().
 *
 * @throws \InvalidArgumentException If the arrays have mismatched counts or weights are invalid.
 *
 * @return array The randomly sorted array.
 *
 * @see      array_is_positive_numeric()
 * @see      rand_float()
 *
 * @example
 * $array = [0.05, 0.085, 0.15, 0.01, 1.2, 0.7, 0.64];
 * $weights = [10, 2, 5, 10, 20, 5, 30];
 * $sorted = Helper\array_weighted_sort($array, $weights, true);
 * print_r($sorted);
 */
function array_weighted_sort(array $array, array $weights, bool $validate_positive = false): array
{
    $array_count   = count($array);
    $weights_count = count($weights);

    if ($array_count !== $weights_count) {
        throw new \InvalidArgumentException(
            "Count mismatch: array ({$array_count}) does not match weights ({$weights_count})."
        );
    }

    if ($validate_positive && !array_is_positive_numeric($weights)) {
        throw new \InvalidArgumentException('All weights must be positive.');
    }

    $total_weight              = array_sum($weights);
    $random_weight_differences = array_map(
        fn ($weight) => $weight * rand_float(0, $total_weight),
        $weights
    );

    array_multisort($random_weight_differences, SORT_DESC, $array);

    return $array;
}

/**
 * Calculates the sum of the scalar products of the keys and values in an array,
 * where each key is multiplied by its corresponding value.
 *
 * This function iterates over the array, multiplying each key by its associated value, and then sums
 * the results to produce the final sum.
 *
 * @param array $array The input array
 *
 * @throws \InvalidArgumentException If the array is empty or contains non-numeric values.
 *
 * @return float The resulting sum
 *
 * @example
 * $data = ['1.5' => .5, 2 => 3, 4 => 1, 6 => 2];
 * $result = Helper\array_dot_product($data);
 * echo $result . PHP_EOL;
 * // $result will be (1.5 * .5) +(2 * 3) + (4 * 1) + (6 * 2) = 22.75
 */
function array_dot_product(array $array): float
{
    if (empty($array)) {
        throw new \InvalidArgumentException('Input array cannot be empty.');
    }

    $sum = 0.0;

    foreach ($array as $key => $value) {
        if (!is_numeric($key) || !is_numeric($value)) {
            throw new \InvalidArgumentException('All array elements must be numeric.');
        }

        $sum += (float)$key * (float)$value;
    }

    return $sum;
}
