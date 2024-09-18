<?php

/**
 * Array Helper Functions and Utilities
 *
 * This file contains a collection of helper functions and utilities for working with arrays in PHP.
 * It includes functions for array manipulation, searching, sorting, and various other array-related operations.
 *
 * Key features:
 * - Array merging and diffing (including recursive operations)
 * - Custom sorting and balancing functions
 * - CSV parsing and conversion utilities
 * - Statistical functions (average, median, standard deviation)
 * - Binary search implementations
 * - Array generation and randomization functions
 * - Super unique filtering for multi-dimensional arrays
 * - Recursive array operations like implode, unique filtering, and transpose
 * - Array randomization and secure shuffling
 *
 * @package  FOfX\Helper
 */

namespace FOfX\Helper;

/**
 * Recursive array unique for multiarrays
 * 
 * @link    https://www.php.net/manual/en/function.array-unique.php
 * 
 * @param   array  $array
 * @return  array
 */
function super_unique(array $array): array
{
    // Return early if the array is empty
    if (empty($array)) return $array;

    // Serialize and then unserialize the array to remove duplicates
    $result = array_map("unserialize", array_unique(array_map("serialize", $array)));
    // Recursively process nested arrays
    foreach ($result as $key => $value) {
        if (is_array($value)) {
            $result[$key] = super_unique($value);
        }
    }

    return $result;
}

/**
 * I added the section for serializing objects
 * 
 * Recursively implodes an array with optional key inclusion
 * Example of $include_keys output: key, value, key, value, key, value
 * 
 * @link    https://gist.github.com/jimmygle/2564610#file-php-recursive-implosion-php
 * 
 * @param   array   $array         multi-dimensional array to recursively implode
 * @param   string  $glue          value that glues elements together
 * @param   bool    $include_keys  include keys before their values
 * @param   bool    $trim_all      trim ALL whitespace from string
 * @return  string                 imploded array
 */
function recursive_implode(array $array, string $glue = ',', bool $include_keys = false, bool $trim_all = false): string
{
    $glued_string = '';
    // Recursively iterates array and adds key/value to glued string
    array_walk_recursive($array, function ($value, $key) use ($glue, $include_keys, &$glued_string) {
        if ($include_keys) {
            $glued_string .= $key . $glue;
        }
        // If the value is an object, then serialize() it
        // to prevent "Fatal error: Uncaught Error: Object of class ... could not be converted to string"
        // Added by me
        if (is_object($value)) {
            $value = serialize($value);
        }
        $glued_string .= $value . $glue;
    });

    // Removes last $glue from string
    if (strlen($glue) > 0) {
        $glued_string = substr($glued_string, 0, -strlen($glue));
    }
    // Trim ALL whitespace
    if ($trim_all) {
        $glued_string = preg_replace("/(\s)/ixsm", '', $glued_string);
    }

    return (string) $glued_string;
}

/**
 * Puts print_r() within <pre> tags.
 * 
 * @param  array  $array           The array to print.
 * @param  bool   $returnAsString  If false, this prints the output. Else it returns the output as a string.
 */
function pre_r(array $array, bool $returnAsString = false)
{
    if ($returnAsString) {
        return "<pre>\n" . print_r($array, true) . "</pre>\n";
    } else {
        echo "<pre>\n";
        print_r($array);
        echo "</pre>\n";
    }
}

/**
 * Check if a value is a valid array integer index greater than 0
 * 
 * @link    https://stackoverflow.com/questions/4844916/best-way-to-check-for-positive-integer-php
 * 
 * @param   mixed  $value
 * @return  bool
 */
function is_int_index(mixed $value): bool
{
    if (is_int($value) && $value >= 0) {
        return true;
    } else {
        return false;
    }
}

/**
 * Validate that all elements in an array are numeric.
 * Since an empty array doesn't contain non-numeric items, $check_empty should probably be false.
 * 
 * @param   array  $array
 * @param   bool   $check_empty
 * @return  bool
 */
function array_is_numeric(array $array, bool $check_empty = false): bool
{
    if ($check_empty && empty($array)) {
        return false;
    }

    foreach ($array as $element) {
        if (!is_numeric($element)) {
            return false;
        }
    }

    return true;
}

/**
 * Validate that all elements are both numeric, and non-negative (positive, or 0).
 * Since an empty array doesn't contain non-numeric items, $check_empty should probably be false.
 * 
 * @param   array  $array
 * @param   bool   $check_empty
 * @return  bool
 */
function array_is_positive_numeric(array $array, bool $check_empty = false): bool
{
    if ($check_empty && empty($array)) {
        return false;
    }

    foreach ($array as $element) {
        if (!is_numeric($element) || $element < 0) {
            return false;
        }
    }

    return true;
}

/**
 * Validate that all elements are integer indexes equal to 0 or higher.
 * Since at least one index is expected, $check_empty should probably be true.
 * 
 * @param   array  $array
 * @param   bool   $check_empty
 * @return  bool
 * @see     is_int_index
 */
function array_is_int_indexes(array $array, bool $check_empty = true): bool
{
    if ($check_empty && empty($array)) {
        return false;
    }

    foreach ($array as $element) {
        if (!is_int_index($element)) {
            return false;
        }
    }

    return true;
}

/**
 * From Thought's comment
 * Checks if the array has any string keys.
 * 
 * @link    https://stackoverflow.com/questions/173400/how-to-check-if-php-array-is-associative-or-sequential/4254008
 * 
 * @param   array  $array
 * @return  bool
 */
function has_string_keys(array $array): bool
{
    //return count(array_filter(array_keys($array), 'is_string')) > 0;
    foreach ($array as $key => $value) {
        // Return true as soon as you find a string key
        if (is_string($key)) return true;
    }

    return false;
}

/**
 * Checks if an array has any arrays as its values.
 * 
 * @param   array  $array
 * @return  bool
 */
function has_array_values(array $array): bool
{
    foreach ($array as $value) {
        // Returns true as soon as an array value is found.
        if (is_array($value)) return true;
    }

    return false;
}

/**
 * Checks if an array has ONLY arrays as its values.
 * If there are no values, then there are no non-array values. So it passes this check.
 * 
 * @param   array  $array
 * @return  bool
 */
function has_only_array_values(array $array): bool
{
    foreach ($array as $value) {
        // Returns false as soon as a non-array value is found.
        if (!is_array($value)) return false;
    }

    return true;
}

/**
 * Checks if an array has only arrays as its values, and they are all of the same size (equal counts)
 * 
 * @param   array  $array
 * @return  bool
 */
function has_only_similar_array_values(array $array): bool
{
    $count = null;
    foreach ($array as $value) {
        if (!is_array($value)) return false;
        // If the first passthru, initialize $count
        if ($count === null) {
            $count = count($value);
        } else {
            // Else validate against the first count
            if ($count != count($value)) {
                return false;
            }
        }
    }

    return true;
}

/**
 * Checks if the set of input arrays all have the same set of keys
 * When using, must pass in array with splat operator prefix
 * 
 * Example:
 *      $arrays = [[1, 2, 3], [0 => 'A', 1 => 'B', 2 => 'C'], ['A', 'B', 'C']];
 *      print_r($arrays);
 *      var_dump(array_same_keys(...$arrays));
 *      $arrays = [[1, 2, 3], [0 => 'A', 1 => 'B', 2 => 'C'], ['A', 'B', 'C'], [1 => 'A', 2 => 'B', 3 => 'C']];
 *      print_r($arrays);
 *      var_dump(array_same_keys(...$arrays));
 * 
 * Without the splat operator, the argument will be interpreted as only one array. So the result will be invalid.
 * 
 * @param   array  ...$arrays
 * @return  bool
 */
function array_same_keys(array ...$arrays): bool
{
    // Return true if no arrays are provided
    if (empty($arrays)) {
        return true;
    }

    $base = array_shift($arrays);
    $base_keys = array_keys($base);
    $count_base_keys = count($base_keys);

    foreach ($arrays as $array) {
        $compare_keys = array_keys($array);
        if ($count_base_keys != count($compare_keys)) {
            return false;
        }
        for ($i = 0; $i < $count_base_keys; $i++) {
            if ($base_keys[$i] != $compare_keys[$i]) {
                return false;
            }
        }
    }

    return true;
}

/**
 * Checks if a set of variables are all arrays with the same count
 * When using, must pass in array with splat operator prefix
 * 
 * Example:
 *      $arrays = [[1, 2, 3], [0 => 'A', 1 => 'B', 2 => 'C'], ['A', 'B', 'C']];
 *      print_r($arrays);
 *      var_dump(array_same_counts(...$arrays));
 *      $arrays = [[1, 2, 3], [0 => 'A', 1 => 'B', 2 => 'C'], ['A', 'B', 'C', 'D']];
 *      print_r($arrays);
 *      var_dump(array_same_counts(...$arrays));
 * 
 * @param   mixed  ...$arrays
 * @return  bool
 */
function array_same_counts(mixed ...$arrays): bool
{
    $base = array_shift($arrays);
    if (!is_array($base)) {
        return false;
    } else {
        $base_count = count($base);
        foreach ($arrays as $array) {
            if (!is_array($array) || count($array) != $base_count) {
                return false;
            }
        }
        return true;
    }
}

/**
 * Returns the highest numeric key of an array
 * 
 * @param   array     $array
 * @return  int|bool
 */
function max_int_key(array $array): int|bool
{
    // If the array keys are empty, return false
    $keys = array_keys($array);
    if (empty($keys)) {
        return false;
    }

    // If there are no int keys, return false
    $int_keys = array_filter($keys, 'is_int');
    if (empty($int_keys)) {
        return false;
    }

    // Return the highest int key
    return max($int_keys);
}

/**
 * Return the next integer key of an array
 * Returns 0 if the array is empty
 * Else returns the highest int key, plus 1
 * 
 * @param   array  $array
 * @return  int
 */
function next_int_key(array $array): int
{
    $keys = array_keys($array);
    if (empty($keys)) {
        return 0;
    }

    $int_keys = array_filter($keys, 'is_int');
    if (empty($int_keys)) {
        return 0;
    }

    // Return the highest int key, plus 1
    return max($int_keys) + 1;
}

/**
 * A shortcut to array_slice().
 * With default $length = 50.
 * 
 * Items are from the first depth. Use slice_assoc() instead for associative arrays with nested values.
 * array_slice() by default preserves string keys, and reorders integer keys.
 * 
 * This will check if any string keys are present, and if so, all keys are preserved.
 * Otherwise, if all keys are integers, it will let array_slice() reorder the integer keys.
 * 
 * @param   array  $array
 * @param   int    $offset  If non-negative, the sequence will start at that offset in the array.
 *                          If negative, the sequence will start that far from the end of the array.
 * @param   int    $length  If length is given and is positive
 *                          then the sequence will have up to that many elements in it.
 *                          If the array is shorter than the length
 *                          then only the available array elements will be present.
 *                          If length is given and is negative
 *                          then the sequence will stop that many elements from the end of the array.
 *                          If it is omitted
 *                          then the sequence will have everything from offset up to the end of the array.
 * @return  array
 * @see     has_string_keys
 */
function slice(array $array, int $offset = 0, int $length = 50): array
{
    return array_slice($array, $offset, $length, has_string_keys($array));
}

/**
 * A shortcut to print_r(slice($array, $offset, $length), $asString).
 * 
 * @param  array  $array     The array to slice and print.
 * @param  int    $offset    The offset to start slicing from.
 * @param  int    $length    The number of elements to slice.
 * @param  bool   $asString  Whether print_r() should return the output as a string.
 * @see    slice
 */
function rSlice(array $array, int $offset = 0, int $length = 50, bool $asString = false)
{
    $slicedArray = slice($array, $offset, $length);
    if ($asString) {
        return print_r($slicedArray, $asString);
    } else {
        print_r($slicedArray);
    }
}


