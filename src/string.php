<?php

/**
 * String Manipulation Utility Functions
 *
 * This file provides a comprehensive set of string manipulation functions.
 * It includes utilities for text processing, formatting, sanitization,
 * and various string operations.
 *
 * Key features:
 * - Text sanitization and escaping
 * - String formatting and padding
 * - CSV string handling
 * - HTML and CSS-related string operations
 */

namespace FOfX\Helper;

/**
 * Generate a password using a random selection of characters.
 *
 * @param int  $length            The length of the password (minimum 4 characters).
 * @param bool $include_numbers   Whether to include numbers in the password.
 * @param bool $include_uppercase Whether to include uppercase letters.
 * @param bool $include_special   Whether to include special characters.
 *
 * @throws \InvalidArgumentException If the password length is less than 4 characters.
 *
 * @return string The generated password.
 */
function generate_password(int $length = 8, bool $include_numbers = true, bool $include_uppercase = true, bool $include_special = false): string
{
    if ($length < 4) {
        throw new \InvalidArgumentException('Password length must be at least 4 characters.');
    }

    // Define character sets
    $char_sets = [
        'lowercase' => 'abcdefghijklmnopqrstuvwxyz',
        'numbers'   => '0123456789',
        'uppercase' => 'ABCDEFGHIJKLMNOPQRSTUVWXYZ',
        'special'   => '!@#$%^&*',
    ];

    // Define which character sets to include
    $include_sets = [
        'lowercase' => true,
        'numbers'   => $include_numbers,
        'uppercase' => $include_uppercase,
        'special'   => $include_special,
    ];

    $available_characters = '';
    $password             = '';

    // Build the available characters string and the password string
    foreach ($include_sets as $set => $include) {
        if ($include) {
            $available_characters .= $char_sets[$set];
            $set_length = strlen($char_sets[$set]);
            $password .= $char_sets[$set][random_int(0, $set_length - 1)];
        }
    }

    $available_length = strlen($available_characters);
    // Add characters until the password is the desired length
    while (strlen($password) < $length) {
        $password .= $available_characters[random_int(0, $available_length - 1)];
    }

    // Shuffle the password to ensure randomness
    return str_shuffle($password);
}

/**
 * Formats a number of bytes into a human-readable string.
 *
 * @link    https://stackoverflow.com/questions/2510434/format-bytes-to-kilobytes-megabytes-gigabytes
 *
 * @param int $bytes     The number of bytes to format.
 * @param int $precision The number of decimal places to round to.
 *
 * @throws \InvalidArgumentException If a negative value is passed for bytes.
 *
 * @return string The formatted size string with appropriate unit.
 */
function format_bytes(int $bytes, int $precision = 2): string
{
    // Handle invalid input: negative bytes don't make sense in this context
    if ($bytes < 0) {
        throw new \InvalidArgumentException('Byte value must be a non-negative integer.');
    }
    if ($precision < 0) {
        throw new \InvalidArgumentException('Precision must be a non-negative integer.');
    }

    // Define the available units
    $units = ['B', 'KB', 'MB', 'GB', 'TB', 'PB', 'EB', 'ZB', 'YB'];

    // Calculate the power of 1024 to use the appropriate unit
    // Use 1 to avoid log(0) error
    $pow = floor(log($bytes ?: 1) / log(1024));
    // Ensure the power doesn't exceed the available units
    $pow = min($pow, count($units) - 1);

    // Divide the byte value by the appropriate factor to scale it
    $bytes /= pow(1024, $pow);

    // Format the final output string with the correct precision and unit
    return round($bytes, $precision) . ' ' . $units[$pow];
}

/**
 * Apply format_bytes() recursively to all numeric elements in an array.
 *
 * @param array $array     The array to format.
 * @param int   $precision The number of decimal places to round to.
 *
 * @return array The array with formatted byte values.
 */
function format_bytes_array(array $array, int $precision = 2): array
{
    $result = [];

    foreach ($array as $key => $value) {
        // If the element is an array, apply the function recursively and store the result
        if (is_array($value)) {
            $result[$key] = format_bytes_array($value, $precision);
        }

        // If the value is numeric, apply format_bytes to format the byte value
        elseif (is_numeric($value)) {
            $result[$key] = format_bytes($value, $precision);
        }

        // Otherwise, just copy the value as is
        else {
            $result[$key] = $value;
        }
    }

    return $result;
}

/**
 * Remove the www. prefix from the domain, if there is one.
 * If no domain is provided, defaults to the server's HTTP host or 'localhost'.
 * Domain should not have 'http://' or 'https://' prefix
 *
 * @param ?string $domain
 *
 * @return string
 */
function strip_www(?string $domain = null): string
{
    // Use the provided domain or default to HTTP_HOST or 'localhost'
    $domain = $domain ?: $_SERVER['HTTP_HOST'] ?? 'localhost';

    // Use strncasecmp for case-insensitive comparison of the first 4 characters
    return (strncasecmp($domain, 'www.', 4) === 0) ? substr($domain, 4) : $domain;
}

/**
 * Convert a relative file path to an HTML-accessible path by removing the document root from the real path.
 *
 * @link    https://www.php.net/manual/en/function.realpath.php
 *
 * @param string  $relative_path The relative file path to convert.
 * @param ?string $document_root The document root to be removed from the real path. Defaults to $_SERVER['DOCUMENT_ROOT'].
 *
 * @throws \InvalidArgumentException If the relative path is invalid.
 *
 * @return string
 */
function htmlpath(string $relative_path, ?string $document_root = null): string
{
    // Default to $_SERVER['DOCUMENT_ROOT'] if $document_root is not provided
    $document_root = $document_root ?? $_SERVER['DOCUMENT_ROOT'];

    // Resolve the absolute path
    $realpath = realpath($relative_path);

    // Check if the path was resolved correctly
    if ($realpath === false) {
        throw new \InvalidArgumentException("Invalid path provided: {$relative_path}");
    }

    // Convert to HTML path by stripping the document root
    return str_replace($document_root, '', $realpath);
}

/**
 * Extracts all the variables from a string.
 *
 * @link     https://stackoverflow.com/questions/19562936/find-all-php-variables-with-preg-match
 *
 * @param string $subject The string containing variables to extract.
 * @param bool   $unique  Whether to return only unique variables.
 *
 * @return array An array of matched variables and their names without the $ symbol.
 *
 * @see      super_unique
 *
 * @example
 *     $variable_string = 'Hallo $var. Goodbye \$var1. blabla $var, $iam a var $varvarvar gfg djf jdfgjh fd $variable word\$escapedvar word$afterwordvar';
 *     print_r(Helper\string_get_vars($variable_string));
 *
 *     // Output (the escaped literals are not captured):
 *     Array
 *     (
 *         [0] => Array
 *             (
 *                 [0] => $var
 *                 [1] => $var
 *                 [2] => $iam
 *                 [3] => $varvarvar
 *                 [4] => $variable
 *                 [5] => $afterwordvar
 *             )
 *
 *         [1] => Array
 *             (
 *                 [0] => var
 *                 [1] => var
 *                 [2] => iam
 *                 [3] => varvarvar
 *                 [4] => variable
 *                 [5] => afterwordvar
 *             )
 *     )
 */

function string_get_vars(string $subject, bool $unique = false): array
{
    // Don't capture if there is an escape, '\', before the '$'
    $pattern = '/(?<!\\\)\$([a-zA-Z_\x7f-\xff][a-zA-Z0-9_\x7f-\xff]*)/';

    preg_match_all($pattern, $subject, $matches);
    if ($unique) {
        return super_unique($matches);
    }

    return $matches;
}

/**
 * Replaces variables in a string with their equivalent from a given PHP superglobal or scope.
 *
 * @param string $inputString The input string containing variables.
 * @param string $scope       The name of the scope (e.g., 'GLOBALS', '_SERVER').
 *
 * @throws \InvalidArgumentException If an invalid scope is provided.
 *
 * @return string The string with variables replaced by the specified scope reference.
 *
 * @see      string_get_vars()
 *
 * @example
 *     $subject = 'Hello $var, please check $id.';
 *     echo replace_vars_scope($subject, 'GLOBALS');
 *     // Output: Hello $GLOBALS['var'], please check $GLOBALS['id'].
 */
function replace_vars_scope(string $inputString, string $scope = 'GLOBALS'): string
{
    $allowedScopes = ['GLOBALS', '_SERVER', '_GET', '_POST', '_FILES', '_COOKIE', '_SESSION', '_REQUEST', '_ENV'];

    if (!in_array($scope, $allowedScopes, true)) {
        throw new \InvalidArgumentException('Invalid scope provided. Allowed scopes are: ' . implode(', ', $allowedScopes));
    }

    $variables = string_get_vars($inputString);

    if (empty($variables[0])) {
        return $inputString;
    }

    $replacements = array_combine(
        $variables[0],
        array_map(function ($var) use ($scope) {
            return '$' . $scope . '[\'' . substr($var, 1) . '\']';
        }, $variables[0])
    );

    // Sort replacements by length (descending) to avoid replacing substrings of longer variable names
    uksort($replacements, function ($a, $b) {
        return strlen($b) - strlen($a);
    });

    return strtr($inputString, $replacements);
}

