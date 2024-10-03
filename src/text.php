<?php

/**
 * Text Processing Utility Functions
 *
 * This file contains functions for advanced text processing operations.
 * It includes utilities for text chunking, overlapping text operations,
 * and array-based text manipulations.
 *
 * Key features:
 * - Text chunking into variable lengths
 * - Overlapping text chunk generation
 * - Array-based text operations
 */

namespace FOfX\Helper;

/**
 * Separates a string into non-overlapping chunks, of lengths 1 to $max_length.
 *
 * @param string $input      The input string to be separated into chunks.
 * @param int    $max_length The maximum length of each chunk.
 *
 * @throws \InvalidArgumentException If the input is empty or $max_length is less than 1.
 *
 * @return array An array of chunks with varying lengths.
 *
 * @example
 *     $string = "The cow jumped over the moon";
 *     $chunks = Helper\text_chunks($string, 3);
 *     print_r($chunks);
 *
 *     // Output:
 *     Array
 *     (
 *         [0] => Array
 *             (
 *                 [0] => The
 *                 [1] => cow
 *                 [2] => jumped
 *                 [3] => over
 *                 [4] => the
 *                 [5] => moon
 *             )
 *         [1] => Array
 *             (
 *                 [0] => The cow
 *                 [1] => jumped over
 *                 [2] => the moon
 *             )
 *         [2] => Array
 *             (
 *                 [0] => The cow jumped
 *                 [1] => over the moon
 *             )
 *     )
 */
function text_chunks(string $input, int $max_length = 3): array
{
    $input = trim($input);

    if ($input === '') {
        throw new \InvalidArgumentException('Input string cannot be empty.');
    }

    if ($max_length < 1) {
        throw new \InvalidArgumentException('Maximum length must be at least 1.');
    }

    $words           = explode(' ', $input);
    $chunked_strings = [];

    for ($i = 1; $i <= $max_length; $i++) {
        $chunks            = array_chunk($words, $i);
        $formatted_chunks  = array_map(fn ($chunk) => implode(' ', $chunk), $chunks);
        $chunked_strings[] = $formatted_chunks;
    }

    return $chunked_strings;
}

/**
 * Separates an array into overlapping chunks of max length $size.
 * The input array can have any numeric or non-numeric keys.
 *
 * @param array $array The input array to be chunked.
 * @param int   $size  The maximum size of each chunk.
 *
 * @throws \InvalidArgumentException If $size is less than 1.
 *
 * @return array An array of overlapping chunks.
 *
 * @example
 *     $string = "The cow jumped over the moon";
 *     $array  = explode(" ", $string);
 *     $chunks = Helper\array_chunk_overlapping($array, 3);
 *     print_r($chunks);
 *
 *     // Output:
 *     Array
 *     (
 *         [0] => Array
 *             (
 *                 [0] => The
 *                 [1] => cow
 *                 [2] => jumped
 *             )
 *         [1] => Array
 *             (
 *                 [0] => cow
 *                 [1] => jumped
 *                 [2] => over
 *             )
 *         [2] => Array
 *             (
 *                 [0] => jumped
 *                 [1] => over
 *                 [2] => the
 *             )
 *         [3] => Array
 *             (
 *                 [0] => over
 *                 [1] => the
 *                 [2] => moon
 *             )
 *     )
 */
function array_chunk_overlapping(array $array, int $size): array
{
    // Validate input size to ensure it is positive
    if ($size < 1) {
        throw new \InvalidArgumentException('Size must be at least 1.');
    }

    // Convert array to a reindexed array of values to avoid issues with non-sequential keys
    $array       = array_values($array);
    $array_count = count($array);

    // Return empty if array is empty
    if ($array_count === 0) {
        return [];
    }

    // Restrict $size based on array size
    $size   = min($array_count, $size);
    $chunks = [];

    // Use array_slice to create overlapping chunks
    for ($i = 0; $i <= $array_count - $size; $i++) {
        $chunks[] = array_slice($array, $i, $size);
    }

    return $chunks;
}

/**
 * Uses array_chunk_overlapping() to chunk an array and returns glued text.
 *
 * @param array  $array The input array to be chunked.
 * @param int    $size  The maximum size of each chunk.
 * @param string $glue  The string to use between elements when joining chunks.
 *
 * @return array An array of overlapping text chunks, where each chunk is glued.
 *
 * @see      array_chunk_overlapping
 *
 * @example
 *     $string = "The cow jumped over the moon";
 *     $array  = explode(" ", $string);
 *     $chunks = Helper\text_chunk_overlapping($array, 3);
 *     print_r($chunks);
 *
 *     // Output:
 *     Array
 *     (
 *         [0] => The cow jumped
 *         [1] => cow jumped over
 *         [2] => jumped over the
 *         [3] => over the moon
 *     )
 */