/**
 * Select only a truncated number of elements from a larger associative array
 * Starts at $offset
 * 
 * This is different than: array_slice($array, 0, $len).
 * array_slice() will truncate according to the keys. It will return $len keys.
 * This function will keep all the associative keys, and truncate the array of values.
 * The elements of the associative array must themselves be arrays.
 * 
 * Example:
 *      $items = array('first' => array(10, 'twenty', 30, 'forty', 50, 60, 70), 'second' => array(10, 20, 30, 40, 50), 'third' => array(1, 2, 3, 4, 5), 'fourth' => array(5, 4, 3, 2, 1), 5 => array('c', 'b', 'a'), 'NotArray'=>'Hello', 'Another' => array('a', 'b'), 'More' => array('a', 'b', 'c', 'd'), 'Last' => array('apples'));
 *      print_r(array_slice($items, 3, 2));
 *      print_r(assoc_array_truncate($items, 3, 2));
 * 
 * The array_slice() outputs: Array ( [fourth] => Array ( [0] => 5 [1] => 4 [2] => 3 [3] => 2 [4] => 1 ) [0] => Array ( [0] => c [1] => b [2] => a ) )
 * It skips the first three keys due to offset 3, and returns the next 2 due to length 2.
 * It does not preserve the array keys as it re-indexes '5' to '0'.
 * 
 * The assoc_array_truncate() outputs: Array ( [first] => Array ( [0] => forty [1] => 50 ) [second] => Array ( [0] => 40 [1] => 50 ) [third] => Array ( [0] => 4 [1] => 5 ) [fourth] => Array ( [0] => 2 [1] => 1 ) [More] => Array ( [0] => d ) )
 * It shows all sub-arrays that have at least 1 element with offset 3. In other words all keys with 4 or more values.
 * It keeps up to 2 values per sub-array.
 * 
 * If I do "print_r(assoc_array_truncate($items, 0, 2));"
 * it will output all the array keys, and show up to the first 2 values for each:
 *      Array ( [first] => Array ( [0] => 10 [1] => twenty ) [second] => Array ( [0] => 10 [1] => 20 ) [third] => Array ( [0] => 1 [1] => 2 ) [fourth] => Array ( [0] => 5 [1] => 4 ) [5] => Array ( [0] => c [1] => b ) [NotArray] => Hello [Another] => Array ( [0] => a [1] => b ) [More] => Array ( [0] => a [1] => b ) [Last] => Array ( [0] => apples ) )
 * With array_slice(), "print_r(array_slice($items, 0, 2));"
 * It will show the first two associative keys, and all the elements for each key.
 * 
 * @param   array                      $array
 * @param   int                        $offset
 * @param   int                        $len
 * @param   bool                       $preserve_keys  If true, preserves the original keys of the sub array.
 *                                                     If false, reorders the keys.
 * @return  array                      $temp_array
 * @throws  \InvalidArgumentException                  If $offset or $len are invalid.
 */
function assoc_array_truncate(array $array, int $offset = 0, int $len = 1, bool $preserve_keys = true): array
{
    // Ensure that len is at least 1 and offset is at least 0
    if (!($len >= 1 && $offset >= 0)) {
        throw new \InvalidArgumentException(
            "ERROR - assoc_array_truncate() - offset ($offset) must be 0 or more and len ($len) must be 1 or more."
        );
    }

    $temp_array = array();
    foreach ($array as $field => $value) {
        // If this is an array, iterate over it starting from $offset up to either $len, or the end of the array
        // And append qualifying values to a new array
        if (is_array($value)) {
            $subKeys = array_keys($value);
            $max = min($offset + $len, count($value));
            for ($i = $offset; $i < $max; $i++) {
                // If $preserve_keys, preserve the original key value. Else reorder the keys.
                if ($preserve_keys) {
                    $temp_array[$field][$subKeys[$i]] = $value[$subKeys[$i]];
                } else {
                    $temp_array[$field][] = $value[$subKeys[$i]];
                }
            }
        } else {
            // If this is a single element, only append if $offset is 0
            if ($offset == 0) {
                $temp_array[$field] = $value;
            }
        }
    }

    return $temp_array;
}

/**
 * Use assoc_array_truncate() to get a slice of an associative array
 * 
 * @param   array  $array
 * @param   int    $offset
 * @param   int    $len
 * @param   bool   $preserve_keys  If true, preserves the original keys of the sub array.
 *                                 If false, reorders the keys.
 * @return  array
 * @see     assoc_array_truncate
 */
function slice_assoc(array $array, int $offset = 0, int $len = 50, bool $preserve_keys = true): array
{
    return assoc_array_truncate($array, $offset, $len, $preserve_keys);
}

/**
 * Transpose a 2-dimensional indexed array, with associative sub-arrays
 * The multidimensional array must be balanced or it will give warnings.
 * In other words, all sub-arrays must have the same count.
 * 
 * @param   array                      $array
 * @return  array                      $newArray
 * @throws  \InvalidArgumentException             If the array is not balanced or contains non-array elements.
 * @see     has_only_similar_array_values
 */
function transpose_indexed_array(array $array): array
{
    // Validate to make sure all elements are arrays
    if (!has_only_similar_array_values($array)) {
        throw new \InvalidArgumentException(
            'transpose_indexed_array() - All values of array must be arrays, and they must all have the same count.'
        );
    }

    $newArray = array();
    $indexes = array_keys($array);
    $count = count($indexes);
    for ($i = 0; $i < $count; $i++) {
        $index = $indexes[$i];
        $keys = array_keys($array[$index]);
        foreach ($keys as $key) {
            $newArray[$key][$index] = $array[$index][$key];
        }
    }

    return $newArray;
}

/**
 * Transpose a 2-dimensional associative array, with indexed sub-arrays
 * The multidimensional array must be balanced or it will give warnings.
 * In other words, all sub-arrays must have the same count.
 * 
 * @param   array                      $array
 * @return  array                      $newArray
 * @throws  \InvalidArgumentException             If the array is not balanced or contains non-array elements.
 * @see     has_only_similar_array_values
 */
function transpose_associative_array(array $array): array
{
    // Validate to make sure all elements are arrays
    if (!has_only_similar_array_values($array)) {
        throw new \InvalidArgumentException(
            'transpose_associative_array() - All values of array must also be arrays, and they all must be of the same count.'
        );
    }

    $newArray = array();
    $keys = array_keys($array);
    foreach ($keys as $key) {
        $indexes = array_keys($array[$key]);
        foreach ($indexes as $index) {
            $newArray[$index][$key] = $array[$key][$index];
        }
    }

    return $newArray;
}

/**
 * Convert a 2-dimensional array into an HTML table.
 * 
 * This function generates an HTML table string from a 2-dimensional array.
 * The array must be balanced, with each sub-array having the same number of elements.
 *
 * @param   array                      $array        The 2-dimensional array to convert to a table.
 * @param   string                     $table_id     Optional id attribute for the table.
 * @param   string                     $class        Optional class attribute for the table.
 * @param   string                     $style        Optional style attribute for the table.
 * @return  string                     $tableString  The generated HTML table as a string.
 *
 * @throws  \InvalidArgumentException                If the array is not balanced.
 * @see     has_only_similar_array_values
 * @see     sanitize_css_id_name
 * @see     sanitize_css_class_name
 * @see     sanitize_style_attribute
 */
function array_to_table(array $array, string $table_id = "", string $class = "", string $style = ""): string
{
    // Ensure the array is balanced
    if (!has_only_similar_array_values($array)) {
        throw new \InvalidArgumentException(
            'The provided array must be balanced (each sub-array must have the same number of elements).'
        );
    }

    $table_id_string = $table_id ? " id=\"" . sanitize_css_id_name($table_id) . "\"" : "";
    $class_string = $class ? " class=\"" . sanitize_css_class_name($class) . "\"" : "";
    $style_string = $style ? " style=\"" . sanitize_style_attribute($style) . "\"" : "";

    $tableString = "<table$table_id_string$class_string$style_string>\n";

    if (empty($array)) {
        $tableString .= "</table>";
    } else {
        $keys = array_keys($array);
        $sub_keys = array_keys($array[$keys[0]]);

        $tableString .= "\t<thead>\n\t\t<tr>\n";
        foreach ($keys as $key) {
            $tableString .= "\t\t\t<th>" . htmlspecialchars($key) . "</th>\n";
        }
        $tableString .= "\t\t</tr>\n\t</thead>\n\t<tbody>\n";

        foreach ($sub_keys as $i) {
            $tableString .= "\t\t<tr>\n";
            foreach ($keys as $j) {
                $tableString .= "\t\t\t<td>" . htmlspecialchars($array[$j][$i]) . "</td>\n";
            }
            $tableString .= "\t\t</tr>\n";
        }

        $tableString .= "\t</tbody>\n</table>";
    }

    return $tableString;
}

/**
 * Take a 2-dimensional array and turn it into a readable padded TSV.
 * Expects an indexed array, with associative sub-arrays.
 * 
 * @param    array                      $array                   The 2D array to convert to TSV
 * @param    bool                       $include_headers         Whether the output should include headers
 * @param    bool                       $right_pad_first_column  Whether the first output column should be padded right instead of left
 * @return   string                                              The TSV-formatted string
 * @throws   \InvalidArgumentException                           If the array items do not have the same keys.
 * @see      array_same_keys
 * @see      transpose_indexed_array
 * @see      mb_str_pad
 * 
 * @example  
 * $array = array(
 *     0 => array('Keyword' => 'apple', 'KD' => '20', 'Volume' => 100),
 *     1 => array('Keyword' => 'do it yourself', 'KD' => '0', 'Volume' => 1000)
 * );
 * $content = Helper\array_nested_to_tsv($array, true, true);
 * echo $content;
 * 
 * Output:
 * Keyword         KD      Volume
 * apple           20         100
 * do it yourself   0        1000
 * 
 * @example  
 * $content = Helper\array_nested_to_tsv($array, true, false);
 * echo $content;
 * 
 * Output:
 *        Keyword  KD      Volume
 *          apple  20         100
 * do it yourself   0        1000
 * 
 * @example  
 * $content = Helper\array_nested_to_tsv($array, false, true);
 * echo $content;
 * 
 * Output:
 * apple           20       100
 * do it yourself   0      1000
 */
function array_nested_to_tsv(array $array, bool $include_headers = true, bool $right_pad_first_column = true): string
{
    if (!array_same_keys(...$array)) {
        throw new \InvalidArgumentException('array_nested_to_tsv() - All array items must have the same keys.');
    }

    $lengths = array();
    $transposed = transpose_indexed_array($array);
    foreach ($transposed as $key => $subArray) {
        if ($include_headers) {
            // If the output file is to include headers, we also have to include the array keys in our calculation
            $subArray[] = $key;
        }
        // Non-English characters can have longer strlen()s, so use mb_strlen()
        $lengths[$key] = max(array_map('mb_strlen', $subArray));
    }
    $content = '';

    if ($include_headers) {
        $line = array();
        foreach (array_keys($transposed) as $key) {
            // If the first line is empty, then this should be the first line
            if ($right_pad_first_column && empty($line)) {
                $pad_type = STR_PAD_RIGHT;
            } else {
                $pad_type = STR_PAD_LEFT;
            }
            $line[] = mb_str_pad($key, $lengths[$key], " ", $pad_type);
        }
        if (!empty($line)) $content .= implode("\t", $line) . PHP_EOL;
    }

    foreach ($array as $subArray) {
        $line = array();
        foreach ($subArray as $key => $value) {
            // If the first line is empty, then this should be the first line
            if ($right_pad_first_column && empty($line)) {
                $pad_type = STR_PAD_RIGHT;
            } else {
                $pad_type = STR_PAD_LEFT;
            }
            $line[] = mb_str_pad($value, $lengths[$key], " ", $pad_type);
        }
        if (!empty($line)) $content .= implode("\t", $line) . PHP_EOL;
    }

    return $content;
}

/**
 * This function returns the average of elements in an array.
 * 
 * @param   array                      $array              The array of elements to average.
 * @param   bool                       $strict_validation  If true, the array will not be filtered for blank strings before validating that it is numeric.
 * @return  ?float                                         The average of the array elements, or null if the array is empty.
 * @throws  \InvalidArgumentException                      If the array contains non-numeric elements.
 * @see     array_is_numeric
 */