/**
 * Renders an array with HTML headings for the keys, optionally applying htmlspecialchars() to both keys and values.
 * The function can either echo the resulting string or return it based on the provided parameters.
 *
 * @param array  $array                The input array to format.
 * @param bool   $use_htmlspecialchars Whether to apply htmlspecialchars() on keys and values.
 * @param bool   $return_as_string     Whether to return the formatted string instead of echoing it.
 * @param string $heading_tag          The HTML tag to wrap keys with (e.g., 'h2', 'h3').
 *                                     Use 'auto' for dynamic header levels based on array depth.
 * @param int    $start_depth          The starting depth of the array.
 *                                     Used when $heading_tag is 'auto' to determine initial header level.
 *
 * @throws \InvalidArgumentException If an invalid heading tag is provided.
 *
 * @return ?string The formatted HTML string if $return_as_string is true, otherwise null.
 */
function print_array_with_headings(
    array $array,
    bool $use_htmlspecialchars = true,
    bool $return_as_string = false,
    string $heading_tag = 'h2',
    int $start_depth = 1
): ?string {
    $valid_heading_tags = ['h1', 'h2', 'h3', 'h4', 'h5', 'h6', 'auto'];
    if (!in_array(strtolower($heading_tag), $valid_heading_tags, true)) {
        throw new \InvalidArgumentException('Invalid heading tag. Allowed tags are: ' . implode(', ', $valid_heading_tags));
    }

    $output = '';

    foreach ($array as $key => $value) {
        $formatted_key = $use_htmlspecialchars ? htmlspecialchars($key, ENT_QUOTES, 'UTF-8') : $key;

        if (is_array($value)) {
            $formatted_value = print_array_with_headings(
                $value,
                $use_htmlspecialchars,
                true,
                $heading_tag,
                $heading_tag === 'auto' ? $start_depth + 1 : $start_depth
            );
        } else {
            $formatted_value = $value ?? '';
            if ($use_htmlspecialchars) {
                $formatted_value = htmlspecialchars($formatted_value, ENT_QUOTES, 'UTF-8');
            }
        }

        $current_tag = $heading_tag === 'auto' ? 'h' . min($start_depth, 6) : $heading_tag;
        $output .= "<{$current_tag}>{$formatted_key}</{$current_tag}>" . PHP_EOL . $formatted_value . PHP_EOL;
    }

    if ($return_as_string) {
        return trim($output);
    }

    echo trim($output);

    return null;
}

/**
 * Prints an array with <h2> tags based on the array keys.
 *
 * This function is a wrapper for print_array_with_headings() specifically for h2 tags.
 *
 * @param array $array                The input array to format.
 * @param bool  $use_htmlspecialchars Whether to apply htmlspecialchars() on keys and values.
 * @param bool  $return_as_string     Whether to return the formatted string instead of echoing it.
 *
 * @return ?string The formatted HTML string if $return_as_string is true, otherwise null.
 */
function print_h2(
    array $array,
    bool $use_htmlspecialchars = false,
    bool $return_as_string = false
): ?string {
    return print_array_with_headings($array, $use_htmlspecialchars, $return_as_string, 'h2');
}

/**
 * Print an array with ascending number <hX> header tags.
 * This is a wrapper for print_array_with_headings() using the 'auto' option.
 *
 * @param array $arr              The input array to format.
 * @param bool  $htmlspecialchars Whether to apply htmlspecialchars() on keys and values (default: false).
 * @param bool  $as_string        Whether to return the output as a string instead of echoing (default: false).
 *
 * @return ?string The formatted HTML string if $as_string is true, otherwise null.
 */
function print_hx(array $arr, bool $htmlspecialchars = false, bool $as_string = false): ?string
{
    return print_array_with_headings(
        $arr,
        $htmlspecialchars,
        $as_string,
        'auto'
    );
}

/**
 * Captures var_dump() result and returns it as a string.
 *
 * @link    https://stackoverflow.com/questions/139474/how-can-i-capture-the-result-of-var-dump-to-a-string
 *
 * @param mixed ...$args Variable number of arguments to pass to var_dump.
 *
 * @return string The captured var_dump output as a string.
 *
 * @see     capture_buffer
 */
function var_dump_string(...$args): string
{
    return capture_buffer('var_dump', false, $args);
}

/**
 * Strips HTML tags from a string and replaces them with whitespace instead of removing them completely.
 *
 * @link    https://stackoverflow.com/questions/12824899/strip-tags-replace-tags-by-space-rather-than-deleting-them
 *
 * @param string            $string         The input string to process.
 * @param array|string|null $allowable_tags The allowed HTML tags that will not be stripped.
 *
 * @return string The processed string with tags replaced by whitespace.
 */
function strip_tags_with_whitespace(string $string, array|string|null $allowable_tags = null): string
{
    // Add space before tags to ensure tags aren't concatenated with words
    $string = str_replace('<', ' <', $string);

    $string = strip_tags($string, $allowable_tags);
    $string = str_replace('  ', ' ', $string);

    return trim($string);
}

/**
 * Removes all non-alphabetical characters from a string.
 *
 * By default, the function only keeps ASCII alphabetic characters (A-Z, a-z).
 * If the $allow_unicode parameter is set to true, the function will preserve
 * alphabetic characters from any language (using the \p{L} Unicode property).
 *
 * @param string $string        The input string to process.
 * @param bool   $allow_unicode Whether to allow Unicode alphabetic characters (default: false).
 * @param string $replacement   The replacement string for non-alphabetic characters (default: '').
 *
 * @return string The string with only alphabetic characters remaining.
 *
 * @example
 *     $string = "This is a string with multiple languages: 12345 中文, русский, English!";
 *     echo Helper\strip_non_alpha($string) . PHP_EOL;
 *     echo Helper\strip_non_alpha($string, true);
 *     // Output:
 *     ThisisastringwithmultiplelanguagesEnglish
 *     Thisisastringwithmultiplelanguages中文русскийEnglish
 */
function strip_non_alpha(string $string, bool $allow_unicode = false, string $replacement = ''): string
{
    // If Unicode alphabetic characters are allowed, use \p{L} to match all alphabetic characters
    if ($allow_unicode) {
        return preg_replace('/[^\p{L}]/u', $replacement, $string);
    }

    // Default: strip everything but ASCII alphabetic characters (A-Z, a-z)
    return preg_replace('/[^a-zA-Z]/', $replacement, $string);
}

/**
 * Removes all non-digit characters from a string.
 *
 * By default, the function only keeps English digits (0-9).
 * If the $allow_unicode parameter is set to true, the function will allow
 * digits from other languages (Unicode numeric characters).
 *
 * @param string $string        The input string to process.
 * @param bool   $allow_unicode Whether to allow Unicode digits (default: false).
 * @param string $replacement   The replacement string for non-digit characters (default: '').
 *
 * @return string The string with only digits remaining.
 *
 * @example
 *     $string = "Phone: (123) 456-7890";
 *     echo Helper\strip_non_digit($string);
 *     // Output:
 *     1234567890
 * @example
 *     $string = "Phone: (123) ٤٥٦-٧٨٩٠";  // Includes Arabic numerals
 *     echo Helper\strip_non_digit($string, true);
 *     // Output:
 *     123٤٥٦٧٨٩٠
 */
function strip_non_digit(string $string, bool $allow_unicode = false, string $replacement = ''): string
{
    // If Unicode digits are allowed, use \p{N} to match all Unicode numeric characters
    if ($allow_unicode) {
        return preg_replace('/[^\p{N}]/u', $replacement, $string);
    }

    // Default: strip everything but ASCII digits (0-9)
    return preg_replace('/\D/', $replacement, $string);
}

/**
 * Removes all non-alphanumeric characters from a string.
 *
 * By default, the function only keeps ASCII alphanumeric characters (A-Z, a-z, 0-9).
 * If the $allow_unicode parameter is set to true, the function will preserve
 * alphanumeric characters from any language (using the \p{L} and \p{N} Unicode properties).
 *
 * @param string $string        The input string to process.
 * @param bool   $allow_unicode Whether to allow Unicode alphanumeric characters (default: false).
 * @param string $replacement   The replacement string for non-alphanumeric characters (default: '').
 *
 * @return string The string with only alphanumeric characters remaining.
 *
 * @example
 *     $string = "This is a string with numbers: 12345 中文, русский, English!";
 *     echo Helper\strip_non_alnum($string) . PHP_EOL;
 *     echo Helper\strip_non_alnum($string, true);
 *     // Output:
 *     Thisisastringwithnumbers12345English
 *     Thisisastringwithnumbers12345中文русскийEnglish
 */
function strip_non_alnum(string $string, bool $allow_unicode = false, string $replacement = ''): string
{
    // If Unicode alphanumeric characters are allowed, use \p{L} for letters and \p{N} for numbers
    if ($allow_unicode) {
        return preg_replace('/[^\p{L}\p{N}]/u', $replacement, $string);
    }

    // Default: strip everything but ASCII alphanumeric characters (A-Z, a-z, 0-9)
    return preg_replace('/[^a-zA-Z0-9]/', $replacement, $string);
}

/**
 * Generates a sed command to replace a string in a file.
 *
 * @param string $search_string  The string to search for.
 * @param string $replace_string The string to replace with.
 * @param string $filename       The file where the replacement will occur.
 * @param string $delimiter      The delimiter used for the sed command (default: '/').
 *
 * @throws \InvalidArgumentException If any required parameter is empty or the delimiter is invalid.
 *
 * @return string The generated sed command.
 */