function text_chunk_overlapping(array $array, int $size, string $glue = ' '): array
{
    // Get the overlapping chunks from array_chunk_overlapping
    $chunks = array_chunk_overlapping($array, $size);

    // Use array_map to glue each chunk using implode
    $glued_chunks = array_map(fn ($chunk) => implode($glue, $chunk), $chunks);

    return $glued_chunks;
}

/**
 * Creates multiple arrays of size 1 to $size.
 *
 * @param array $array The input array to be chunked.
 * @param int   $size  The maximum size of each chunk.
 *
 * @return array An array of arrays, each containing chunks of increasing size.
 *
 * @see      array_chunk_overlapping
 *
 * @example
 *     $string = "The cow jumped over the moon";
 *     $array = explode(" ", $string);
 *     $chunks = Helper\array_chunk_multi($array, 3);
 *     print_r($chunks);
 *
 *     // Output:
 *     Array
 *     (
 *         [0] => Array
 *             (
 *                 [0] => Array ( [0] => The )
 *                 [1] => Array ( [0] => cow )
 *                 [2] => Array ( [0] => jumped )
 *                 [3] => Array ( [0] => over )
 *                 [4] => Array ( [0] => the )
 *                 [5] => Array ( [0] => moon )
 *             )
 *         [1] => Array
 *             (
 *                 [0] => Array ( [0] => The, [1] => cow )
 *                 [1] => Array ( [0] => cow, [1] => jumped )
 *                 [2] => Array ( [0] => jumped, [1] => over )
 *                 [3] => Array ( [0] => over, [1] => the )
 *                 [4] => Array ( [0] => the, [1] => moon )
 *             )
 *         [2] => Array
 *             (
 *                 [0] => Array ( [0] => The, [1] => cow, [2] => jumped )
 *                 [1] => Array ( [0] => cow, [1] => jumped, [2] => over )
 *                 [2] => Array ( [0] => jumped, [1] => over, [2] => the )
 *                 [3] => Array ( [0] => over, [1] => the, [2] => moon )
 *             )
 *     )
 */
function array_chunk_multi(array $array, int $size): array
{
    // Validate input size to ensure it is at least 1
    if ($size < 1) {
        throw new \InvalidArgumentException('Size must be at least 1.');
    }

    // If the input array is empty, return an empty array
    if (empty($array)) {
        return [];
    }

    $chunks_by_size = [];
    $array_length   = count($array);

    // Generate chunks of increasing size, up to the length of the array
    for ($i = 1; $i <= min($size, $array_length); $i++) {
        $chunks_by_size[] = array_chunk_overlapping($array, $i);
    }

    return $chunks_by_size;
}

/**
 * Uses array_chunk_multi(), but returns glued text.
 *
 * @param array  $array The input array to be chunked.
 * @param int    $size  The maximum size of each chunk.
 * @param string $glue  The string to use between elements when joining chunks.
 *
 * @return array An array of arrays, each containing glued text chunks.
 *
 * @see      array_chunk_multi
 *
 * @example
 *     $string = "The cow jumped over the moon";
 *     $array = explode(" ", $string);
 *     $chunks = Helper\text_chunk_multi($array, 3);
 *     print_r($chunks);
 *
 *     // Output:
 *     Array
 *     (
 *         [0] => Array
 *             (
 *                 [0] => The
 *                 [1] => cow
 *                 [2] => jumped
 *                 [3] => over
 *                 [4] => the
 *                 [5] => moon
 *             )
 *         [1] => Array
 *             (
 *                 [0] => The cow
 *                 [1] => cow jumped
 *                 [2] => jumped over
 *                 [3] => over the
 *                 [4] => the moon
 *             )
 *         [2] => Array
 *             (
 *                 [0] => The cow jumped
 *                 [1] => cow jumped over
 *                 [2] => jumped over the
 *                 [3] => over the moon
 *             )
 *     )
 */
function text_chunk_multi(array $array, int $size, string $glue = ' '): array
{
    // Get chunks of increasing sizes
    $chunks = array_chunk_multi($array, $size);

    // Glue the chunks together using array_map
    $glued_chunks = array_map(function ($chunk_group) use ($glue) {
        return array_map(function ($chunk) use ($glue) {
            return implode($glue, $chunk);
        }, $chunk_group);
    }, $chunks);

    return $glued_chunks;
}