function array_average(array $array, bool $strict_validation = false): ?float
{
    if (!$strict_validation) {
        // Can't use normal array_filter(), as that will remove value 0 elements
        // So use strlen() to filter out blank strings
        // Also filter out null values
        $array = array_filter($array, fn($val) => !is_null($val) && strlen($val) > 0);
    }

    if (!array_is_numeric($array)) {
        $invalid_elements = implode(', ', array_filter($array, fn($val) => !is_numeric($val)));
        throw new \InvalidArgumentException("array_average() - array contains non-numeric elements: $invalid_elements.");
    }

    if (count($array)) {
        return array_sum($array) / count($array);
    } else {
        return null;
    }
}

/**
 * This function finds the median of an array.
 * If two elements are in the middle, the median is the average of the two elements.
 * The function can perform strict validation or filter out non-numeric elements based on the $strict_validation parameter.
 * 
 * @param   array                      $array              The array to find the median of.
 * @param   bool                       $strict_validation  Whether to perform strict validation on the array.
 * @return  ?float                                         The median of the array elements, or null if the array is empty after filtering non-numeric elements.
 * @throws  \InvalidArgumentException                      If the array is empty or contains non-numeric elements when $strict_validation is true.
 * @see     array_is_numeric
 */
function array_median(array $array, bool $strict_validation = false): ?float
{
    if ($strict_validation) {
        if (empty($array)) {
            throw new \InvalidArgumentException('array_median() - The array is empty.');
        }
        if (!array_is_numeric($array)) {
            throw new \InvalidArgumentException('array_median() - The array contains non-numeric elements.');
        }
    } else {
        // Filter out non-numeric elements
        $array = array_filter($array, 'is_numeric');
        if (empty($array)) {
            return null;
        }
    }

    sort($array);
    $count = count($array);
    $middle = $count / 2;

    return ($array[ceil($middle) - 1] + $array[floor($middle)]) / 2;
}

/**
 * This function calculates the weighted average of an array, given another array of weights.
 * 
 * If $strict_validation is false, non-numeric elements are removed before calculating the weighted average.
 * If $strict_validation is true, the function will throw an exception if the array is empty or contains non-numeric elements.
 *
 * @param   array                      $values             The array of values to calculate the weighted average for.
 * @param   array                      $weights            The array of weights corresponding to each value.
 * @param   bool                       $strict_validation  Whether to perform strict validation on the arrays.
 * @return  ?float                                         The weighted average, or null if the array is empty after filtering non-numeric elements.
 * @throws  \InvalidArgumentException                      If $strict_validation is true and the array is empty or contains non-numeric elements.
 * @see     array_is_numeric
 */
function array_weighted_average(array $values, array $weights, bool $strict_validation = false): ?float
{
    if ($strict_validation) {
        if (empty($values)) {
            throw new \InvalidArgumentException('array_weighted_average() - The array is empty.');
        }
        if (!array_is_numeric($values)) {
            throw new \InvalidArgumentException(
                'array_weighted_average() - The values array contains non-numeric elements.'
            );
        }
        if (!array_is_numeric($weights)) {
            throw new \InvalidArgumentException(
                'array_weighted_average() - The weights array contains non-numeric elements.'
            );
        }
    } else {
        // Filter out non-numeric elements
        // Can't use normal array_filter(), as that will remove value 0 elements
        // Must use array_values() to re-index after filtering, so that the keys for both arrays match up
        $values = array_values(array_filter($values, 'is_numeric'));
        $weights = array_values(array_filter($weights, 'is_numeric'));

        if (empty($values)) {
            return null;
        }
    }

    if (count($values) !== count($weights)) {
        $message = 'array_weighted_average() - The values and weights arrays must have the same number of elements. count(values)=' . count($values) . ', count(weights)=' . count($weights) . '.';
        throw new \InvalidArgumentException($message);
    }

    // Must ensure that all weights are non-negative to avoid dividing by zero
    // Use PHP_FLOAT_EPSILON since due to floating point math inaccuracy,
    //total weights that sum to 0 might calculate as trivially positive
    $total_weight = array_sum($weights);
    if ($total_weight <= PHP_FLOAT_EPSILON) {
        throw new \InvalidArgumentException('array_weighted_average() - The sum of weights must be greater than zero (PHP_FLOAT_EPSILON=' . PHP_FLOAT_EPSILON . ').');
    }

    $weighted_sum = 0;
    foreach ($values as $i => $value) {
        $weighted_sum += $value * $weights[$i];
    }

    return $weighted_sum / $total_weight;
}


/**
 * This function returns the statistical Standard Deviation of elements in an array.
 *
 * If $strict_validation is false, non-numeric elements are removed before calculating the standard deviation.
 * If $strict_validation is true, the function will throw an exception if the array is empty or contains non-numeric elements.
 *
 * @param   array                      $array              The array of elements to calculate the standard deviation for.
 * @param   bool                       $strict_validation  Whether to perform strict validation on the array.
 * @return  ?float                                         The standard deviation of the array elements, or null if the array is empty after filtering non-numeric elements.
 * @throws  \InvalidArgumentException                      If $strict_validation is true and the array is empty or contains non-numeric elements.
 * @see     array_average
 */
function array_stdev(array $array, bool $strict_validation = false): ?float
{
    if ($strict_validation) {
        if (empty($array)) {
            throw new \InvalidArgumentException('array_stdev() - The array is empty.');
        }
        if (!array_is_numeric($array)) {
            throw new \InvalidArgumentException('array_stdev() - The array contains non-numeric elements.');
        }
    } else {
        // Filter out non-numeric elements
        $array = array_filter($array, 'is_numeric');
        if (empty($array)) {
            return null;
        }
    }

    $count = count($array);
    // If there is only one element, the standard deviation is 0
    if ($count == 1) {
        return 0;
    } else {
        $variance = 0;
        $average = array_average($array);

        foreach ($array as $value) {
            // The variance is the sum of squared differences from the mean
            $variance += pow($value - $average, 2);
        }

        // The Standard Deviation is sqrt(variance/(n-1))
        return sqrt($variance / ($count - 1));
    }
}


/**
 * Performs a zip operation on a set of arrays.
 * Aggregates iterables into tuples, similar to Python's zip() function.
 * However the results are NOT the same as the Python zip() function.
 * 
 * @link    https://www.programiz.com/python-programming/methods/built-in/zip
 * @link    https://stackoverflow.com/questions/2815162/is-there-a-php-function-like-pythons-zip
 * 
 * @param   array  ...$arrays  Variable number of arrays to be zipped.
 * @return  array              An array of tuples.
 */
function array_map_zip(array ...$arrays): array
{
    // Return an empty array if any of the provided arrays is empty
    if (empty($arrays) || in_array([], $arrays)) {
        return [];
    }

    // Perform the zip operation using array_map with null callback.
    return array_map(null, ...$arrays);
}


/**
 * Attempts to replicate Python's zip() function
 * 
 * Example:
 *      $number_list = [1, 2, 3];
 *      $str_list = ['one', 'two', 'three'];
 *      print_r(array_zip($number_list, $str_list));
 *      print_r(python_zip($number_list, $str_list));
 *      $number_list = [1, 2, 3, 5];
 *      $str_list = ['one', 'two', 'five'];
 *      $numbers_tuple = ['ONE', 'TWO', 'THREE', 'FOUR', 'FIVE'];
 *      print_r(array_zip($number_list, $str_list, $numbers_tuple));
 *      print_r(python_zip($number_list, $str_list, $numbers_tuple));
 * 
 * Result:
 * Array ( [0] => Array ( [0] => 1 [1] => one ) [1] => Array ( [0] => 2 [1] => two ) [2] => Array ( [0] => 3 [1] => three ) )
 * Array ( [0] => Array ( [0] => 1 [1] => one ) [1] => Array ( [0] => 2 [1] => two ) [2] => Array ( [0] => 3 [1] => three ) )
 * Array ( [0] => Array ( [0] => 1 [1] => one [2] => ONE ) [1] => Array ( [0] => 2 [1] => two [2] => TWO ) [2] => Array ( [0] => 3 [1] => five [2] => THREE ) [3] => Array ( [0] => 5 [1] => FOUR ) [4] => Array ( [0] => FIVE ) )
 * Array ( [0] => Array ( [0] => 1 [1] => one [2] => ONE ) [1] => Array ( [0] => 2 [1] => two [2] => TWO ) [2] => Array ( [0] => 3 [1] => five [2] => THREE ) )
 * 
 * array_zip() creates partial tuples, python_zip() only creates full tuples
 * 
 * @link    https://stackoverflow.com/questions/2815162/is-there-a-php-function-like-pythons-zip
 * 
 * @param   array  ...$arrays
 * @return  array
 */
function python_zip(array ...$arrays): array
{
    if (count($arrays) === 1) {
        $result = array();
        foreach ($arrays[0] as $item) {
            $result[] = array($item);
        };
        return $result;
    };
    $result = call_user_func_array('array_map', array_merge(array(null), $arrays));
    $length = min(array_map('count', $arrays));
    return array_slice($result, 0, $length);
}

/**
 * My attempt at a zip() function to produce tuples from a set of arrays
 * 
 * @param   array                      ...$arrays
 * @return  array
 * @throws  \InvalidArgumentException              If all arguments are not arrays
 * @see     has_only_array_values
 */
function array_zip(array ...$arrays): array
{
    if (!has_only_array_values($arrays)) {
        throw new \InvalidArgumentException('array_zip() - All arguments must be arrays.');
    }

    $tuples = array();
    foreach ($arrays as $subArray) {
        $i = 0;
        foreach ($subArray as $value) {
            $tuples[$i][] = $value;
            $i++;
        }
    }

    return $tuples;
}

/**
 * Takes an array of arrays of equal sizes, and creates an array with their averages
 * If $precision is specified, the result is rounded to the given decimal places.
 * Using the passing in of an array of arrays rather than the splat operator, due to $precision argument
 * 
 * @param   array                      $arrays
 * @param   ?int                                precision
 * @return  array
 * @throws  \InvalidArgumentException           If the input arrays do not have the same keys.
 * @see     array_same_keys
 */
function average_across_arrays(array $arrays, ?int $precision = null): array
{
    if (!array_same_keys(...$arrays)) {
        throw new \InvalidArgumentException(
            'average_across_arrays() - All values of array must also be arrays, and they all must have the same keys.'
        );
    }

    $positions = array_zip(...$arrays);
    $averages = array();
    for ($i = 0; $i < count($positions); $i++) {
        $averages[$i] = array_average($positions[$i]);
        if (is_int($precision)) {
            $averages[$i] = round($averages[$i], $precision);
        }
    }

    return $averages;
}

/**
 * Recursively adds slashes to strings in mixed input data.
 * 
 * @link    https://stackoverflow.com/questions/19210833/php-addslashes-using-array
 * 
 * @param   mixed  $data  The input data that may be a string, array, or other types.
 * @return  mixed         The input data with slashes added to strings.
 */
function addslashes_recursive(mixed $data): mixed
{
    if (is_array($data)) {
        return array_map(__NAMESPACE__ . '\addslashes_recursive', $data);
    } elseif (is_string($data)) {
        return addslashes($data);
    } else {
        return $data;
    }
}

/**
 * Recursively applies array_unique() to an array
 * 
 * @param   array  $array
 * @param   bool   $preserve_keys  Whether to preserve int keys or reorder them
 * @return  array  $newArray
 * @see     has_array_values
 * @see     max_int_key
 */
function array_unique_recursive(array $array, bool $preserve_keys = false): array
{
    // Check if the array's values are themselves arrays
    // If not, apply array_unique() to the array
    if (has_array_values($array)) {
        // array_unique() expects an array with non-array elements.
        // Find the array elements in the array, and apply array_unique_recursive() to them
        $newArray = array();
        foreach ($array as $key => $value) {
            // If $preserve_keys is not true, then reorder the int keys
            if (!$preserve_keys) {
                if (is_numeric($key)) {
                    $max = max_int_key($newArray);
                    // If no integer keys, set to 0. Else set to max key plus 1.
                    if ($max === false) {
                        $key = 0;
                    } else {
                        $key = $max + 1;
                    }
                }
            }
            if (is_array($value)) {
                // If the value is an array, decide whether to apply recursively, or apply array_unique()
                if (has_array_values($value)) {
                    $newArray[$key] = array_unique_recursive($value, $preserve_keys);
                } else {
                    // If not preserving keys, re-index using array_values()
                    $uniqueArray = array_unique($value);
                    if (!$preserve_keys) $uniqueArray = array_values($uniqueArray);
                    $newArray[$key] = $uniqueArray;
                }
            } else {
                // Else add the value if it is not already present
                if (!in_array($value, $newArray)) {
                    $newArray[$key] = $value;
                }
            }
        }
        return $newArray;
    } else {
        return array_unique($array);
    }
}