function generate_sed(string $search_string, string $replace_string, string $filename, string $delimiter = '/'): string
{
    // Combined empty checks for critical inputs
    if (empty($search_string) || empty($replace_string) || empty($filename)) {
        throw new \InvalidArgumentException('Search string, replace string, and filename must not be empty.');
    }

    // Validate delimiter: must be a single character
    if (strlen($delimiter) !== 1) {
        throw new \InvalidArgumentException('Delimiter must be a single character.');
    }

    // Sanitize and escape special characters in the search and replace strings
    $search_string_escaped  = addcslashes($search_string, '$.*[]\\^' . $delimiter);
    $replace_string_escaped = addcslashes($replace_string, '$.*[]\\^' . $delimiter);

    // Escape the filename to prevent shell injection
    $escaped_filename = escapeshellarg($filename);

    // Detect OS to determine quote style (Windows uses double quotes, Unix-like systems use single quotes)
    $quote_style = (PHP_OS_FAMILY === 'Windows') ? '"' : "'";

    // Construct and return the sed command
    // 'g' for global replacement
    return sprintf(
        "sed -i 's%s%s%s%s%s%s' %s%s%s",
        $delimiter,
        $search_string_escaped,
        $delimiter,
        $replace_string_escaped,
        $delimiter,
        'g',
        $quote_style,
        $filename,
        $quote_style
    );
}

/**
 * Determines if the given value is a negative integer.
 * Accepts only integer types, otherwise returns false.
 *
 * @param mixed $value The value to check.
 *
 * @return bool True if the value is a negative integer, otherwise false.
 */
function is_int_negative(mixed $value): bool
{
    return is_int($value) && $value < 0;
}

/**
 * Determines if the given value is a positive integer.
 * Accepts only integer types and, optionally, zero.
 *
 * @param mixed $value       The value to validate.
 * @param bool  $accept_zero If true, zero is accepted as valid.
 *
 * @return bool True if the value is a positive integer or zero (if accepted), otherwise false.
 */
function is_int_positive(mixed $value, bool $accept_zero = false): bool
{
    return is_int($value) && ($value > 0 || ($accept_zero && $value === 0));
}

/**
 * Checks if a value is numeric or is a decimal point.
 * Accepts numbers and the string '.' as valid inputs.
 *
 * @param mixed $value The value to check.
 *
 * @return bool True if the value is numeric or a decimal point, otherwise false.
 */
function is_numeric_decimal(mixed $value): bool
{
    return is_numeric($value) || $value === '.';
}

/**
 * Determines if the given value is a whole number.
 * Excludes boolean values to prevent unexpected results.
 *
 * @param mixed $number The value to check.
 *
 * @return bool True if the value is a whole number, otherwise false.
 */
function is_whole_number(mixed $number): bool
{
    if (is_bool($number)) {
        return false;
    }

    $floatValue = filter_var($number, FILTER_VALIDATE_FLOAT);

    return $floatValue !== false && floor($floatValue) === $floatValue;
}

/**
 * Multibyte String Pad
 *
 * Functionally, the equivalent of the standard str_pad function, but is capable of successfully padding multibyte strings.
 *
 * By default, this function uses mb_strwidth() for length calculations, which is more appropriate for visual alignment
 * of strings containing multibyte characters. If exact byte-length padding is needed, set $use_width to false.
 *
 * @link    https://stackoverflow.com/questions/14773072/php-str-pad-not-working-with-unicode-characters
 *
 * @param string $input      The string to be padded.
 * @param int    $length     The length of the resultant padded string.
 * @param string $pad_string The string to use as padding. Defaults to space.
 * @param int    $pad_type   The type of padding. Defaults to STR_PAD_RIGHT.
 * @param string $encoding   The encoding to use, defaults to UTF-8.
 * @param bool   $use_width  Whether to use mb_strwidth() instead of mb_strlen(). Defaults to true.
 *                           Set to true for visual alignment, false for byte-length padding.
 *
 * @return string A padded multibyte string.
 */
function helper_mb_str_pad($input, $length, $pad_string = ' ', $pad_type = STR_PAD_RIGHT, $encoding = 'UTF-8', $use_width = true)
{
    $result = $input;

    $string_length_func = $use_width ? 'mb_strwidth' : 'mb_strlen';

    if (($pad_stringRequired = $length - $string_length_func($input, $encoding)) > 0) {
        switch ($pad_type) {
            case STR_PAD_LEFT:
                $result = mb_substr(str_repeat($pad_string, $pad_stringRequired), 0, $pad_stringRequired, $encoding) .
                    $input;

                break;
            case STR_PAD_RIGHT:
                $result = $input .
                    mb_substr(str_repeat($pad_string, $pad_stringRequired), 0, $pad_stringRequired, $encoding);

                break;
            case STR_PAD_BOTH:
                $leftPaddingLength  = (int)floor($pad_stringRequired / 2);
                $rightPaddingLength = (int)($pad_stringRequired - $leftPaddingLength);
                $result             = mb_substr(str_repeat($pad_string, $leftPaddingLength), 0, $leftPaddingLength, $encoding) .
                    $input .
                    mb_substr(str_repeat($pad_string, $rightPaddingLength), 0, $rightPaddingLength, $encoding);

                break;
        }
    }

    return $result;
}

/**
 * Validates whether a string contains only Latin characters and optionally other elements.
 *
 * This function checks if the input string consists of characters from the Latin script,
 * and optionally includes numbers, spaces, and punctuation. Empty strings return true.
 *
 * @link    https://stackoverflow.com/questions/2499474/how-can-i-test-if-an-input-field-contains-foreign-characters
 * @link    https://stackoverflow.com/questions/70533579/php-check-for-characters-in-the-latin-script-plus-spaces-and-numbers
 * @link    https://www.regular-expressions.info/unicode.html
 *
 * @param string $input             The input string to be validated.
 * @param bool   $allow_numbers     Whether to allow numbers (0-9). Default is true.
 * @param bool   $allow_spaces      Whether to allow whitespace characters. Default is true.
 * @param bool   $allow_punctuation Whether to allow punctuation. Default is true.
 *
 * @return bool Returns true if the string is empty or contains only allowed characters, false otherwise.
 */
function string_is_latin(
    string $input,
    bool $allow_numbers = true,
    bool $allow_spaces = true,
    bool $allow_punctuation = true
): bool {
    // Empty strings are considered valid
    if ($input === '') {
        return true;
    }

    $pattern = '/^[\p{Latin}';
    if ($allow_numbers) {
        $pattern .= '\d';
    }
    if ($allow_spaces) {
        $pattern .= '\s';
    }
    if ($allow_punctuation) {
        $pattern .= '\p{P}';
    }
    $pattern .= ']+$/u';

    return (bool) preg_match($pattern, $input);
}

/**
 * Validates whether a string contains only characters from Unicode blocks up to and including Latin Extended-B.
 *
 * This function considers characters with Unicode code points from 0 to 591 (U+0000 to U+024F) as valid.
 * This range includes ASCII, Latin-1 Supplement, Latin Extended-A, and Latin Extended-B.
 * Empty strings are considered valid.
 *
 * @link    https://en.wikipedia.org/wiki/List_of_Unicode_characters
 *
 * @param string $input The input string to validate.
 *
 * @return bool Returns true if the string is empty or contains only characters with code points up to U+024F, false otherwise.
 */
function string_is_latin_extended_b(string $input): bool
{
    return preg_match('/^[\x{0000}-\x{024F}]*$/u', $input) === 1;
}

/**
 * Validates whether a string contains only ASCII characters.
 *
 * Optionally ignores the characters £, ±, §, and €, which are not ASCII,
 * but may be considered valid for certain use cases.
 *
 * @param string $input       The input string to validate.
 * @param bool   $allow_extra Whether to allow extra non-ASCII characters (£, ±, §, €). Default is false.
 *
 * @return bool Returns true if the string contains only ASCII characters (and optionally the extra characters), false otherwise.
 */
function string_is_ascii(string $input, bool $allow_extra = false): bool
{
    if ($allow_extra) {
        $extra_chars = ['£', '±', '§', '€'];
        $input       = mb_ereg_replace('[' . implode('', $extra_chars) . ']', '', $input);
    }

    return mb_check_encoding($input, 'ASCII');
}

/**
 * Validates whether a string contains only characters typically found on English keyboards.
 *
 * This function checks if the string contains only ASCII printable characters,
 * and additional characters £, ±, §. The € character is optionally included.
 * Newline and tab characters are optionally allowed based on the $allow_whitespace parameter.
 *
 * @link    https://stackoverflow.com/questions/4619603/php-validate-string-characters-are-uk-or-us-keyboard-characters
 *
 * @param string $input            The input string to validate.
 * @param bool   $include_euro     Whether to include the € character as valid. Default is true.
 * @param bool   $allow_whitespace Whether to allow newline and tab characters. Default is true.
 *
 * @return bool Returns true if the string contains only allowed characters, false otherwise.
 */
function string_is_english_keyboard(string $input, bool $include_euro = true, bool $allow_whitespace = true): bool
{
    $pattern = '[';
    // All printable ASCII characters including space
    $pattern .= ' -~';
    if ($allow_whitespace) {
        $pattern .= "\n\t";
    }
    $pattern .= '£±§';
    if ($include_euro) {
        $pattern .= '€';
    }
    $pattern .= ']*';

    // The /D modifier ensures $ matches only at the end of the string, not before a newline.
    // This is particularly important when $allow_whitespace is false, as without /D,
    // strings ending with a newline were incorrectly considered valid even when whitespace
    // was not allowed, because $ would match before the final newline.
    // The /u modifier enables proper UTF-8 handling for Unicode characters like £, ±, §, €.
    return (bool) preg_match('/^' . $pattern . '$/Du', $input);
}

/**
 * Converts a boolean value into a string with optional padding.
 *
 * @param bool   $boolean_value The boolean value to convert.
 * @param string $padding       The optional padding to apply before and after the result.
 *
 * @return string The boolean as a string with the optional padding.
 */
function bool_to_string(bool $boolean_value, string $padding = ''): string
{
    $result = $boolean_value ? 'true' : 'false';

    return $padding . $result . $padding;
}

/**
 * Converts a mixed value into a string representation, optionally adding quotes and appending its original type.
 *
 * @param mixed $value        The value to convert (string, bool, int, float, array, or other).
 * @param bool  $quote_string If true, wraps strings in single quotes.
 * @param bool  $append_type  If true, appends the original type in parentheses.
 *
 * @return string The string representation of the value.
 *
 * @see     bool_to_string
 * @see     var_dump_string
 */
function stringval(mixed $value, bool $quote_string = false, bool $append_type = false): string
{
    if (is_bool($value)) {
        $result = bool_to_string($value);
    } elseif (is_int($value) || is_float($value)) {
        $result = strval($value);
    } elseif (is_string($value)) {
        $result = $quote_string ? "'" . $value . "'" : $value;
    } elseif (is_array($value)) {
        $result = print_r($value, true);
    } elseif (is_object($value) && method_exists($value, '__toString')) {
        return $value->__toString();
    } else {
        $result = var_dump_string($value);
    }

    if ($append_type) {
        $result .= ' (' . gettype($value) . ')';
    }

    return $result;
}

/**
 * Explodes a string into an array and trims each value.
 * Returns an empty array if the string is null or has length 0.
 *
 * @param string  $separator    The delimiter used to explode the string.
 * @param ?string $input_string The input string to be exploded and trimmed.
 * @param int     $limit        The limit on the number of exploded elements.
 *
 * @return array The exploded and trimmed array.
 */
function explode_trim(string $separator, ?string $input_string, int $limit = PHP_INT_MAX): array
{
    if ($input_string === null || $input_string === '') {
        return [];
    }

    return array_map('trim', explode($separator, $input_string, $limit));
}

/**
 * Removes characters (punctuation or other Unicode properties) from the beginning and end of a string.
 *
 * By default, it removes punctuation characters based on the Unicode property \p{P}, but other Unicode
 * properties can be specified using the $unicode_property parameter.
 *
 * @link    https://stackoverflow.com/questions/8283539/php-preg-replace-remove-punctuation-from-beginning-and-end-of-string
 *
 * @param string $string           The input string from which characters will be stripped.
 * @param string $unicode_property The Unicode property to use for matching characters (e.g., 'P' for punctuation).
 *                                 Defaults to 'P' (punctuation).
 *
 * @throws \InvalidArgumentException If an unsupported or dangerous Unicode property is passed.
 *
 * @return string The modified string with characters matching the Unicode property removed from boundaries.
 */
function strip_boundary_characters(string $string, string $unicode_property = 'P'): string
{
    // Use \s for whitespace to cover all cases (spaces, tabs, newlines)
    if ($unicode_property === 'Z') {
        // Strip all whitespace at the start and end
        return preg_replace('/^\s+|\s+\z/u', '', $string);
    }

    // Escape backslashes for the interpolated Unicode property
    $escaped_unicode_property = preg_quote($unicode_property, '/');

    // Default behavior for other Unicode properties, ensuring backslashes are handled correctly
    return preg_replace("/^\\p{{$escaped_unicode_property}}+|\\p{{$escaped_unicode_property}}+\\z/u", '', $string);
}

/**
 * Removes punctuation from the beginning and end of a string.
 *
 * This is a wrapper function for strip_boundary_characters(), where the default Unicode property is 'P' (punctuation).
 *
 * @param string $string The input string from which punctuation will be stripped.
 *
 * @return string The modified string with punctuation removed from boundaries.
 */
function strip_boundary_punctuation(string $string): string
{
    return strip_boundary_characters($string, 'P');
}

/**
 * Splits a string by any whitespace character, with an option to include non-breaking spaces.
 *
 * @link    https://www.tutorialkart.com/php/php-split-string-by-any-whitespace-character/
 *
 * @param string $input                   The input string to split.
 * @param bool   $includeNonBreakingSpace Whether to include non-breaking spaces in the splitting.
 *
 * @return array The array of split string components, with empty elements removed.
 */
function preg_split_whitespace(string $input, bool $includeNonBreakingSpace = true): array
{
    // Choose regex pattern: include all Unicode whitespace + non-breaking space, or ASCII whitespace only
    $pattern = $includeNonBreakingSpace ? '/[\s\x{00A0}]+/u' : '/\s+/';

    // The -1 argument is the default for "no limit" on splits, added explicitly to pass PREG_SPLIT_NO_EMPTY.
    // PREG_SPLIT_NO_EMPTY removes empty elements from the resulting array, like array_filter().
    return preg_split($pattern, trim($input), -1, PREG_SPLIT_NO_EMPTY);
}

/**
 * Splits a string by word boundaries, potentially splitting contractions.
 *
 * This function splits a string at word boundaries, which includes splitting contractions.
 * For a more natural word splitting that preserves contractions, consider using
 * preg_split_whitespace() combined with strip_boundary_punctuation().
 *
 * @link    https://www.tutorialkart.com/php/php-split-string-by-any-whitespace-character/
 *
 * @param string $input The input string to be split
 *
 * @return array An array of non-empty words
 */
function preg_split_word_boundary(string $input): array
{
    $words = preg_split('/\b/u', $input, -1, PREG_SPLIT_NO_EMPTY);

    return array_values(array_filter(array_map('trim', $words)));
}

/**
 * Convert a string to an array of words, with boundary punctuation removed
 *
 * @param string $string
 * @param bool   $unique Return only unique words. Default false.
 *
 * @return array
 *
 * @see     preg_split_whitespace
 * @see     strip_boundary_punctuation
 */
function string_to_words(string $string, bool $unique = false): array
{
    //$array = explode(' ', $string);
    $array     = preg_split_whitespace($string);
    $new_array = [];
    foreach ($array as $word) {
        $new_array[] = strip_boundary_punctuation($word);
    }
    if ($unique) {
        $new_array = array_unique($new_array);
    }
    // Remove empty elements and re-index keys
    $new_array = array_filter($new_array);
    $new_array = array_values($new_array);

    return $new_array;
}

/**
 * Retrieves a predefined list of stop words.
 *
 * This function returns an array of common English stop words. Stop words are
 * frequent words that are often filtered out in text processing tasks because
 * they typically don't contribute much to the overall meaning of a sentence.
 *
 * 's' and 't' were removed from the list of stop words.
 *
 * @link    https://github.com/rap2hpoutre/remove-stop-words/blob/master/src/remove_stop_words.php
 * @link    https://www.semrush.com/blog/seo-stop-words/
 * @link    https://blog.hubspot.com/marketing/stop-words-seo
 * @link    https://github.com/rap2hpoutre/remove-stop-words/blob/master/src/locale/en.php
 *
 * @return array An array of stop words.
 */
function get_stop_words(): array
{
    $stop_words = ['a', 'about', 'above', 'actually', 'after', 'again', 'against', 'all', 'almost', 'also', 'although', 'always', 'am', 'an', 'and', 'any', 'are', 'as', 'at', 'be', 'became', 'because', 'become', 'been', 'before', 'being', 'below', 'between', 'both', 'but', 'by', 'can', 'could', 'did', 'do', 'does', 'doing', 'don', 'down', 'during', 'each', 'either', 'else', 'few', 'for', 'from', 'further', 'had', 'has', 'have', 'having', 'he', "he'd", "he'll", "he's", 'hence', 'her', 'here', "here's", 'hers', 'herself', 'him', 'himself', 'his', 'how', "how's", 'i', "i'd", "i'll", "i'm", "i've", 'if', 'in', 'into', 'is', 'it', "it's", 'its', 'itself', 'just', "let's", 'may', 'maybe', 'me', 'might', 'mine', 'more', 'most', 'must', 'my', 'myself', 'neither', 'no', 'nor', 'not', 'now', 'of', 'off', 'oh', 'ok', 'on', 'once', 'only', 'or', 'other', 'ought', 'our', 'ours', 'ourselves', 'out', 'over', 'own', 'same', 'she', "she'd", "she'll", "she's", 'should', 'so', 'some', 'such', 'than', 'that', "that's", 'the', 'their', 'theirs', 'them', 'themselves', 'then', 'there', "there's", 'these', 'they', "they'd", "they'll", "they're", "they've", 'this', 'those', 'through', 'to', 'too', 'under', 'until', 'up', 'very', 'was', 'we', "we'd", "we'll", "we're", "we've", 'were', 'what', "what's", 'when', "when's", 'whenever', 'where', "where's", 'whereas', 'wherever', 'whether', 'which', 'while', 'who', "who's", 'whoever', 'whom', 'whose', 'why', "why's", 'will', 'with', 'within', 'without', 'would', 'yes', 'yet', 'you', "you'd", "you'll", "you're", "you've", 'your', 'yours', 'yourself', 'yourselves'];

    return $stop_words;
}

/**
 * Removes stop words from a string and trims extra whitespace.
 *
 * Does not remove word boundary punctuation. Consider filter_stop_words() instead.
 *
 * @param string $words The input string to be cleaned.
 *
 * @return string The string without stop words and extra whitespace.
 */