/**
 * Generate an array of random numbers within a specified range.
 * 
 * @param   int                        $count  The number of elements to generate.
 * @param   int                        $min    The minimum value for random numbers.
 * @param   int                        $max    The maximum value for random numbers.
 * @return  array                              An array of random numbers.
 * @throws  \InvalidArgumentException          If $count is negative or $min is greater than $max.
 */
function rand_array(int $count, int $min, int $max): array
{
    if ($count < 0) {
        throw new \InvalidArgumentException('The count must be a positive integer.');
    }

    if ($min > $max) {
        throw new \InvalidArgumentException('The minimum value cannot be greater than the maximum value.');
    }

    $numbers = [];
    for ($i = 0; $i < $count; $i++) {
        $numbers[] = random_int($min, $max);
    }

    return $numbers;
}

/**
 * Creates an array of random elements out of an array. An element may be selected multiple times.
 *
 * Example:
 *      $fruits = ["Apple", "Bananas", "Grapes", "Oranges", "Pears", "Pineapples", "Raisins"];
 *      print_r(array_rand_duplicates($fruits, 5));
 * This will create an array of 5 randomly picked fruit items.
 *
 * @param   array                      $array  The array to select from.
 * @param   int                        $len    The number of elements to select.
 * @return  array                              The array of randomly selected elements.
 * @throws  \InvalidArgumentException          If the array is empty or $len is negative.
 */
function array_rand_duplicates(array $array, int $len): array
{
    if (empty($array)) {
        throw new \InvalidArgumentException('The array cannot be empty.');
    }
    if ($len < 0) {
        throw new \InvalidArgumentException('Length must be a non-negative integer.');
    }

    $count = count($array);
    $keys = array_keys($array);
    $result = array();

    for ($i = 0; $i < $len; $i++) {
        $random_index = random_int(0, $count - 1);
        $random_key = $keys[$random_index];
        $result[] = $array[$random_key];
    }

    return $result;
}

/**
 * Shuffle an array and return a slice of it.
 * 
 * @param   array  $array  The array to shuffle.
 * @param   int    $len    The length of the array slice.
 * @return  array          The shuffled and sliced array.
 */
function shuffle_slice(array $array, int $len): array
{
    shuffle($array);
    $len = min($len, count($array));
    return array_slice($array, 0, $len);
}


/**
 * Pick a random key from an array using the cryptographically secure random_int() function.
 * 
 * @param   array       $array  The array from which to pick a random key.
 * @return  int|string          The randomly selected key from the array.
 */
function array_random_key(array $array): int|string
{
    if (empty($array)) {
        throw new \InvalidArgumentException('The array cannot be empty.');
    }

    $maxIndex = count($array) - 1;
    // Use array_slice() with a random offset and a length of 1, and preserve the keys.
    // Use key() to fetch the key of this random element.
    $randomOffset = random_int(0, $maxIndex);
    $key = key(array_slice($array, $randomOffset, 1, true));
    return $key;
}


/**
 * Shuffle an array using cryptographically secure random_int() via array_random_key().
 * Much slower than shuffle_slice() for larger arrays.
 * For larger arrays, should probably use shuffle_slice() instead.
 * 
 * @param   array     $array   The array to shuffle.
 * @param   int|bool  $length  Optional. The length of the subset to return. Defaults to false.
 * @return  array              The shuffled (and possibly sliced) array.
 * @see     array_random_key
 */
function shuffle_secure(array $array, int|bool $length = false): array
{
    $shuffledArray = [];
    $count = count($array);

    for ($i = 0; $i < $count; $i++) {
        $randomKey = array_random_key($array);
        $shuffledArray[] = $array[$randomKey];
        unset($array[$randomKey]);
    }
    if ($length && is_int($length)) {
        $shuffledArray = array_slice($shuffledArray, 0, $length);
    }

    return $shuffledArray;
}


/**
 * array_rand() will return an array of random keys.
 * This will use those keys to create an array of values from the original array.
 * Note that this is NOT the same as shuffle_slice(), since array_rand() returns keys in the original order.
 * Thus these results will preserve their original ordering.
 * 
 * array_rand() throws an error if $num is greater than the number of elements in the array.
 * This function will limit $num.
 * 
 * Example:
 *      $fruits = array("Apple", "Bananas", "Grapes", "Oranges", "Pears", "Pineapples", "Raisins");
 *      print_r($fruits);
 *      print_r(shuffle_secure($fruits, 3));
 *      print_r(array_rand_to_array($fruits, 3));
 * 
 * shuffle_secure() will randomly pick 3 of the fruits in any order.
 * While array_rand_to_array() will pick 3 fruits, but they will be in the same order as the original array.
 * 
 * @param   array  $array  The array to pick from.
 * @param   int    $num    The number of elements to pick.
 * @return  mixed
 */
function array_rand_to_array(array $array, int $num = 1): array
{
    if (empty($array)) {
        return [];
    }

    // Limit $num to the number of elements in the array.
    $num = min($num, count($array));
    // If $num is 1, array_rand() will return a single random key.
    // So use cast to array.
    $randomKeys = (array) array_rand($array, $num);
    $newArray = [];
    foreach ($randomKeys as $key) {
        $newArray[] = $array[$key];
    }

    return $newArray;
}

/**
 * Use array_random_key() to pick an array of random elements out of an array.
 * More secure than array_rand_to_array(), but much slower. At larger scales, the speed difference seems to increase.
 * 
 * Note that this is NOT the same as shuffle_slice(). These elements will be random and thus may give duplicates.
 * In other words, multiple items will be picked randomly independently. So the same item may be picked more than once.
 * Whereas with shuffle_slice(), the array is shuffled, then a slice is returned.
 * 
 * Similar to array_rand_duplicates(), except that it uses array_random_key() rather than doing the work internally.
 * Also similar in speed to array_rand_duplicates().
 * 
 * @param   array  $array  The array to pick random elements from.
 * @param   int    $num    The number of elements to pick. Can be larger than the number of elements in the array.
 * @return  array          An array of selected elements.
 * @see     array_random_key
 */
function array_random_elements(array $array, int $num = 1): array
{
    if (empty($array)) {
        return [];
    }

    $randomElements = [];
    for ($i = 0; $i < $num; $i++) {
        $randomElements[] = $array[array_random_key($array)];
    }

    return $randomElements;
}

/**
 * A wrapper for array_random_elements(), that gets the first element only.
 * 
 * @param   array  $array  The array to pick a random element from.
 * @return  mixed
 * @see     array_random_elements
 */
function array_random_element(array $array): mixed
{
    return array_random_elements($array, 1)[0];
}

/**
 * Sorts a multidimensional array by the values of another array.
 * Takes advantage of the fact that array_multisort($array);
 * will sort a multi-dimensional array by the value of its keys' first sub-array.
 * 
 * The example below makes an array of lengths of the array's keys, and then uses array_multisort_by_array() to sort ascending by key length.
 * Example:
 *      $word_counts = array('a cappella' => array('original_case' => 'a cappella', 'file_row_first_found' => 0, 'count_in_file' => 18,), 'abbandono' => array('original_case' => 'abbandono', 'file_row_first_found' => 0, 'count_in_file' => 13,), 'accrescendo' => array('original_case' => 'accrescendo', 'file_row_first_found' => 0, 'count_in_file' => 13,), 'affettuoso' => array('original_case' => 'affettuoso', 'file_row_first_found' => 0, 'count_in_file' => 13,), 'agilmente' => array('original_case' => 'agilmente', 'file_row_first_found' => 0, 'count_in_file' => 13,), 'agitato' => array('original_case' => 'agitato', 'file_row_first_found' => 0, 'count_in_file' => 13,), 'amabile' => array('original_case' => 'amabile', 'file_row_first_found' => 0, 'count_in_file' => 13,), 'amoroso' => array('original_case' => 'amoroso', 'file_row_first_found' => 0, 'count_in_file' => 38,), 'appassionatamente' => array('original_case' => 'appassionatamente', 'file_row_first_found' => 0, 'count_in_file' => 13,), 'appassionato' => array('original_case' => 'appassionato', 'file_row_first_found' => 0, 'count_in_file' => 13,),);
 *      $keys = array_keys($word_counts);
 *      $key_lengths = array_map('strlen', $keys);
 *      array_multisort_by_array($word_counts, $key_lengths, SORT_ASC, SORT_REGULAR);
 *      print_r($word_counts);
 * 
 * @param   array  &$array1     The multidimensional array to be sorted. Passed by reference.
 * @param   array  $array2      The array whose values will determine the sort order.
 * @param   int    $sort_order  The order in which to sort the values (SORT_ASC or SORT_DESC).
 * @param   int    $sort_flags  See: https://www.php.net/manual/en/function.array-multisort.php
 * @return  void                The input $array1 is modified by reference.
 */
function array_multisort_by_array(
    array &$array1,
    array $array2,
    int $sort_order = SORT_ASC,
    int $sort_flag = SORT_REGULAR
): void {
    // Ensure that the array counts match.
    if (count($array1) != count($array2)) {
        throw new \InvalidArgumentException(
            "array_multisort_by_array() - count(\$array1) != count(\$array2). " . count($array1) . " != " . count($array2)
        );
    }

    // Get the keys of $array1.
    $keys = array_keys($array1);
    // Create a temporary array that includes the sort values from $array2.
    $tempArray = [];
    foreach ($keys as $i => $key) {
        $tempArray[] = ['sort_value' => $array2[$i], 'original_key' => $key, 'original_value' => $array1[$key]];
    }
    // Sort the temporary array by 'sort_value'.
    array_multisort(array_column($tempArray, 'sort_value'), $sort_order, $sort_flag, $tempArray);

    // Rebuild $array1 based on the sorted order.
    $sortedArray = [];
    foreach ($tempArray as $item) {
        $sortedArray[$item['original_key']] = $item['original_value'];
    }

    // Replace the original array with the sorted one.
    $array1 = $sortedArray;
}