function remove_stop_words(string $words): string
{
    $stop_words          = get_stop_words();
    $stop_words_patterns = [];

    // Convert stop words to regex patterns
    foreach ($stop_words as $key => $word) {
        $stop_words_patterns[$key] = '/\b' . preg_quote($word, '/') . '\b/iu';
    }

    // Remove stop words from the input string
    $cleaned_text = preg_replace($stop_words_patterns, '', $words);

    // Remove extra whitespace
    $cleaned_text = trim(preg_replace('/\s+/', ' ', $cleaned_text));

    // Return the cleaned string
    return $cleaned_text;
}

/**
 * Takes a string, removes stop words, and converts it into an array of words.
 *
 * @param string $string The input string to be processed.
 *
 * @return array The array of words without stop words.
 */
function remove_stop_words_to_array(string $string): array
{
    // If the input string is empty or consists only of whitespace, return an empty array
    if (empty(trim($string))) {
        return [];
    }

    // Remove stop words from the input string
    $modified = remove_stop_words($string);

    // Convert the modified string into an array of words
    $array = string_to_words($modified);

    // Return the resulting array of words
    return $array;
}

/**
 * Filters out stop words from a given input string.
 *
 * @param string $input  The input string to process.
 * @param bool   $unique Whether to return only unique words. Default is false.
 *
 * @return array An array of words with stop words removed.
 *
 * @see     string_to_words
 */
function filter_stop_words(string $input, bool $unique = false): array
{
    $words      = string_to_words($input, $unique);
    $stop_words = array_flip(array_map('strtolower', get_stop_words()));

    // Use array_values() to re-index the array after filtering
    return array_values(array_filter($words, function ($word) use ($stop_words) {
        return $word !== '' && !isset($stop_words[strtolower($word)]);
    }));
}

/**
 * Increments the suffix of a string based on an array of existing strings.
 *
 * This function is similar to WordPress's post name incrementing behavior.
 * It adds a numeric suffix to the input string if it already exists in the
 * given array of names.
 *
 * @param string $name                   The original string to increment.
 * @param array  $names                  Array of existing names to check against.
 * @param bool   $modify_post_name_array Whether to add the new name to the array. Default is false.
 *
 * @throws \InvalidArgumentException
 *
 * @return string The incremented string.
 *
 * @example
 * $post_name_array = [
 *     'hello-world', 'hello-world-2', 'hello-world-3', 'test', 'test-2',
 *     'example', 'test-post', 'a-post', 'a-post-2', 'Hello world'
 * ];
 *
 * echo Helper\get_post_name_incremented('hello-world', $post_name_array) . PHP_EOL;
 * echo Helper\get_post_name_incremented('Hello world', $post_name_array) . PHP_EOL;
 * echo Helper\get_post_name_incremented('Hello world', $post_name_array, true) . PHP_EOL;
 * echo Helper\get_post_name_incremented('Hello world', $post_name_array, true) . PHP_EOL;
 * echo Helper\get_post_name_incremented('Hello world', $post_name_array) . PHP_EOL;
 * echo Helper\get_post_name_incremented('Hello world', $post_name_array) . PHP_EOL;
 *
 * // Output:
 * // hello-world-4
 * // Hello world-2
 * // Hello world-2
 * // Hello world-3
 * // Hello world-4
 * // Hello world-4
 *
 * // Note: The third and fourth calls modify $post_name_array by adding
 * // 'Hello world-2' and 'Hello world-3' respectively.
 */
function get_post_name_incremented(string $name, array &$names, bool $modify_post_name_array = false): string
{
    if (empty($name)) {
        throw new \InvalidArgumentException('$name cannot be empty.');
    }

    $incremented_name = $name;
    $suffix           = 2;

    while (in_array($incremented_name, $names, true)) {
        $incremented_name = $name . '-' . $suffix;
        $suffix++;
    }

    if ($modify_post_name_array) {
        $names[] = $incremented_name;
    }

    return $incremented_name;
}

/**
 * Wraps a string and returns only the first line.
 *
 * This function uses PHP's wordwrap to wrap the input string to a specified
 * width and returns only the first line of the result. The returned string
 * ends on a complete word rather than in the middle of a word, unless
 * $cut_long_words is set to true.
 *
 * @link    https://stackoverflow.com/questions/1233290/making-sure-php-substr-finishes-on-a-word-not-a-character
 *
 * @param string $string         The input string to be wrapped.
 * @param int    $width          The width at which to wrap the string.
 * @param string $break          The line break character. Default is "\n".
 * @param bool   $cut_long_words Whether to cut words longer than $width. Default is false.
 *
 * @throws \InvalidArgumentException If $width is less than or equal to 0.
 *
 * @return string The first line of the wrapped string.
 */
function wordwrap_first_line(string $string, int $width, string $break = "\n", bool $cut_long_words = false): string
{
    if ($width <= 0) {
        throw new \InvalidArgumentException('Width must be greater than 0.');
    }

    $wrapped = wordwrap($string, $width, $break, $cut_long_words);
    $array   = explode($break, $wrapped);

    return $array[0] ?? '';
}

/**
 * Condenses var_export() output into a single line.
 *
 * This function removes extra whitespace from the var_export() output and
 * optionally returns or prints the result. Note that the output is not
 * sanitized and should not be directly echoed in an HTML context without
 * proper escaping.
 *
 * @param mixed $variable The variable to export.
 * @param bool  $return   Whether to return the output. Default is false.
 *
 * @return ?string The condensed var_export output if $return is true; otherwise, null.
 */
function var_export_inline(mixed $variable, bool $return = false): ?string
{
    $export    = trim(var_export($variable, true));
    $condensed = preg_replace('/\s+/', ' ', $export);

    if ($return) {
        return $condensed;
    }

    echo $condensed;

    return null;
}

/**
 * Converts a string representation of bytes to its numerical value.
 *
 * This function takes a string or numeric value representing a size in bytes,
 * optionally with a unit (B, KB, MB, GB, etc.), and converts it to its
 * numerical value in bytes. The function returns the value as an integer
 * whenever possible, unless the value requires floating-point precision.
 *
 * @param mixed $value      The value to convert (e.g., '2G', '1024M', 1048576).
 * @param bool  $prefer_int Whether to return an integer when possible. Default is true.
 *
 * @throws \InvalidArgumentException If the input is invalid or if the unit is unrecognized.
 *
 * @return int|float The value in bytes as an integer or float.
 */
function convert_to_bytes(mixed $value, bool $prefer_int = true): int|float
{
    // If the input is numeric, return it directly as an int or float
    if (is_numeric($value)) {
        return $prefer_int && $value == (int)$value ? (int)$value : (float)$value;
    }

    // Trim and validate the input string for units
    $value = trim($value);
    if (!preg_match('/^(-?\d+(?:\.\d+)?)\s*([BKMGTPEZY]?)B?$/i', $value, $matches)) {
        throw new \InvalidArgumentException("Invalid byte string: $value");
    }

    // Extract the numeric part and the unit, defaulting to bytes if no unit is provided
    $num  = (float)$matches[1];
    $unit = strtoupper($matches[2] ?: 'B');

    // Map units to their corresponding byte values
    $unit_multipliers = [
        'B' => 1,
        'K' => 1024,
        'M' => 1024 ** 2,
        'G' => 1024 ** 3,
        'T' => 1024 ** 4,
        'P' => 1024 ** 5,
        'E' => 1024 ** 6,
        'Z' => 1024 ** 7,
        'Y' => 1024 ** 8,
    ];

    // If the unit is not recognized, throw an exception
    if (!isset($unit_multipliers[$unit])) {
        throw new \InvalidArgumentException("Unrecognized unit: $unit");
    }

    // Calculate the byte value by multiplying the number by the appropriate unit multiplier
    $bytes = $num * $unit_multipliers[$unit];

    // If prefer_int is true, return the result as an integer if it is a whole number
    if ($prefer_int && $bytes == (int)$bytes && abs($bytes) <= PHP_INT_MAX) {
        return (int)$bytes;
    }

    return $bytes;
}

/**
 * Sanitizes a CSS class name according to CSS specifications.
 *
 * This function takes an input string and returns a sanitized CSS class name
 * that complies with the CSS Syntax Module Level 3 specifications. The rules
 * applied to ensure compliance include:
 *
 * 1. Class names cannot start with a digit. If the input starts with a digit,
 *    the function prepends 'cls_' to the name.
 * 2. Class names cannot start with a hyphen followed by a digit. If this pattern
 *    is detected, 'cls_' is prepended to the name.
 * 3. Any invalid characters (such as symbols, punctuation marks, spaces, etc.)
 *    are removed entirely. Valid characters include:
 *    - Letters (a-z, A-Z)
 *    - Numbers (0-9)
 *    - Hyphens (-) and underscores (_)
 *    - Unicode characters U+00A0 and higher
 * 4. Class names that consist entirely of invalid characters (e.g., only hyphens,
 *    underscores, or empty strings) will be replaced with 'cls_'.
 * 5. Leading and trailing whitespace is trimmed, and any internal whitespace is
 *    removed to ensure a valid CSS class name.
 *
 * Edge cases:
 * - If the class name is empty or becomes empty after sanitization, the result
 *   is 'cls_'.
 * - If the class name contains only invalid sequences, such as consecutive hyphens
 *   or underscores, the name is replaced with 'cls_'.
 *
 * @param string $class_name The input class name to sanitize.
 *
 * @return string The sanitized CSS class name.
 */