/**
 * Using sort() or rsort() on an array with keys will give only the values and not the keys.
 * array_multisort_nested($array); can sort by the values while keeping the keys.
 * Originally for github/import_moby.php.
 * 
 * Example:
 *      $word_counts = array('a cappella' => array('original_case' => 'a cappella', 'file_row_first_found' => 0, 'count_in_file' => 18,), 'abbandono' => array('original_case' => 'abbandono', 'file_row_first_found' => 0, 'count_in_file' => 13,), 'accrescendo' => array('original_case' => 'accrescendo', 'file_row_first_found' => 0, 'count_in_file' => 13,), 'affettuoso' => array('original_case' => 'affettuoso', 'file_row_first_found' => 0, 'count_in_file' => 13,), 'agilmente' => array('original_case' => 'agilmente', 'file_row_first_found' => 0, 'count_in_file' => 13,), 'agitato' => array('original_case' => 'agitato', 'file_row_first_found' => 0, 'count_in_file' => 13,), 'amabile' => array('original_case' => 'amabile', 'file_row_first_found' => 0, 'count_in_file' => 13,), 'amoroso' => array('original_case' => 'amoroso', 'file_row_first_found' => 0, 'count_in_file' => 38,), 'appassionatamente' => array('original_case' => 'appassionatamente', 'file_row_first_found' => 0, 'count_in_file' => 13,), 'appassionato' => array('original_case' => 'appassionato', 'file_row_first_found' => 0, 'count_in_file' => 13,),);
 *      echo print_r(json_encode($word_counts), true ) . PHP_EOL . PHP_EOL;
 *      array_multisort_nested($word_counts, 'count_in_file', SORT_DESC);
 *      echo print_r(json_encode($word_counts), true ) . PHP_EOL . PHP_EOL;
 * 
 * Before array_multisort_nested():
 *      {"a cappella":{"original_case":"a cappella","file_row_first_found":0,"count_in_file":18},"abbandono":{"original_case":"abbandono","file_row_first_found":0,"count_in_file":13},"accrescendo":{"original_case":"accrescendo","file_row_first_found":0,"count_in_file":13},"affettuoso":{"original_case":"affettuoso","file_row_first_found":0,"count_in_file":13},"agilmente":{"original_case":"agilmente","file_row_first_found":0,"count_in_file":13},"agitato":{"original_case":"agitato","file_row_first_found":0,"count_in_file":13},"amabile":{"original_case":"amabile","file_row_first_found":0,"count_in_file":13},"amoroso":{"original_case":"amoroso","file_row_first_found":0,"count_in_file":38},"appassionatamente":{"original_case":"appassionatamente","file_row_first_found":0,"count_in_file":13},"appassionato":{"original_case":"appassionato","file_row_first_found":0,"count_in_file":13}}
 * After array_multisort_nested($word_counts, 'count_in_file', SORT_DESC);:
 *      {"amoroso":{"original_case":"amoroso","file_row_first_found":0,"count_in_file":38},"a cappella":{"original_case":"a cappella","file_row_first_found":0,"count_in_file":18},"abbandono":{"original_case":"abbandono","file_row_first_found":0,"count_in_file":13},"accrescendo":{"original_case":"accrescendo","file_row_first_found":0,"count_in_file":13},"affettuoso":{"original_case":"affettuoso","file_row_first_found":0,"count_in_file":13},"agilmente":{"original_case":"agilmente","file_row_first_found":0,"count_in_file":13},"agitato":{"original_case":"agitato","file_row_first_found":0,"count_in_file":13},"amabile":{"original_case":"amabile","file_row_first_found":0,"count_in_file":13},"appassionatamente":{"original_case":"appassionatamente","file_row_first_found":0,"count_in_file":13},"appassionato":{"original_case":"appassionato","file_row_first_found":0,"count_in_file":13}}
 * 
 * @param   array        &$array         The multidimensional array to be sorted. Passed by reference.
 * @param   string|bool  $subArrayField  The sub-array field to sort by.
 * @param   int          $sort_order     The order in which to sort the values (SORT_ASC or SORT_DESC).
 * @param   int          $sort_flags     See: https://www.php.net/manual/en/function.array-multisort.php
 * @return  void                         The input $array is modified by reference.
 * @see     array_multisort_by_array
 */
function array_multisort_nested(
    array &$array,
    string|bool $subArrayField = false,
    int $sort_order = SORT_ASC,
    int $sort_flags = SORT_REGULAR
): void {
    // Initialize an empty array for sorting values.
    $sortValuesArray = [];

    // If $subArrayField is provided, validate and populate the sorting values array.
    foreach ($array as $subarray) {
        if ($subArrayField && !array_key_exists($subArrayField, $subarray)) {
            throw new \InvalidArgumentException("Key '$subArrayField' not found in sub-array.");
        }
        $sortValuesArray[] = $subArrayField ? $subarray[$subArrayField] : $subarray;
    }

    // Sort the array based on the populated sorting values array.
    array_multisort_by_array($array, $sortValuesArray, $sort_order, $sort_flags);
}

/**
 * Modified to use strnatcmp instead of strcmp, to deal with the issue with multi-digit numbers as mentioned at dev.to
 * Requires a sorted array.
 * 
 * Example:
 *      set_memory_max();
 *      $large_array = array();
 *      $max = 10000000;
 *      for ($i = 0; $i < $max; $i++) {
 *          $large_array[] = $i;
 *      }
 *      $start = microtime(true);
 *      for ($i = 0; $i < 1000; $i++) {
 *          $needle = rand(0, $max);
 *          //$index = array_search($needle, $large_array);
 *          $index = binarySearch($needle, $large_array);
 *          echo "$i: needle: $needle index: $index" . PHP_EOL;
 *      }
 *      $end = microtime(true);
 *      $elapsed = $end - $start;
 *      echo "Elapsed: $elapsed seconds." . PHP_EOL;
 * 
 * With array_search(), this takes 29.73 seconds.
 * With binarySearch(), this takes 0.080367 seconds.
 * 
 * @link    https://medium.com/@michaelking0191/binary-search-algorithm-in-php-b113cbb56dc6
 * @link    https://dev.to/bornfightcompany/beware-of-php-s-strcmp-function-when-sorting-3ogb
 * 
 * @param   mixed      $needle              The item to search for.
 * @param   array      $haystack            The sorted array to search within.
 * @param   ?callable  $compare             The comparison function. If null, uses strnatcmp by default.
 * @param   ?int       $high                The upper bound of the search range.
 * @param   int        $low                 The lower bound of the search range.
 * @param   bool       $containsDuplicates  Whether the array contains duplicates.
 * @return  int|bool                        The index of the found item, or false if not found.
 */
function binary_search(
    mixed $needle,
    array $haystack,
    ?callable $compare = null,
    ?int $high = null,
    int $low = 0,
    bool $containsDuplicates = false
): int|bool {
    if ($high === null) {
        $high = count($haystack) - 1;
    }

    // Use strnatcmp if no comparison function is provided.
    $compare = $compare ?? 'strnatcmp';

    // Whilst we have a range. If not, then that match was not found.
    while ($high >= $low) {
        // Find the middle of the range.
        $mid = (int)floor(($high + $low) / 2);
        // Compare the middle of the range with the needle. This should return <0 if it's in the first part of the range,
        // or >0 if it's in the second part of the range. It will return 0 if there is a match.
        $cmp = ($compare === 'strnatcmp') ? strnatcmp($needle, $haystack[$mid]) : call_user_func($compare, $needle, $haystack[$mid]);

        // Adjust the range based on the above logic, so the next loop iteration will use the narrowed range
        if ($cmp < 0) {
            $high = $mid - 1;
        } elseif ($cmp > 0) {
            $low = $mid + 1;
        } else {
            // We've found a match
            if ($containsDuplicates) {
                // Find the first item, if there is a possibility our data set contains duplicates by comparing the
                // previous item with the current item ($mid).
                while ($mid > 0 && ($compare === 'strnatcmp' ? strnatcmp($haystack[$mid - 1], $haystack[$mid]) : call_user_func($compare, $haystack[$mid - 1], $haystack[$mid])) === 0) {
                    $mid--;
                }
            }
            return $mid;
        }
    }
    return false;
}

/**
 * Wrapper for binary_search() that sorts the array before performing the search.
 * 
 * @param   mixed         $needle                 The value to search for.
 * @param   array         $haystack               The array to search in.
 * @param   int|callable  $sort_flag_or_callable  Optional. The sort flag or custom callable. Defaults to SORT_NATURAL.
 * @param   bool          $containsDuplicates     Whether the array contains duplicates.
 * @return  int|bool                              The index of the found item, or false if not found.
 */
function binary_search_with_sorting(
    mixed $needle,
    array $haystack,
    int|callable $sort_flag_or_callable = SORT_NATURAL,
    bool $containsDuplicates = false
): int|bool {
    $compare = null;

    // Determine how to sort and what compare function to use
    if (is_callable($sort_flag_or_callable)) {
        usort($haystack, $sort_flag_or_callable);
        $compare = $sort_flag_or_callable;
    } else {
        switch ($sort_flag_or_callable) {
            case SORT_NATURAL:
                sort($haystack, SORT_NATURAL);
                $compare = 'strnatcmp';
                break;

            case SORT_NUMERIC:
                sort($haystack, SORT_NUMERIC);
                $compare = function ($a, $b) {
                    return $a - $b;
                };
                break;

            case SORT_STRING:
                sort($haystack, SORT_STRING);
                $compare = 'strcmp';
                break;

            case SORT_REGULAR:
            default:
                sort($haystack, SORT_REGULAR);
                $compare = 'strcmp';
                break;
        }
    }

    // Call binary_search with the sorted array and the appropriate comparison function
    return binary_search($needle, $haystack, $compare, null, 0, $containsDuplicates);
}

/**
 * Use binary_search() to check if a needle is in a haystack.
 * This returns a Boolean value instead of the index.
 * 
 * @param   mixed         $needle      The value to search for.
 * @param   array         $haystack    The array to search in.
 * @param   bool          $sort_array  Whether the array needs to be sorted.
 * @param   int|callable  $sort_flag   Optional. The sort flag or custom callable. Defaults to SORT_NATURAL.
 * @return  bool                       true if the needle is found, false otherwise.
 * @see     binary_search
 */
function binary_in_array(
    mixed $needle,
    array $haystack,
    bool $sort_array = false,
    int|callable $sort_flag = SORT_NATURAL
): bool {
    $compare = null;

    if ($sort_array) {
        $haystack_values = array_values($haystack);

        if (is_callable($sort_flag)) {
            usort($haystack_values, $sort_flag);
            $compare = $sort_flag;
        } else {
            switch ($sort_flag) {
                case SORT_NATURAL:
                    sort($haystack_values, SORT_NATURAL);
                    $compare = 'strnatcmp';
                    break;

                case SORT_NUMERIC:
                    sort($haystack_values, SORT_NUMERIC);
                    $compare = function ($a, $b) {
                        return $a - $b;
                    };
                    break;

                case SORT_STRING:
                    sort($haystack_values, SORT_STRING);
                    $compare = 'strcmp';
                    break;

                case SORT_REGULAR:
                default:
                    sort($haystack_values, SORT_REGULAR);
                    $compare = 'strcmp';
                    break;
            }
        }
    } else {
        $haystack_values = $haystack;
    }

    $search_result = binary_search($needle, $haystack_values, $compare);

    // Return true if the search result is 0 or evaluates to true.
    return $search_result === 0 || boolval($search_result);
}

/**
 * Parses a CSV string into an array, with row validation and optional header row handling.
 * 
 * If you are getting Warnings for the $fields[$index] line, try passing "" as the $escape parameter.
 * Slashes, \, in a .csv might be meant to just be slashes rather than escape characters.
 * 
 * It skips mismatched rows. Rows are validated relative to the first row.
 * Rows whose number of fields do not match the number of fields of the first row, are skipped.
 * For instance in SEMRush exports, the bottom three rows are about subscription limits.
 * 
 * You may have to increase the memory limit e.g.:
 *      ini_set("memory_limit","512M");
 * 
 * Example: 
 *      $filename = 'files/3d_related_us_2023-01-07.csv';
 *      $file_contents = file_get_contents($filename);
 *      $parsed = str_csv_to_array($file_contents);
 *      print_r(assoc_array_truncate(csv_array_to_assoc($parsed), 0, 10));
 * 
 * This prints the first 10 rows, for each header.
 * 
 * @param   string  $string              The CSV string to be parsed.
 * @param   bool    $first_line_headers  If true, the first row is treated as headers for an associative array.
 * @param   string  $separator           The field delimiter (default: `,`).
 * @param   string  $enclosure           The field enclosure character (default: `"`).
 * @param   string  $escape              The escape character for enclosed fields (default: `\`).
 * @return  array                        The parsed CSV data as an array, either associative or indexed based on `$first_line_headers`.
 */
function str_csv_to_array(
    string $string,
    bool $first_line_headers = true,
    string $separator = ",",
    string $enclosure = "\"",
    string $escape = "\\"
): array {
    // Return an empty array if the input string is empty
    if (trim($string) === '') {
        return [];
    }

    // Split the string into lines
    $lines = split_lines($string);
    $array = [];

    if ($first_line_headers) {
        // Extract headers
        $headers = str_getcsv(array_shift($lines), $separator, $enclosure, $escape);
        $array['headers'] = $headers;
        $expectedFieldCount = count($headers);

        // Initialize empty arrays for each header if there are no data rows
        if (empty($lines)) {
            foreach ($headers as $header) {
                $array[] = [$header => []];
            }
            return $array;
        }
    } else {
        $firstLineFields = str_getcsv($lines[0], $separator, $enclosure, $escape);
        $expectedFieldCount = count($firstLineFields);
    }

    // Populate the arrays with data
    foreach ($lines as $line) {
        $fields = str_getcsv($line, $separator, $enclosure, $escape);
        // Skip rows with mismatched field counts
        if (count($fields) !== $expectedFieldCount) {
            continue;
        }

        // If $first_line_headers, create an associative array based on the headers
        // Else, create an indexed array
        if ($first_line_headers) {
            foreach ($headers as $index => $header) {
                $array[$index][$header][] = $fields[$index] ?? null;
            }
        } else {
            $array[] = $fields;
        }
    }

    return $array;
}