function sanitize_css_class_name(string $class_name): string
{
    // Ensure the string is in UTF-8 encoding
    $class_name = mb_convert_encoding($class_name, 'UTF-8', 'UTF-8');

    // Remove leading and trailing whitespace
    $class_name = trim($class_name);

    // Remove any whitespace inside the string
    $class_name = preg_replace('/\s+/', '', $class_name);

    // Remove invalid characters (letters, numbers, hyphens, underscores, Unicode characters U+00A0 and higher are allowed)
    $class_name = preg_replace('/[^\p{L}\p{N}\-_]/u', '', $class_name);

    // Check if the class name starts with a digit, hyphen(s), or invalid pattern
    if (preg_match('/^(?:[0-9]|-[^a-zA-Z_]|--[^a-zA-Z_])/u', $class_name)) {
        $class_name = 'cls_' . ltrim($class_name, '-');
    }

    // Check if the class name is invalid (only hyphens, underscores, or empty)
    if ($class_name === '' || $class_name === '-' || preg_match('/^[-_]+$/', $class_name)) {
        $class_name = 'cls_';
    }

    return $class_name;
}

/**
 * Sanitizes a CSS ID name according to CSS specifications.
 *
 * This function takes an input string and returns a sanitized CSS ID name
 * that complies with the CSS Syntax Module Level 3 specifications. If the ID
 * name starts with an invalid pattern, 'id_' is prepended to the name.
 * Invalid characters are removed, not replaced with underscores.
 *
 * Rules for sanitizing ID names:
 * - ID names cannot start with a digit.
 * - ID names cannot start with a hyphen followed by a digit or multiple hyphens.
 * - Allowed characters are: letters, numbers, hyphens (-), underscores (_), and Unicode characters U+00A0 and higher.
 * - If the name is empty or consists entirely of invalid characters (e.g., only hyphens or underscores), it is replaced with 'id_'.
 *
 * @param string $id_name The input ID name to sanitize.
 *
 * @return string The sanitized CSS ID name.
 */
function sanitize_css_id_name(string $id_name): string
{
    // Ensure the string is in UTF-8 encoding
    $id_name = mb_convert_encoding($id_name, 'UTF-8', 'UTF-8');

    // Remove leading and trailing whitespace
    $id_name = trim($id_name);

    // Remove any whitespace inside the string
    $id_name = preg_replace('/\s+/', '', $id_name);

    // Remove invalid characters (only letters, numbers, hyphens, underscores, and Unicode U+00A0 and higher allowed)
    $id_name = preg_replace('/[^\p{L}\p{N}\-_]/u', '', $id_name);

    // If the ID starts with multiple hyphens, or hyphen followed by a digit, prefix with 'id_'
    if (preg_match('/^(-{2,}|-[0-9])/u', $id_name)) {
        $id_name = 'id_' . ltrim($id_name, '-');
    }

    // If the ID starts with a digit, also prefix with 'id_'
    if (preg_match('/^[0-9]/u', $id_name)) {
        $id_name = 'id_' . $id_name;
    }

    // Check if the ID name is invalid (only hyphens, underscores, or empty)
    if ($id_name === '' || preg_match('/^[-_]+$/', $id_name)) {
        $id_name = 'id_';
    }

    return $id_name;
}

/**
 * Sanitizes a string to be safely used within an HTML 'style' attribute.
 *
 * This function aggressively removes potentially dangerous content,
 * prioritizing security over preserving all valid CSS. Some valid CSS
 * constructs may be altered or removed in the process.
 *
 * Known limitations:
 * - All URL functions (e.g., url()) are removed for security
 * - CSS functions like calc(), var(), and others are preserved but may be altered
 * - Complex CSS may be simplified or partially removed
 * - CSS hacks or non-standard syntax will be removed
 * - Vendor-specific prefixes are preserved but not validated
 * - Unicode characters outside the basic multilingual plane are removed
 *
 * @param string $style The input CSS string to be sanitized.
 *
 * @return string A sanitized CSS string safe for use in a 'style' attribute.
 */
function sanitize_style_attribute(string $style): string
{
    // Remove control characters and comments
    $style = preg_replace('/[\x00-\x1F\x7F]+|\s*\/\*.*?\*\/\s*/s', '', $style);

    // Remove potentially dangerous content
    $dangerous_patterns = [
        '/expression\s*\([^;]+/is',  // Remove expression()
        '/javascript\s*:/i',         // Remove javascript:
        '/vbscript\s*:/i',           // Remove vbscript:
        '/behavior\s*:/i',           // Remove behavior:
        '/-moz-binding\s*:/i',       // Remove -moz-binding:
        '/@import\s+/i',             // Remove @import
        '/url\s*\([^;]+/i',           // Remove url()
    ];
    $style = preg_replace($dangerous_patterns, '', $style);

    // Allow only valid CSS characters and basic syntax
    // Note: This step removes Unicode characters outside the basic multilingual plane
    $style = preg_replace('/[^a-zA-Z0-9\s:;,.()#%+-]+/', '', $style);

    // Remove any leftover semicolons or colons at the start or end
    $style = trim($style, ':;');

    // Ensure property-value pairs are properly formatted
    $declarations = explode(';', $style);
    $sanitized    = [];
    foreach ($declarations as $declaration) {
        $parts = explode(':', $declaration, 2);
        if (count($parts) == 2) {
            $property = trim($parts[0]);
            $value    = trim($parts[1]);
            if ($property !== '' && $value !== '') {
                $sanitized[] = "$property: $value";
            }
        }
    }

    return implode('; ', $sanitized);
}

/**
 * Displays special characters and escape sequences in a string for debugging purposes.
 *
 * This function converts a string to a format where all special characters, escape sequences,
 * and non-ASCII characters are visible. It:
 * - Escapes all control characters (0x00-0x1F and 0x7F) using \x notation, except for common escape sequences
 * - Escapes all non-ASCII characters (0x80 and above) using \x or \u{} notation
 * - Preserves common escape sequences (\r, \n, \t, \v, \f, \e)
 * - Represents null byte as \x00
 * - Escapes backslashes
 * - Preserves printable ASCII characters (0x20-0x7E, except backslash)
 * - Handles already escaped sequences by adding an extra backslash (except in JSON strings)
 * - Treats JSON input differently, preserving its original escape sequences
 *
 * @param string $input       The input string to process.
 * @param bool   $html_output Whether to format the output for HTML display. Default is false.
 *
 * @throws \InvalidArgumentException If the input is not a valid UTF-8 string.
 *
 * @return string The formatted string with visible special characters and escape sequences.
 */
function show_escape_sequences(string $input, bool $html_output = false): string
{
    if (!mb_check_encoding($input, 'UTF-8')) {
        throw new \InvalidArgumentException('Input must be a valid UTF-8 string.');
    }

    $json_input = json_decode($input);
    $is_json    = (json_last_error() === JSON_ERROR_NONE);

    $result = preg_replace_callback(
        '/\\\\(?:[nrtfv\\\\]|u\{?[0-9a-fA-F]+\}?|x[0-9a-fA-F]{2})|[\x00-\x1F\x7F-\xFF]|[\x{0080}-\x{10FFFF}]/u',
        function ($match) use ($is_json) {
            $char = $match[0];
            if (strlen($char) > 1 && $char[0] === '\\') {
                // Already escaped sequence
                return $is_json ? $char : '\\' . $char;
            }
            $ord = mb_ord($char, 'UTF-8');
            switch ($char) {
                case "\r":
                    return '\r';
                case "\n":
                    return '\n';
                case "\t":
                    return '\t';
                case "\v":
                    return '\v';
                case "\f":
                    return '\f';
                case "\0":
                    return '\x00';
                case "\e":
                    return '\e';
                case '\\':
                    return '\\\\';
                default:
                    if ($ord <= 0x1F || $ord === 0x7F) {
                        return '\\x' . sprintf('%02X', $ord);
                    } elseif ($ord <= 0xFF) {
                        return '\\x' . sprintf('%02X', $ord);
                    } else {
                        return '\\u{' . sprintf('%X', $ord) . '}';
                    }
            }
        },
        $input
    );

    if ($html_output) {
        $result = htmlspecialchars($result, ENT_QUOTES | ENT_SUBSTITUTE | ENT_HTML5);
        $result = '<code>' . str_replace(' ', '&nbsp;', $result) . '</code>';
    }

    return $result;
}

/**
 * Prints a human-readable representation of a variable in a single line.
 *
 * This function is a variation of `print_r()` that removes line breaks,
 * making the output appear on a single line for compact readability.
 *
 * @param mixed $input  The variable to be printed.
 * @param bool  $return If true, the output is returned as a string instead of being printed.
 *
 * @return ?string The single-line output if $return is true, otherwise null.
 */
function print_r_inline($input, bool $return = false): ?string
{
    if ($input === null) {
        $output = 'null';
    } else {
        $output = print_r($input, true);
    }

    $inline_output = preg_replace('/\s+/', ' ', trim($output));

    if ($return) {
        return $inline_output;
    }

    echo $inline_output . PHP_EOL;

    return null;
}

/**
 * Splits a string into lines, handling cross-platform line breaks.
 *
 * @param string $input The input string containing lines of data.
 *
 * @return array The array of lines.
 */
function split_lines(string $input): array
{
    // Trim input to remove leading and trailing whitespace
    $trimmed_input = trim($input);

    return preg_split('/\r\n|\r|\n/', $trimmed_input);
}