/**
 * Converts an array created by str_csv_to_array() with $first_line_headers true
 * into an associative array that can be used with array_to_table().
 * 
 * @param   array  $csv_data           The array created by str_csv_to_array() with headers.
 * @return  array  $associative_array  The converted associative array.
 * @see     str_csv_to_array
 */
function csv_array_to_assoc(array $csv_data): array
{
    // Initialize the array to hold the associative data
    $associative_array = [];

    // Extract headers and their corresponding keys
    $headers = $csv_data['headers'];
    $header_keys = array_keys($headers);
    $field_count = count($header_keys);

    // Iterate over each header to build the associative array
    for ($i = 0; $i < $field_count; $i++) {
        $header = $headers[$header_keys[$i]];
        // Ensure an empty array if no values exist
        $values = $csv_data[$i][$header] ?? [];
        $associative_array[$header] = $values;
    }

    return $associative_array;
}

/**
 * Fills out empty values of an associative array based on an index field.
 * 
 * Example:
 *      $array = ['Keyword' => ['Apples', 'Bananas', 'Oranges', 'Pears'], 'Color' => ['Red', 'Yellow'], 'Size' => [], 'Age' => [1, 2, 3, 4]];
 *      echo print_r(json_encode($array), true) . PHP_EOL . PHP_EOL;
 *      echo print_r(json_encode(assoc_array_fill_empty_values($array)), true) . PHP_EOL . PHP_EOL;
 * 
 * This gives:
 *      {"Keyword":["Apples","Bananas","Oranges","Pears"],"Color":["Red","Yellow"],"Size":[],"Age":[1,2,3,4]}
 *      {"Keyword":["Apples","Bananas","Oranges","Pears"],"Color":["Red","Yellow","",""],"Size":["","","",""],"Age":[1,2,3,4]}
 * 
 * In the second result, the missing values are filled with blank value.
 * 
 * @param   array   $assoc_array  The associative array to process.
 * @param   string  $index_field  The field whose length determines the required size for all other fields.
 * @return  array                 The modified associative array with filled values.
 * @see     binary_in_array
 */
function assoc_array_fill_empty_values(array $assoc_array, string $index_field = 'Keyword'): array
{
    // Throw an exception if the index field is not found
    if (!isset($assoc_array[$index_field])) {
        throw new \InvalidArgumentException("Index field '$index_field' not found in the associative array.");
    }

    $fields = array_keys($assoc_array);
    $index_keys = array_keys($assoc_array[$index_field]);

    foreach ($fields as $field) {
        if ($field !== $index_field) {
            $keys = array_keys($assoc_array[$field]);
            sort($keys);

            foreach ($index_keys as $index) {
                // Since $keys were sorted, and we aren't modifying $keys, we can use binary search
                if (!binary_in_array($index, $keys)) {
                    $assoc_array[$field][$index] = '';
                }
            }

            // Sort the field by its keys
            ksort($assoc_array[$field]);
        }
    }

    return $assoc_array;
}

/**
 * Merge multiple arrays based on a shared index field.
 * The first argument should be the field that the merge is based on.
 * 
 * Example:
 *      $first = ['FieldA' => [1, 2, 3], 'FieldB' => [10, 20, 30]];
 *      $second = ['FieldA' => ['A', 'B', 'C'], 'FieldB' => ['Apple', 'Banana', 'Carrot']];
 *      $third = ['FieldA' => [100, 2, 300], 'FieldB' => [1000, 9999, 3000]];
 *      $merged = assoc_array_merge('FieldA', $first, $second, $third);
 *      print_r($merged);
 * 
 * Here the merge is based on FieldA. With the $third array, the FieldA '2' value is associated with the FieldB value '9999'.
 * Since the $first array already had a FieldA '2' value, this '9999' value is not added. And the '20' FieldB value is kept.
 * 
 * @param   string                     $index_field  The field on which to base the merge.
 * @param   array                      ...$arrays    The arrays to be merged.
 * @return  array                                    The merged array.
 * @throws  \InvalidArgumentException                If the $index_field is not found in all arrays.
 */
function assoc_array_merge(string $index_field, array ...$arrays): array
{
    // Validate that the index field exists in all arrays
    foreach ($arrays as $array) {
        if (!array_key_exists($index_field, $array)) {
            throw new \InvalidArgumentException("Index field '$index_field' not found in one of the arrays.");
        }
    }

    // Use the first array as the base for merging
    $base = array_shift($arrays);

    // Iterate over the remaining arrays to merge them
    foreach ($arrays as $array) {
        $fields = array_keys($array);

        // Iterate over the index field values in the current array
        foreach ($array[$index_field] as $i => $index_value) {
            // Check if the index value is not already in the base array
            if (!in_array($index_value, $base[$index_field], true)) {
                $next_key = count($base[$index_field]);

                // Add values from the current array to the base array
                foreach ($fields as $field) {
                    $base[$field][$next_key] = $array[$field][$i];
                }
            }
        }
    }

    // Return the merged array
    return $base;
}


/**
 * Merges multiple arrays recursively. Values in latter arguments take precedence over values in earlier arguments.
 * Overwrites in case of associative keys. In case of numeric keys, it appends if the value is not already present.
 * 
 * Based on code in comments by martyniuk dot vasyl and mark dot roduner.
 * 
 * Example:
 *      $options1 = ['headers' => ['User-Agent' => 'Mozilla/5.0 (X11; Linux x86_64)', 'Accept-Language' => 'en-US,en;q=1.0'], 'connect_timeout' => 10, 'timeout' => 10];
 *      $options2 = ['headers' => ['User-Agent' => 'Guzzle', 'X-Foo' => ['Bar', 'Baz']], 'connect_timeout' => 20, 'timeout' => 20];
 *      $merged = array_merge_recursive_distinct($options1, $options2);
 *      print_r($merged);
 *
 * Or:
 *      $options1 = ['headers' => ['User-Agent' => 'Mozilla/5.0 (X11; Linux x86_64)', 'Accept-Language' => 'en-US,en;q=1.0'], 'connect_timeout' => 10, 'timeout' => 10];
 *      $options2 = ['headers' => ['User-Agent' => 'Guzzle', 'X-Foo' => ['Bar', 'Baz']], 'connect_timeout' => 20, 'timeout' => 20];
 *      $options3 = $options4 = $options5 = $options6 = [];
 *      $arrays = [$options4, $options2, $options3, $options1, $options5, $options6];
 *      $merged = array_merge_recursive_distinct(...$arrays);
 *      print_r($merged);
 * 
 * @link    https://stackoverflow.com/questions/1747507/merge-multiple-arrays-recursively
 * 
 * @param   array  ...$arrays  The set of arrays that will be merged. Later arrays take precedence.
 * @return  array              The merged array with distinct values.
 */
function array_merge_recursive_distinct(array ...$arrays): array
{
    // Use the first array as the base for merging
    $base = array_shift($arrays);

    // Iterate over the remaining arrays to merge them into the base array
    foreach ($arrays as $array) {
        foreach ($array as $key => $value) {
            // If both the base and the current value are arrays, merge them recursively
            // Else check if it is a numeric key. If it is not a numeric key, then add this value to the base with the given key.
            // To prevent "PHP Warning:  Undefined array key", you must suppress warnings like "@is_array($base[$key])"
            // or check for the key using isset($base[$key])
            if (is_array($value) && isset($base[$key]) && is_array($base[$key])) {
                $base[$key] = array_merge_recursive_distinct($base[$key], $value);
            }
            // For numeric keys, append to the base array if the value is not already present
            elseif (is_numeric($key)) {
                if (!in_array($value, $base, true)) {
                    $base[] = $value;
                }
            }
            // For associative keys, overwrite or add the value in the base array
            else {
                $base[$key] = $value;
            }
        }
    }

    // Return the merged array
    return $base;
}

/**
 * Converts a file to an array.
 * Optionally uses str_csv_to_array() if there is a separator.
 * 
 * @param   string                     $filename            The path to the file to be converted.
 * @param   string|null                $separator           The separator character for CSV parsing. If null, plain text lines are returned.
 * @param   bool                       $first_line_headers  If true, the first line of CSV is treated as headers.
 * @param   string                     $enclosure           The enclosure character for CSV parsing (default: `"`).
 * @param   string                     $escape              The escape character for CSV parsing (default: `\`).
 * @return  array                                           Parsed file contents as an array.
 * @see     str_csv_to_array
 * @throws  \InvalidArgumentException                       If the filename is invalid.
 * @throws  \RuntimeException                               If the file cannot be read.
 */
function file_to_array(
    string $filename,
    ?string $separator = null,
    bool $first_line_headers = false,
    string $enclosure = "\"",
    string $escape = "\\"
): array {
    // Validate the filename (basic validation for safe path usage)
    if (strpos($filename, '..') !== false) {
        throw new \InvalidArgumentException("Invalid filename: $filename");
    }
    // Check if the file exists and is readable
    if (!file_exists($filename) || !is_readable($filename)) {
        throw new \RuntimeException("File cannot be read: $filename");
    }

    // Get the contents of the file and trim whitespace
    $file_contents = trim(file_get_contents($filename));
    // Normalize line endings to Unix-style (\n) for consistent processing
    $file_contents = str_replace(["\r\n", "\r"], "\n", $file_contents);
    // If a separator is provided, parse as CSV
    if ($separator !== null) {
        return str_csv_to_array($file_contents, $first_line_headers, $separator, $enclosure, $escape);
    }

    // Otherwise, split the file into an array of lines
    return explode("\n", $file_contents);
}

/**
 * Build a complex array of nested arrays
 * Generates nested arrays based on optional hash string
 * 
 * Can be used to create an array for testing that is constant because it is hashed
 * and that has a mix of int and string keys, and nested elements of varying counts
 * 
 * Example:
 *    $options = array('Apples', 'Pears', 10, "<Tag's>", 3.14, 20, 'Bananas', -5,
 *                     'A "string" with <strong>tags</strong>.', "Are tomatoes\n<i>fruits</i>?\nWho's to say?");
 *    $nested_array = build_nested_array($options, .5, 5, 4, true, 1);
 * 
 * @param   array   $options                 The set of possible values.
 * @param   float   $string_key_probability  The chances the key is a string. If 0, all keys are integers.
 * @param   int     $items                   The number of nested items in the array.
 * @param   int     $depth                   The depth to generate nesting up to.
 * @param   bool    $randomize_item_counts   If true, the number of items for each array is randomized.
 * @param   string  $hash_string             The base hash string. Can be used to generate consistent results.
 *                                           If set, the randomization is based on hashing. Else it is random.
 * @return  array
 * @see     hashed_array_element
 * @see     hashed_probability
 * @see     random_probability
 */
function build_nested_array(
    array $options = array(),
    float $string_key_probability = 0,
    int $items = 10,
    int $depth = 2,
    bool $randomize_item_counts = false,
    string $hash_string = ''
): array {
    // Initialize the result array
    $result = array();

    // Use a default range if no options are provided
    if (empty($options)) {
        $options = range(0, 1000);
    }

    // Determine the maximum number of items based on randomization or hashing
    $use_hash = strlen($hash_string) > 0;
    $max = $randomize_item_counts
        ? ($use_hash ? hashed_array_element($hash_string, range(1, $items)) : random_int(1, $items))
        : $items;

    for ($i = 0; $i < $max; $i++) {
        // Generate a sub-hash string if needed
        $sub_hash_string = $use_hash ? $hash_string . $i : '';

        // Determine the key type (string or integer)
        $key = $i;
        if ($string_key_probability > 0) {
            $prob = $use_hash ? hashed_probability($sub_hash_string) : random_probability();
            if ($prob < $string_key_probability) {
                $key = number_to_words($i);
            }
        }

        // If depth is 0, assign a value from options
        if ($depth === 0) {
            $result[$key] = $use_hash
                ? hashed_array_element($sub_hash_string, $options)
                : $options[array_rand($options)];
        } else {
            // Recursively build nested arrays
            $result[$key] = build_nested_array(
                $options,
                $string_key_probability,
                $items,
                $depth - 1,
                $randomize_item_counts,
                $sub_hash_string
            );
        }
    }

    return $result;
}

/**
 * Calculate the symmetric difference between two arrays.
 * Returns the values that are in either of the arrays but not in both.
 * 
 * array_diff() gives the values in the first array that are not in subsequent arrays.
 * This applies array_diff() both ways.
 * 
 * @param   array  $array1  The first array to compare.
 * @param   array  $array2  The second array to compare.
 * @return  array           The symmetric difference of the two arrays.
 */
function array_diff_net(array $array1, array $array2): array
{
    $diff_from_array1 = array_diff($array1, $array2);
    $diff_from_array2 = array_diff($array2, $array1);
    return array_merge($diff_from_array1, $diff_from_array2);
}

/**
 * Recursively computes the difference between two arrays.
 * Returns the values from the first array that are not present in the second array, including nested arrays.
 *
 * Modified from treeface's answer
 * 
 * @link    https://stackoverflow.com/questions/3876435/recursive-array-diff
 * 
 * @param   array  $array1  The first array to compare.
 * @param   array  $array2  The second array to compare.
 * @return  array           The recursive difference of the two arrays.
 */
function array_diff_recursive(array $array1, array $array2): array
{
    $diff = [];

    foreach ($array1 as $key => $value) {
        // If the key exists in the second array, recursively call this function if it is an array,
        // Otherwise check if the value is in arr2
        if (array_key_exists($key, $array2)) {
            // If both values are arrays, compute the recursive difference
            // Added check for && is_array($arr2[$key])
            if (is_array($value) && is_array($array2[$key])) {
                $recursiveDiff = array_diff_recursive($value, $array2[$key]);

                // If the recursive difference is not empty, add it to the result
                if (count($recursiveDiff)) {
                    $diff[$key] = $recursiveDiff;
                }
            }
            // If the values differ, add the value from the first array to the result
            elseif (!in_array($value, $array2, true)) {
                $diff[$key] = $value;
            }
        }
        // If the key is not in the second array, check if the value is in
        // the second array (this is a quirk of how array_diff works)
        elseif (!in_array($value, $array2, true)) {
            $diff[$key] = $value;
        }
    }

    // Return the computed difference
    return $diff;
}

/**
 * Applies array_diff_recursive() both ways.
 * Computes the recursive difference between two arrays in both directions, and merges the results.
 * 
 * Example:
 *      $foo = [0, 1, 2, 'three' => 3, 4, 'five' => 5, 6, 'six' => 6, 7, 'notseven' => 6, 8, array(3, 4, 5), 'hundreds' => array(100, 200, 300, 400)];
 *      $moo = [0, 'three' => 3, 4, 1, 7, 'notseven' => 666, 'five' => 555, 2, 10, 11, 'twelve' => 12, 13, 'hundreds' => array(100, 300, 500), array(3, 7, 5)];
 *      print_r($foo);
 *      print_r($moo);
 *      print_r(array_diff($foo, $moo)); // This will give "Warning: Array to string conversion"
 *      print_r(array_diff_recursive($foo, $moo));
 *      print_r(array_diff_recursive($moo, $foo));
 *      print_r(array_diff_recursive_net($foo, $moo));
 * 
 * @param   array  $array1  The first array to compare.
 * @param   array  $array2  The second array to compare.
 * @return  array           The merged array containing differences from both comparisons.
 * @see     array_diff_recursive
 * @see     array_merge_recursive
 */
function array_diff_recursive_net(array $array1, array $array2): array
{
    $diff1 = array_diff_recursive($array1, $array2);
    $diff2 = array_diff_recursive($array2, $array1);
    return array_merge_recursive($diff1, $diff2);
}

/**
 * Sorts an array by specified field values.
 * 
 * Example:
 *      $data[] = array('volume' => 67, 'edition' => 2);
 *      $data[] = array('volume' => 86, 'edition' => 1);
 *      $data[] = array('volume' => 85, 'edition' => 6);
 *      $data[] = array('volume' => 98, 'edition' => 2);
 *      $data[] = array('volume' => 86, 'edition' => 10);
 *      $data[] = array('volume' => 86, 'edition' => 6);
 *      $data[] = array('volume' => 67, 'edition' => 7);
 *      $sorted = array_sort_by_fields($data, 'volume', SORT_DESC, 'edition', SORT_ASC);
 *      print_r($sorted);
 * 
 * @link    https://www.php.net/array_multisort
 * @link    https://stackoverflow.com/questions/4582649/php-sort-array-by-two-field-values
 * 
 * @return  array    The sorted array.
 */
function array_sort_by_fields(array $data, ...$fields): array
{
    $validFields = false;

    // Prepare sorting arrays for each field
    foreach ($fields as $index => $field) {
        if (is_string($field)) {
            $field_values = array_column($data, $field);
            // Check if the field exists in the data
            if (count(array_filter($field_values, fn($value) => $value !== null)) > 0) {
                $fields[$index] = $field_values;
                $validFields = true;
            } else {
                // Remove non-existing field from the $fields array
                unset($fields[$index]);
            }
        }
    }

    // If no valid fields found, return the original data
    if (!$validFields) {
        return $data;
    }

    // Add the original array to the sorting arguments.
    // Since $data is not passed by reference in the function definition,
    // the original array outside the function is not modified.
    $fields[] = &$data;

    // Perform the multi-field sort
    array_multisort(...$fields);

    // Return the sorted array
    return $data;
}

/**
 * Adjust counts to balance the distribution.
 * 
 * This function increments and decrements counts to balance the distribution.
 * It only adjusts counts by a single unit in each call.
 *
 * @param   array                      $counts  The current count distribution
 * @param   int                        $start   The start of the range to adjust
 * @param   int                        $end     The end of the range to adjust
 * @param   int                        $step    The step direction (1 or -1)
 * @return  int                                 Returns 1 if an adjustment was made, otherwise 0.
 * @throws  \InvalidArgumentException           If the step is not 1 or -1.
 */
function adjust_counts(array &$counts, int $start, int $end, int $step): int
{
    // Validate the step parameter
    if ($step !== 1 && $step !== -1) {
        throw new \InvalidArgumentException("Step must be 1 or -1.");
    }

    // Get the keys in the range [start, end] considering only keys that exist in $counts
    $keys = array_keys($counts);
    $keys = array_filter($keys, function ($key) use ($start, $end, $step) {
        return ($step === 1) ? ($key >= $start && $key <= $end) : ($key <= $start && $key >= $end);
    });

    // Sort keys to ensure proper iteration
    if ($step === 1) {
        sort($keys);
    } else {
        rsort($keys);
    }

    // Iterate over the filtered and sorted keys in the counts array
    foreach ($keys as $index => $i) {
        // Check if there is a next key in the sequence
        if (isset($keys[$index + 1])) {
            $nextKey = $keys[$index + 1];
            // If the current key's count is greater than the next key's count, 
            // decrement the current key's count and increment the next key's count
            if ($counts[$i] > $counts[$nextKey]) {
                $counts[$i]--;
                $counts[$nextKey]++;
                // Return 1 to indicate that an adjustment was made
                return 1;
            }
        }
    }

    return 0;
}


/**
 * Generates an array of numbers evenly distributed within the specified range.
 * 
 * This function attempts to generate an array of numbers evenly distributed 
 * between the $min to $max range. The function only works properly if the 
 * count is greater than the difference between max and min.
 * 
 * The weighted sum of the counts of the result:
 * array_weighted_sum(array_count_values($result));
 * should equal the average of $min and $max, times the $count.
 * 
 * @param   int                        $count  The number of items to generate
 * @param   int                        $min    The lowest array value to generate
 * @param   int                        $max    The highest array value to generate
 * @return  array                              The generated array of numbers.
 * @throws  \InvalidArgumentException
 * @see     adjust_counts
 */
function generate_balanced_array(int $count, int $min, int $max): array
{
    if ($count <= 0 || $min > $max) {
        throw new \InvalidArgumentException(
            "The maximum value ($max) must be greater than or equal to the minimum value ($min)."
        );
    }

    // Calculate the distribution parameters
    $range     = $max - $min + 1;
    $targetSum = (int) (($min + $max) / 2 * $count);
    $baseCount = (int) floor($count / $range);
    $counts    = array_fill($min, $range, $baseCount);
    $remaining = $count - array_sum($counts);

    // Distribute the remaining counts
    for ($i = $min; $remaining > 0; $i = ($i + 1 > $max) ? $min : $i + 1) {
        $counts[$i]++;
        $remaining--;
    }

    // Calculate the current weighted sum
    $currentSum = array_sum(array_map(function ($k, $v) {
        return $k * $v;
    }, array_keys($counts), $counts));

    // Adjust the counts until the current sum matches the target sum
    while ($currentSum !== $targetSum) {
        if ($currentSum < $targetSum) {
            $currentSum += adjust_counts($counts, $min, $max, 1);
        } else {
            $currentSum -= adjust_counts($counts, $max, $min, -1);
        }
    }

    // Generate the final array based on the counts
    $result = [];
    foreach ($counts as $value => $count) {
        $result = array_merge($result, array_fill(0, $count, $value));
    }

    // Shuffle the array to randomize the order of values
    shuffle($result);

    return $result;
}

/**
 * Combines isset() and is_array()
 * 
 * To avoid, "Warning: Undefined variable"
 * you need to add @ to the variable to suppress warnings
 * 
 * Example:
 *      $bool = isset_array(@$somevar);
 * 
 * @param   mixed  $value  The variable to check.
 * @return  bool           true if the variable is set and is an array, false otherwise.
 */
function isset_array(mixed $value): bool
{
    return isset($value) && is_array($value);
}

/**
 * Applies strstr() to an array of needles.
 * 
 * Searches for the first occurrence of any string in the $needles array 
 * within the $haystack string. If found, it returns the portion of the 
 * haystack string starting from the first match.
 * 
 * @param   string       $haystack       The input string to search in.
 * @param   array        $needles        An array of strings to search for.
 * @param   bool         $before_needle  Whether to return the part before the needle (default is false).
 * @return  string|bool                  The portion of the haystack string if found, otherwise false.
 */
function strstr_array(string $haystack, array $needles, bool $before_needle = false): string|bool
{
    foreach ($needles as $needle) {
        $result = strstr($haystack, $needle, $before_needle);
        if ($result !== false) {
            return $result;
        }
    }
    return false;
}

/**
 * Applies stristr() to an array of needles.
 * 
 * Searches for the first occurrence of any string in the $needles array 
 * within the $haystack string, ignoring case. If found, it returns the 
 * portion of the haystack string starting from the first match or, optionally, 
 * the part before the match.
 * 
 * @param   string       $haystack       The input string to search in.
 * @param   array        $needles        An array of strings to search for.
 * @param   bool         $before_needle  Whether to return the part before the needle (default is false).
 * @return  string|bool                  The portion of the haystack string if found, otherwise false.
 */
function stristr_array(string $haystack, array $needles, bool $before_needle = false): string|bool
{
    foreach ($needles as $needle) {
        $result = stristr($haystack, $needle, $before_needle);
        if ($result !== false) {
            return $result;
        }
    }
    return false;
}

/**
 * Determines if the given array is a simple indexed array.
 * 
 * A simple indexed array has the following characteristics:
 * - All keys are integers
 * - Keys may not be sequential
 * - All values are non-array (scalar or null)
 * 
 * @param   array  $array  The array to check
 * @return  bool           True if the array is a simple indexed array, false otherwise
 */
function is_indexed_array(array $array): bool
{
    if (empty($array)) {
        // Empty arrays are considered indexed
        return true;
    }

    foreach ($array as $key => $value) {
        if (!is_int($key) || is_array($value)) {
            return false;
        }
    }

    return true;
}