/**
 * Pads a string based on the alignment direction, with support for multibyte characters.
 *
 * @param string $cell      The string to be padded.
 * @param int    $width     The width to pad to.
 * @param string $alignment The alignment direction ('left', 'right', 'center').
 * @param bool   $use_width Whether to use mb_strwidth() instead of mb_strlen(). Defaults to true.
 *
 * @throws \InvalidArgumentException If an invalid alignment is provided.
 *
 * @return string The padded string.
 *
 * @see     helper_mb_str_pad
 */
function pad_string(string $cell, int $width, string $alignment, bool $use_width = true): string
{
    $valid_alignments = ['left', 'right', 'center'];
    if (!in_array($alignment, $valid_alignments, true)) {
        throw new \InvalidArgumentException("Invalid alignment: $alignment. Use 'left', 'right', or 'center'.");
    }

    // Map alignment to STR_PAD constants
    if ($alignment === 'left') {
        $pad_type = STR_PAD_RIGHT;
    } elseif ($alignment === 'right') {
        $pad_type = STR_PAD_LEFT;
    } else {
        // Center
        $pad_type = STR_PAD_BOTH;
    }

    // Use helper_mb_str_pad for multibyte-aware padding
    return helper_mb_str_pad($cell, $width, ' ', $pad_type, 'UTF-8', $use_width);
}

/**
 * Calculates the maximum width for each column based on the parsed lines.
 *
 * @param array $parsed_lines The array of parsed lines.
 * @param bool  $use_width    Whether to use mb_strwidth() instead of mb_strlen(). Defaults to true.
 *
 * @throws \InvalidArgumentException If any line is not an array.
 *
 * @return array The array of maximum widths for each column.
 */
function calculate_column_widths(array $parsed_lines, bool $use_width = true): array
{
    $column_widths      = [];
    $string_length_func = $use_width ? 'mb_strwidth' : 'mb_strlen';

    foreach ($parsed_lines as $line) {
        if (!is_array($line)) {
            throw new \InvalidArgumentException('Each line must be an array.');
        }

        foreach ($line as $index => $cell) {
            $cell_length           = $string_length_func((string)$cell, 'UTF-8');
            $column_widths[$index] = max($column_widths[$index] ?? 0, $cell_length);
        }
    }

    return $column_widths;
}

/**
 * Aligns a single line based on the specified column widths and alignment.
 *
 * @param array     $line              The array of cells in the line.
 * @param array     $column_widths     The array of maximum widths for each column.
 * @param string    $default_alignment The default alignment direction ('left', 'right', 'center').
 * @param bool      $left_align_first  Whether to force left-alignment for the first column.
 * @param ?callable $padding_function  Optional custom padding function.
 * @param bool      $use_width         Whether to use mb_strwidth() instead of mb_strlen(). Defaults to true.
 *
 * @throws \InvalidArgumentException If an invalid alignment is provided or invalid padding function.
 *
 * @return string The aligned line as a string.
 */
function align_line(
    array $line,
    array $column_widths,
    string $default_alignment = 'left',
    bool $left_align_first = true,
    ?callable $padding_function = null,
    bool $use_width = true
): string {
    $padding_function ??= __NAMESPACE__ . '\pad_string';
    $valid_alignments = ['left', 'right', 'center'];

    if (!in_array($default_alignment, $valid_alignments, true)) {
        throw new \InvalidArgumentException("Invalid alignment: $default_alignment. Use 'left', 'right', or 'center'.");
    }

    // Check if the provided padding function is callable
    if (!is_callable($padding_function)) {
        throw new \InvalidArgumentException('Invalid padding function provided.');
    }

    // Align each cell in the line
    $aligned_cells = array_map(
        function ($cell, $index) use ($column_widths, $default_alignment, $left_align_first, $padding_function, $use_width) {
            // Determine the alignment for this cell
            // If it's the first cell and $left_align_first is true, use 'left' alignment
            // Otherwise, use the default alignment
            $alignment = ($index === 0 && $left_align_first) ? 'left' : $default_alignment;

            // Pad the cell according to its determined alignment and width
            // The width for this cell is taken from $column_widths using the current index
            return $padding_function($cell, $column_widths[$index], $alignment, $use_width);
        },
        $line,
        array_keys($line)
    );

    // Join the aligned cells with a space separator
    return implode(' ', $aligned_cells);
}

/**
 * Aligns columns in a multi-line CSV or similarly delimited string.
 *
 * This function takes a string containing CSV-like data and aligns its columns for improved readability.
 * While optimized for CSV, it can work with other delimiters by specifying a custom separator.
 *
 * By default, this function uses mb_strwidth() for width calculations, which is more appropriate for visual alignment
 * of strings containing multibyte characters. If exact byte-length alignment is needed, set $use_width to false.
 *
 * @param string $input            The input string containing lines of data.
 * @param string $separator        The delimiter used to separate columns. Default is comma (',').
 * @param string $enclosure        The enclosure character used in the input string. Default is double quotes.
 * @param string $escape           The escape character used in the input string. Default is backslash.
 * @param string $alignment        The default alignment direction ('left', 'right', 'center'). Default is 'left'.
 * @param bool   $left_align_first Whether to left-align the first line and first column. Default is true.
 * @param bool   $use_width        Whether to use mb_strwidth() instead of mb_strlen(). Defaults to true.
 *                                 Set to true for visual alignment, false for byte-length alignment.
 *
 * @throws \InvalidArgumentException If an invalid alignment is provided.
 *
 * @return string The string with aligned columns.
 *
 * @see     split_lines
 * @see     calculate_column_widths
 * @see     align_line
 */
function align_csv_columns(
    string $input,
    string $separator = ',',
    string $enclosure = '"',
    string $escape = '\\',
    string $alignment = 'left',
    bool $left_align_first = true,
    bool $use_width = true
): string {
    // Validate alignment
    $valid_alignments = ['left', 'right', 'center'];
    if (!in_array($alignment, $valid_alignments, true)) {
        throw new \InvalidArgumentException("Invalid alignment: $alignment. Use 'left', 'right', or 'center'.");
    }

    if (empty($input)) {
        return '';
    }

    // Split the input into lines
    $lines = split_lines($input);

    // Parse each line into columns
    $parsed_lines = array_map(fn ($line) => str_getcsv($line, $separator, $enclosure, $escape), $lines);

    // Calculate the maximum width for each column
    $column_widths = calculate_column_widths($parsed_lines, $use_width);

    // Align each line according to the calculated widths and alignment rules
    $aligned_lines = array_map(
        fn ($line, $index) => align_line($line, $column_widths, $alignment, $left_align_first && $index === 0, null, $use_width),
        $parsed_lines,
        array_keys($parsed_lines)
    );

    // Join aligned lines into a single output string
    return implode(PHP_EOL, $aligned_lines);
}

/**
 * Generates a MySQL CLI command for executing a query.
 *
 * @param string    $user            The MySQL username.
 * @param string    $password        The MySQL user's password.
 * @param string    $query           The MySQL query to be executed.
 * @param ?string   $database        Optional. The MySQL database name.
 * @param bool      $escape_shell    Whether to escape shell arguments.
 * @param ?callable $escape_function Optional. Function to escape shell arguments.
 *                                   If null, uses strval() (no escaping).
 *                                   Defaults to escapeshellarg_linux().
 *                                   Note: For Windows, escape_windows_cmd_argument() should be used instead of escapeshellarg_windows()
 *                                   since on Windows escapeshellarg() replaces double quotes with spaces.
 *
 * @throws \InvalidArgumentException If the input parameters are invalid.
 *
 * @return string The generated MySQL CLI command.
 */
function mysql_cli_command(
    string $user,
    string $password,
    string $query,
    ?string $database = null,
    bool $escape_shell = true,
    ?callable $escape_function = null
): string {
    if (empty($user) || empty($password) || empty($query)) {
        throw new \InvalidArgumentException('User, password, and query are required.');
    }

    // Use callable escape function if provided, otherwise default to strval
    if ($escape_shell) {
        // Default to escapeshellarg_linux if no function provided
        // Note that for Windows, escape_windows_cmd_argument() should be used instead of escapeshellarg_windows()
        // since on Windows escapeshellarg() replaces double quotes with spaces.
        $escape_function ??= __NAMESPACE__ . '\escapeshellarg_linux';
    } else {
        $escape_function = 'strval';
    }

    // Build the base command string
    $command = sprintf(
        'mysql -u %s -p%s',
        $escape_function($user),
        $escape_function($password)
    );

    // Add the database part if provided
    if ($database) {
        $command .= sprintf(' -D %s', $escape_function($database));
    }

    // Add the query part
    $command .= sprintf(' -e %s', $escape_function($query));

    return $command;
}

/**
 * Converts a number to its word representation.
 *
 * This function acts as a wrapper to the Numbers class.
 * It uses a static instance of Numbers to convert numbers to words.
 *
 * Can be used instead of NumberFormatter::SPELLOUT.
 *
 * @param int|float $number         The number to convert to words.
 * @param string    $hyphen         String used to join tens and units (default: '-').
 * @param string    $conjunction    String used between words for numbers (default: ' ').
 * @param string    $separator      String used to separate groups of numbers (default: ' ').
 * @param string    $minus          String used for negative numbers (default: 'minus ').
 * @param string    $decimal        String used for decimal points (default: ' point ').
 * @param int|null  $decimal_places Number of decimal places to consider (null for all).
 *
 * @throws \Exception If the number is represented in scientific notation.
 *
 * @return string The number in words.
 */
function number_to_words(
    int|float $number,
    string $hyphen = '-',
    string $conjunction = ' ',
    string $separator = ' ',
    string $minus = 'minus ',
    string $decimal = ' point ',
    ?int $decimal_places = null
): string {
    static $numbers = null;

    if ($numbers === null) {
        $numbers = new Numbers();
    }

    return $numbers->numberToWords($number, $hyphen, $conjunction, $separator, $minus, $decimal, $decimal_places);
}

/**
 * Trim the input if it is a string, otherwise return the input as is.
 *
 * Can be useful since trim() may modify types, for instance trimming a Boolean false
 * gives an empty string instead of the Boolean false value.
 *
 * @param mixed $input The input to trim.
 *
 * @return mixed The trimmed input or the original input if it is not a string.
 */
function trim_if_string(mixed $input): mixed
{
    return is_string($input) ? trim($input) : $input;
}

/**
 * Escapes single quotes for use in sed commands.
 *
 * This function replaces single quotes with the escaped sequence '\''.
 * This is necessary for safely using strings in sed commands.
 *
 * @param string $string The string to escape.
 *
 * @return string The escaped string.
 */
function escape_single_quotes_for_sed(string $string): string
{
    // The backslash is escaped as \\ to represent a literal backslash in the replacement string.
    return str_replace("'", "'\\''", $string);
}

/**
 * Sanitizes a string for use as a MySQL identifier (database name, table name, column name, index name, etc.).
 *
 * MySQL identifier rules:
 * - Maximum length is typically 64 characters
 * - Can contain ASCII letters (a-z, A-Z), numbers (0-9), underscore (_)
 * - Can contain extended Unicode characters (letters and numbers) when $allowExtendedChars is true
 * - While MySQL technically allows $ in identifiers, it's excluded here for portability
 * - Identifiers may begin with a number, but this might cause issues in some contexts
 * - All identifiers are converted to lowercase for maximum portability across systems
 * - A leading underscore is preserved if the original string started with underscore(s)
 * - Trailing underscores are always removed
 * - Multiple consecutive underscores are collapsed into a single underscore
 *
 * @param string $string                          The string to sanitize.
 * @param int    $maxLength                       Maximum length for the identifier (default: 64).
 * @param bool   $allowExtendedChars              Whether to allow extended Unicode characters (default: false).
 * @param bool   $mustStartWithLetterOrUnderscore Whether to force the identifier to start with a letter
 *                                                or underscore (default: false).
 * @param bool   $preserveLeadingUnderscore       Whether to preserve original leading underscores (default: true).
 *
 * @throws \InvalidArgumentException If maxLength is less than 1.
 * @throws \RuntimeException         If the resulting sanitized string is empty.
 *
 * @return string The sanitized MySQL identifier.
 */
function sanitize_mysql_identifier(
    string $string,
    int $maxLength = 64,
    bool $allowExtendedChars = false,
    bool $mustStartWithLetterOrUnderscore = false,
    bool $preserveLeadingUnderscore = true
): string {
    if ($maxLength < 1) {
        throw new \InvalidArgumentException('Maximum length must be greater than 0 characters.');
    }

    // Check if original string starts with underscore
    $hadLeadingUnderscore = str_starts_with($string, '_');

    // Convert to lowercase for consistency
    $sanitized = mb_strtolower($string, 'UTF-8');

    if ($allowExtendedChars) {
        // Allow ASCII and extended characters
        // Replace anything else with underscores
        $sanitized = preg_replace('/[^\p{L}\p{N}_]/u', '_', $sanitized);
    } else {
        // Only allow basic ASCII letters, numbers, and underscores
        $sanitized = preg_replace('/[^a-z0-9_]/', '_', $sanitized);
    }

    // Collapse multiple underscores into a single underscore
    $sanitized = preg_replace('/_+/', '_', $sanitized);

    // Remove all leading and trailing underscores initially
    $sanitized = trim($sanitized, '_');

    // Restore original leading underscore if needed
    if ($preserveLeadingUnderscore && $hadLeadingUnderscore) {
        $sanitized = '_' . $sanitized;
    }

    // If it must start with a letter or underscore and doesn't
    if ($mustStartWithLetterOrUnderscore) {
        if ($allowExtendedChars) {
            // Check if it starts with a letter (including extended) or underscore
            if (!preg_match('/^[\p{L}_]/u', $sanitized)) {
                $sanitized = '_' . $sanitized;
            }
        } else {
            // Check if it starts with an ASCII letter or underscore
            if (!preg_match('/^[a-z_]/', $sanitized)) {
                $sanitized = '_' . $sanitized;
            }
        }
    }

    // If empty after sanitization, throw an exception
    if ($sanitized === '') {
        throw new \RuntimeException('Resulting sanitized string is empty.');
    }

    // Ensure the length doesn't exceed the maximum
    return mb_substr($sanitized, 0, $maxLength, 'UTF-8');
}

/**
 * Sanitize a domain name for use in MySQL database names.
 *
 * @param string $domainName       The domain name to sanitize.
 * @param string $username         An optional username to append to the sanitized domain.
 * @param bool   $includeTLD       Whether to include the TLD in the sanitized domain.
 * @param bool   $forceLetterStart Whether to force the resulting string to start with a letter.
 * @param string $prefix           The prefix to use if forcing the string to start with a letter.
 *
 * @return string The sanitized database name.
 */
function sanitize_domain_for_database(string $domainName, string $username = '', bool $includeTLD = true, bool $forceLetterStart = false, string $prefix = 'db_'): string
{
    // Remove protocol and www prefix, then convert to lowercase
    $domain = strtolower(preg_replace('#^(https?://)?(www\.)?#', '', $domainName));

    if (!$includeTLD) {
        // Remove the TLD
        $domain = preg_replace('/\.[^.]+$/', '', $domain);
    }

    // Replace non-alphanumeric characters with underscores and collapse multiple underscores
    $sanitizedDomain = preg_replace(['/[^a-z0-9]+/', '/_+/'], '_', $domain);

    // Remove leading underscores
    $sanitizedDomain = ltrim($sanitizedDomain, '_');

    // Check if we need to add a prefix
    $needsPrefix = $forceLetterStart && !ctype_alpha($sanitizedDomain[0]);

    // Calculate the maximum domain length
    $maxDomainLength = 64;

    if (strlen($username) > 0) {
        // Account for the username and underscore
        $maxDomainLength -= strlen($username) + 1;
    }

    if ($needsPrefix) {
        // Account for the prefix
        $maxDomainLength -= strlen($prefix);
    }

    // Truncate the domain if necessary
    $sanitizedDomain = substr($sanitizedDomain, 0, $maxDomainLength);

    // Add prefix if needed
    if ($needsPrefix) {
        $sanitizedDomain = $prefix . $sanitizedDomain;
    }

    // Append username if provided
    if ($username) {
        $sanitizedDomain .= '_' . preg_replace('/[^a-z0-9_]/', '_', strtolower($username));
    }

    // Ensure the final length doesn't exceed 64 characters
    $sanitizedDomain = substr($sanitizedDomain, 0, 64);

    // Remove any trailing underscores from the final result
    return rtrim($sanitizedDomain, '_');
}

/**
 * Validates a string identifier against a set of rules.
 *
 * Rules:
 * - String must not be empty
 * - String can only contain letters, numbers, hyphens, and underscores
 * - Optional maximum length check (defaults to 64 characters)
 * - Optional check against reserved strings (case-insensitive)
 *
 * @param string   $identifier      The string to validate
 * @param ?int     $maxLength       Optional maximum length (default: 64, null for no limit)
 * @param string[] $reservedStrings Optional array of reserved strings that are not allowed
 *
 * @throws \InvalidArgumentException If the identifier is invalid or contains reserved strings
 *
 * @return bool Returns true if validation passes (throws exception otherwise)
 */
function validate_identifier(
    string $identifier,
    ?int $maxLength = 64,
    array $reservedStrings = []
): bool {
    // Check for empty string
    if ($identifier === '') {
        throw new \InvalidArgumentException('Identifier cannot be empty.');
    }

    // Check maximum length if specified
    if ($maxLength !== null && strlen($identifier) > $maxLength) {
        throw new \InvalidArgumentException(
            sprintf('Identifier "%s" exceeds maximum length of %d characters.', $identifier, $maxLength)
        );
    }

    // Check for valid characters (letters, numbers, hyphens, underscores)
    if (!preg_match('/^[a-zA-Z0-9\-_]+$/', $identifier)) {
        throw new \InvalidArgumentException(
            sprintf('Identifier "%s" can only contain letters, numbers, hyphens, and underscores.', $identifier)
        );
    }

    // Check against reserved strings if any are provided (case-insensitive)
    foreach ($reservedStrings as $reserved) {
        if (strcasecmp($identifier, $reserved) === 0) {
            throw new \InvalidArgumentException(
                sprintf('Identifier "%s" matches reserved string.', $identifier)
            );
        }
    }

    return true;
}

/**
 * Validates whether a string is a valid UUID (Universally Unique Identifier).
 *
 * This function performs a format-based validation rather than a strict validation.
 * It checks if the input string matches the standard UUID format,
 * which consists of 5 hyphen-separated groups (8-4-4-4-12 hexadecimal characters).
 *
 * The function supports both uppercase and lowercase hexadecimal characters.
 * It does not validate the version or variant of the UUID, nor does it verify
 * that the UUID was generated according to any particular algorithm.
 *
 * @param string $uuid The string to validate as a UUID.
 *
 * @return bool Returns true if the string matches UUID format, false otherwise.
 */
function is_valid_uuid(string $uuid): bool
{
    return preg_match('/^[0-9a-f]{8}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{12}$/i', $uuid) === 1;
}