/**
 * Determines if the given array is column-based.
 * 
 * A column-based array has the following characteristics:
 * - All top-level elements are arrays (columns)
 * - All columns have the same number of elements
 * - The keys of the top-level array are strings (column names)
 * 
 * Any scalar value (including null, empty strings, integers, floats, and booleans) 
 * is considered valid data. Nested arrays are not allowed.
 * 
 * Example of a column-based array:
 * [
 *     'name' => ['Alice', 'Bob', 'Charlie'],
 *     'age'  => [25, 30, 35],
 *     'city' => ['New York', 'London', 'Paris']
 * ]
 * 
 * @param   array  $data  The array to check
 * @return  bool          True if the array is column-based, false otherwise
 */
function is_column_based_array(array $data): bool
{
    // Check if the array is empty or not associative
    if (empty($data) || array_keys($data) === range(0, count($data) - 1)) {
        return false;
    }

    $expected_length = null;

    foreach ($data as $key => $column) {
        // Check if key is a string and column is an array
        if (!is_string($key) || !is_array($column)) {
            return false;
        }

        $column_length = count($column);

        // Set or check the expected length
        if ($expected_length === null) {
            $expected_length = $column_length;
        } elseif ($column_length !== $expected_length) {
            return false;
        }

        // Check if all elements in the column are not arrays
        foreach ($column as $value) {
            if (is_array($value)) {
                return false;
            }
        }
    }

    return true;
}

/**
 * Determines if the given array is row-based.
 * 
 * A row-based array has the following characteristics:
 * - All elements are arrays (rows)
 * - All rows have the same keys
 * - The keys of each row are strings (column names)
 * 
 * Any scalar value (including null, empty strings, integers, floats, and booleans) 
 * is considered valid data. Nested arrays are not allowed.
 * 
 * Example of a row-based array:
 * [
 *     ['name' => 'Alice', 'age' => 25, 'city' => 'New York'],
 *     ['name' => 'Bob',   'age' => 30, 'city' => 'London'],
 *     ['name' => 'Charlie', 'age' => 35, 'city' => 'Paris']
 * ]
 * 
 * @param   array  $data  The array to check
 * @return  bool          True if the array is row-based, false otherwise
 */
function is_row_based_array(array $data): bool
{
    // Check if the array is empty
    if (empty($data)) {
        return false;
    }

    $first_row = reset($data);

    // Check if the first element is an associative array
    if (!is_array($first_row) || array_keys($first_row) === range(0, count($first_row) - 1)) {
        return false;
    }

    $expected_keys = array_keys($first_row);

    foreach ($data as $row) {
        // Check if row is an array with the same keys as the first row
        if (!is_array($row) || array_keys($row) !== $expected_keys) {
            return false;
        }

        // Check if all values in the row are not arrays
        foreach ($row as $key => $value) {
            if (!is_string($key) || is_array($value)) {
                return false;
            }
        }
    }

    return true;
}

/**
 * Convert the array structure to a row-based format for consistent processing.
 *
 * This function takes an array and converts it into a consistent row-based structure, regardless of whether 
 * the input is column-based, row-based, or indexed. It throws an exception if the input array structure 
 * is invalid or unsupported.
 *
 * @param   array                      $data  The input array, which can be column-based, row-based, or indexed.
 * @return  array                             The converted array in row-based format.
 * @throws  \InvalidArgumentException         If the input array structure is invalid or not recognized.
 *
 * @see     is_column_based_array
 * @see     is_row_based_array
 * @see     is_indexed_array
 */
function convert_array_to_row_structure(array $data): array
{
    // Handle the case where the array is empty.
    if (empty($data)) {
        return [];
    }

    if (is_column_based_array($data)) {
        // Convert a column-based array to a row-based structure.
        $keys        = array_keys($data);
        $max_length  = max(array_map('count', $data)); // Determine the longest column.
        $normalized  = [];

        // Loop through each index and build a row by extracting corresponding column values.
        for ($i = 0; $i < $max_length; $i++) {
            $row = [];
            foreach ($keys as $key) {
                // Ensure missing values are handled by using an empty string as the default.
                $row[$key] = $data[$key][$i] ?? '';
            }
            $normalized[] = $row;
        }

        return $normalized;
    }

    if (is_row_based_array($data)) {
        // No transformation needed for row-based arrays.
        return $data;
    }

    if (is_indexed_array($data)) {
        // Treat a simple indexed array as a single row.
        return $data;
    }

    // If none of the conditions match, throw an exception for unsupported array structures.
    throw new \InvalidArgumentException('Invalid array structure. Must be indexed, column-based, or row-based.');
}

/**
 * Gets the headers for the CSV based on the array structure.
 *
 * @param   array  $data             The input array
 * @param   bool   $is_column_based  Whether the array is column-based
 * @return  array                    The headers for the CSV
 */
function get_csv_headers(array $data, bool $is_column_based): array
{
    if ($is_column_based) {
        return array_keys($data);
    }
    $first_row = reset($data);
    return is_array($first_row) ? array_keys($first_row) : [];
}

/**
 * Writes a single CSV row.
 *
 * @param   resource           $handle       The file handle to write to
 * @param   array              $row          The row data to write
 * @param   string             $delimiter    The delimiter to use
 * @param   string             $enclosure    The enclosure character
 * @param   string             $escape_char  The escape character
 * @return  void
 * @throws  \RuntimeException                If unable to write CSV row
 */
function write_csv_row($handle, array $row, string $delimiter, string $enclosure, string $escape_char): void
{
    $result = fputcsv($handle, $row, $delimiter, $enclosure, $escape_char);
    if ($result === false) {
        throw new \RuntimeException('Failed to write CSV row.');
    }
}

/**
 * Writes column-based data to the CSV.
 *
 * @param   resource  $handle       The file handle to write to
 * @param   array     $data         The column-based data to write
 * @param   string    $delimiter    The delimiter to use
 * @param   string    $enclosure    The enclosure character
 * @param   string    $escape_char  The escape character
 * @return  void
 * @see     write_csv_row
 */
function write_column_based_data($handle, array $data, string $delimiter, string $enclosure, string $escape_char): void
{
    // Determine the maximum number of rows
    $row_count = max(array_map('count', $data));

    // Write each row
    for ($i = 0; $i < $row_count; $i++) {
        $row = array_map(static function ($column) use ($i) {
            return $column[$i] ?? '';
        }, $data);
        write_csv_row($handle, $row, $delimiter, $enclosure, $escape_char);
    }
}

/**
 * Writes row-based data to the CSV.
 *
 * @param   resource                   $handle       The file handle to write to
 * @param   array                      $data         The row-based data to write
 * @param   string                     $delimiter    The delimiter to use
 * @param   string                     $enclosure    The enclosure character
 * @param   string                     $escape_char  The escape character
 * @return  void
 * @throws  \InvalidArgumentException                If any row is not an array
 * @see     write_csv_row
 */
function write_row_based_data($handle, array $data, string $delimiter, string $enclosure, string $escape_char): void
{
    foreach ($data as $row) {
        if (!is_array($row)) {
            throw new \InvalidArgumentException('Each row must be an array.');
        }
        write_csv_row($handle, $row, $delimiter, $enclosure, $escape_char);
    }
}

/**
 * Converts an array to a CSV string.
 * 
 * This function normalizes the input array to a row-based structure using
 * `convert_array_to_row_structure()` and then writes it to a CSV format.
 * 
 * It handles indexed arrays, column-based arrays, and row-based arrays.
 * Throws an exception if the input array is not indexed, column-based, or row-based.
 * 
 * Since `fputcsv()` adds a newline character (\n) to the end of each row, 
 * the generated CSV string will also have a trailing newline. Use `trim()` 
 * if you need to remove the trailing newline from the final output.
 * 
 * @param   array                      $data             The input array to be converted to CSV format.
 * @param   string                     $delimiter        The delimiter to use between values (default is a comma).
 * @param   string                     $enclosure        The enclosure character to use for fields (default is a double quote).
 * @param   string                     $escape_char      The escape character for enclosed values (default is a backslash).
 * @param   bool                       $include_headers  Whether to include column headers in the CSV output (default is true).
 * @return  string                                       The generated CSV string.
 * @throws  \RuntimeException                            If unable to open or read the temporary file stream.
 * @throws  \InvalidArgumentException                    If the input array structure is invalid or not recognized.
 *
 * @see     convert_array_to_row_structure
 * @see     is_indexed_array
 */
function array_to_csv(
    array $data,
    string $delimiter = ',',
    string $enclosure = '"',
    string $escape_char = '\\',
    bool $include_headers = true
): string {
    if (empty($data)) {
        return '';
    }

    // Normalize the data structure to a row-based format
    $data = convert_array_to_row_structure($data);

    // Open a temporary stream for writing the CSV content
    $output = fopen('php://temp', 'r+');
    if ($output === false) {
        throw new \RuntimeException('Failed to open temporary file stream.');
    }

    try {
        // Ensure that $data is properly structured as rows (even for indexed arrays)
        if (is_indexed_array($data)) {
            // Treat the entire indexed array as a single row
            fputcsv($output, $data, $delimiter, $enclosure, $escape_char);
        } else {
            // Get headers from the first row if include_headers is true and the array is associative
            if ($include_headers && !empty($data) && is_array(reset($data))) {
                $headers = array_keys(reset($data));
                fputcsv($output, $headers, $delimiter, $enclosure, $escape_char);
            }

            // Write each row to the CSV
            foreach ($data as $row) {
                // Ensure each row is an array before passing to fputcsv
                fputcsv($output, (array) $row, $delimiter, $enclosure, $escape_char);
            }
        }

        // Rewind the stream to read the data back as a string
        rewind($output);
        $csv_string = stream_get_contents($output);
        if ($csv_string === false) {
            throw new \RuntimeException('Failed to read CSV data from stream.');
        }

        return $csv_string;
    } finally {
        fclose($output);
    }
}

/**
 * Aligns an array's columns for improved readability, returning a string.
 * 
 * This function normalizes the input array to a row-based structure using
 * `convert_array_to_row_structure()`, calculates column widths, and then
 * aligns the columns into a string for display.
 * 
 * @param   array                      $data              The input array to be aligned.
 * @param   string                     $alignment         The default alignment direction ('left', 'right', 'center'). Default is 'left'.
 * @param   bool                       $left_align_first  Whether to left-align the first column. Default is true.
 * @param   bool                       $use_width         Whether to use mb_strwidth() instead of mb_strlen(). Defaults to true.
 * @return  string                                        The aligned data as a string.
 *
 * @throws  \InvalidArgumentException                     If the input array structure is invalid or not recognized.
 *
 * @see     convert_array_to_row_structure
 * @see     pad_string
 * @see     calculate_column_widths
 */
function align_array_columns(
    array $data,
    string $alignment = 'left',
    bool $left_align_first = true,
    bool $use_width = true
): string {
    // Validate alignment
    $valid_alignments = ['left', 'right', 'center'];
    if (!in_array($alignment, $valid_alignments, true)) {
        throw new \InvalidArgumentException("Invalid alignment: $alignment. Use 'left', 'right', or 'center'.");
    }

    if (empty($data)) {
        return '';
    }

    // Convert the input array to a row-based structure
    $row_based_data = convert_array_to_row_structure($data);

    // Handle simple indexed arrays as a single row
    if (is_indexed_array($row_based_data)) {
        $row_based_data = [$row_based_data]; // Treat the indexed array as a single row
    }

    // Calculate the maximum width for each column
    $column_widths = calculate_column_widths($row_based_data, $use_width);

    // Align each row according to the calculated widths and alignment rules
    $aligned_lines = array_map(
        function ($row) use ($column_widths, $alignment, $left_align_first, $use_width) {
            $aligned_row = [];
            foreach ($row as $index => $cell) {
                $cell_alignment = ($index === 0 && $left_align_first) ? 'left' : $alignment;
                $aligned_row[$index] = pad_string((string)$cell, $column_widths[$index], $cell_alignment, $use_width);
            }
            return implode(' ', $aligned_row);
        },
        $row_based_data
    );

    // Join aligned rows with newline characters for display
    return implode(PHP_EOL, $aligned_lines);
}
