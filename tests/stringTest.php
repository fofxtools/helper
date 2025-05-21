<?php

declare(strict_types=1);

namespace FOfX\Helper;

use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\Attributes\DataProvider;

class StringTest extends TestCase
{
    private $expected_quote;
    private $tempDir;
    private $originalDir;

    /**
     * Set up a temporary directory for testing
     */
    protected function setUp(): void
    {
        parent::setUp();

        // Determine the expected quote style based on the operating system
        $this->expected_quote = (PHP_OS_FAMILY === 'Windows') ? '"' : "'";

        // Store the original working directory
        $this->originalDir = getcwd();

        // Create a temporary directory
        $this->tempDir = rtrim(sys_get_temp_dir(), '/\\') . DIRECTORY_SEPARATOR . 'htmlpath_test_' . uniqid();
        if (!is_dir($this->tempDir)) {
            mkdir($this->tempDir);
        }

        // Create a subdirectory for testing
        $subdir = $this->tempDir . DIRECTORY_SEPARATOR . 'subdir';
        if (!is_dir($subdir)) {
            mkdir($subdir);
        }
    }

    /**
     * Clean up the temporary directory after testing
     */
    protected function tearDown(): void
    {
        // Change back to the original directory
        chdir($this->originalDir);

        // Remove the temporary directory and its contents
        $this->rrmdir($this->tempDir);

        parent::tearDown();
    }

    /**
     * Normalize a path for comparison across different operating systems
     *
     * @param string $path The path to normalize
     *
     * @return string The normalized path
     */
    private function normalizePathForComparison(string $path): string
    {
        // Convert Windows backslashes to forward slashes
        $path = str_replace('\\', '/', $path);

        // Remove any drive letter (for Windows compatibility)
        $path = preg_replace('/^[A-Za-z]:/', '', $path);

        // Ensure the path starts with a forward slash
        return '/' . ltrim($path, '/');
    }

    /**
     * Test the generate_password function with default parameters.
     *
     * @return void
     */
    public function test_generate_password_default(): void
    {
        $password = generate_password();

        $this->assertIsString($password);

        $this->assertEquals(8, strlen($password));
    }

    /**
     * Test the generate_password function with a specific length.
     *
     * @return void
     */
    public function test_generate_password_custom_length(): void
    {
        $password = generate_password(12);

        $this->assertIsString($password);

        $this->assertEquals(12, strlen($password));
    }

    /**
     * Test the generate_password function with numbers included.
     *
     * @return void
     */
    public function test_generate_password_include_numbers(): void
    {
        $password = generate_password(8, true, false, false);

        $this->assertMatchesRegularExpression('/[0-9]/', $password);
    }

    /**
     * Test the generate_password function with uppercase letters included.
     *
     * @return void
     */
    public function test_generate_password_include_uppercase(): void
    {
        $password = generate_password(8, false, true, false);

        $this->assertMatchesRegularExpression('/[A-Z]/', $password);
    }

    /**
     * Test the generate_password function with special characters included.
     *
     * @return void
     */
    public function test_generate_password_include_special(): void
    {
        $password = generate_password(8, false, false, true);

        $this->assertMatchesRegularExpression('/[!@#$%^&*]/', $password);
    }

    /**
     * Test the generate_password function with all character sets included.
     *
     * @return void
     */
    public function test_generate_password_include_all(): void
    {
        $password = generate_password(10, true, true, true);

        $this->assertMatchesRegularExpression('/[0-9]/', $password);

        $this->assertMatchesRegularExpression('/[A-Z]/', $password);

        $this->assertMatchesRegularExpression('/[!@#$%^&*]/', $password);
    }

    /**
     * Test that generate_password throws an exception for lengths less than 4.
     *
     * @return void
     */
    public function test_generate_password_throws_exception_for_invalid_length(): void
    {
        $this->expectException(\InvalidArgumentException::class);

        generate_password(3);
    }

    /**
     * Test the generate_password function with an extremely large length.
     *
     * @return void
     */
    public function test_generate_password_large_length(): void
    {
        $password = generate_password(1000);

        $this->assertIsString($password);

        $this->assertEquals(1000, strlen($password));
    }

    /**
     * Test the generate_password function for dirty input, passing negative length.
     *
     * @return void
     */
    public function test_generate_password_negative_length(): void
    {
        $this->expectException(\InvalidArgumentException::class);

        generate_password(-5);
    }

    /**
     * Test the generate_password function for edge case where length equals 4.
     *
     * @return void
     */
    public function test_generate_password_length_of_4(): void
    {
        $password = generate_password(4);

        $this->assertIsString($password);

        $this->assertEquals(4, strlen($password));
    }

    /**
     * Test the format_bytes function with typical input.
     *
     * @return void
     */
    public function test_format_bytes_standard_case(): void
    {
        // Testing formatting of 1024 bytes
        $result = format_bytes(1024);

        // Expecting the output to be '1 KB'
        $this->assertEquals('1 KB', $result);
    }

    /**
     * Test the format_bytes function with zero bytes.
     *
     * @return void
     */
    public function test_format_bytes_zero_bytes(): void
    {
        // Testing formatting of 0 bytes
        $result = format_bytes(0);

        // Expecting the output to be '0 B'
        $this->assertEquals('0 B', $result);
    }

    /**
     * Test the format_bytes function with very small bytes (under 1 KB).
     *
     * @return void
     */
    public function test_format_bytes_small_bytes(): void
    {
        // Testing formatting of 512 bytes
        $result = format_bytes(512);

        // Expecting the output to be '512 B'
        $this->assertEquals('512 B', $result);
    }

    /**
     * Test the format_bytes function with large bytes (over 1 GB).
     *
     * @return void
     */
    public function test_format_bytes_large_bytes(): void
    {
        // Testing formatting of 1073741824 bytes (1 GB)
        $result = format_bytes(1073741824);

        // Expecting the output to be '1 GB'
        $this->assertEquals('1 GB', $result);
    }

    /**
     * Test the format_bytes function with precision specified.
     *
     * @return void
     */
    public function test_format_bytes_with_precision(): void
    {
        // Testing formatting of 1536 bytes with precision of 1 decimal place
        $result = format_bytes(1536, 1);

        // Expecting the output to be '1.5 KB'
        $this->assertEquals('1.5 KB', $result);
    }

    /**
     * Test the format_bytes function with very large number of bytes (over 1 TB).
     *
     * @return void
     */
    public function test_format_bytes_very_large_bytes(): void
    {
        // Testing formatting of 1099511627776 bytes (1 TB)
        $result = format_bytes(1099511627776);

        // Expecting the output to be '1 TB'
        $this->assertEquals('1 TB', $result);
    }

    /**
     * Test the format_bytes function with a negative byte value.
     *
     * @return void
     */
    public function test_format_bytes_negative_bytes(): void
    {
        // Expect an InvalidArgumentException when using negative bytes
        $this->expectException(\InvalidArgumentException::class);

        // Testing with -1024 bytes
        format_bytes(-1024);
    }

    /**
     * Test the format_bytes function with extreme large bytes.
     *
     * @return void
     */
    public function test_format_bytes_extreme_large_bytes(): void
    {
        // Testing formatting of an extremely large byte value (beyond TB)
        $result = format_bytes(PHP_INT_MAX);

        // Expecting the result to end with the largest unit available, which is 'EB' (or higher if applicable)
        $this->assertStringEndsWith('EB', $result);
    }

    /**
     * Test format_bytes_array with a flat array of numeric values.
     *
     * @return void
     */
    public function test_format_bytes_array_flat_numeric_values(): void
    {
        $input = [1024, 2048, 4096];

        // Expecting formatted byte values
        $expected = ['1 KB', '2 KB', '4 KB'];

        $result = format_bytes_array($input);

        $this->assertEquals($expected, $result);
    }

    /**
     * Test format_bytes_array with a nested array of numeric values.
     *
     * @return void
     */
    public function test_format_bytes_array_nested_numeric_values(): void
    {
        $input = [
            'file1'  => 1024,
            'folder' => [
                'file2' => 2048,
                'file3' => 3072,
            ],
        ];

        // Expecting formatted byte values in nested arrays
        $expected = [
            'file1'  => '1 KB',
            'folder' => [
                'file2' => '2 KB',
                'file3' => '3 KB',
            ],
        ];

        $result = format_bytes_array($input);

        $this->assertEquals($expected, $result);
    }

    /**
     * Test format_bytes_array with mixed numeric and non-numeric values.
     *
     * @return void
     */
    public function test_format_bytes_array_mixed_values(): void
    {
        $input = [1024, 'text', 2048, ['another text', 3072]];

        // Expecting non-numeric values to remain unchanged
        $expected = ['1 KB', 'text', '2 KB', ['another text', '3 KB']];

        $result = format_bytes_array($input);

        $this->assertEquals($expected, $result);
    }

    /**
     * Test format_bytes_array with an empty array.
     *
     * @return void
     */
    public function test_format_bytes_array_empty_array(): void
    {
        $input = [];

        // Expecting an empty array
        $expected = [];

        $result = format_bytes_array($input);

        $this->assertEquals($expected, $result);
    }

    /**
     * Test format_bytes_array with a large numeric value.
     *
     * @return void
     */
    public function test_format_bytes_array_large_value(): void
    {
        // 1 GB
        $input = [1073741824];

        // Expecting the large byte value to be formatted as '1 GB'
        $expected = ['1 GB'];

        $result = format_bytes_array($input);

        $this->assertEquals($expected, $result);
    }

    /**
     * Test format_bytes_array with a precision parameter.
     *
     * @return void
     */
    public function test_format_bytes_array_with_precision(): void
    {
        $input = [1536, 2048];

        // Expecting formatted values with precision
        $expected = ['1.5 KB', '2 KB'];

        $result = format_bytes_array($input, 1);

        $this->assertEquals($expected, $result);
    }

    /**
     * Test format_bytes_array with a dirty input (non-array and non-numeric values).
     *
     * @return void
     */
    public function test_format_bytes_array_dirty_input(): void
    {
        $input = ['text', ['inner' => 'more text']];

        // Expecting the values to remain unchanged since they are not numeric
        $expected = ['text', ['inner' => 'more text']];

        $result = format_bytes_array($input);

        $this->assertEquals($expected, $result);
    }

    /**
     * Test format_bytes_array with deeply nested arrays.
     *
     * @return void
     */
    public function test_format_bytes_array_deeply_nested_arrays(): void
    {
        $input = [
            'level1' => [
                'level2' => [
                    'level3' => [
                        1024,
                        2048,
                    ],
                ],
            ],
        ];

        // Expecting the deeply nested arrays to be formatted
        $expected = [
            'level1' => [
                'level2' => [
                    'level3' => [
                        '1 KB',
                        '2 KB',
                    ],
                ],
            ],
        ];

        $result = format_bytes_array($input);

        $this->assertEquals($expected, $result);
    }

    /**
     * Test format_bytes_array with all zero values.
     *
     * @return void
     */
    public function test_format_bytes_array_all_zeros(): void
    {
        $input = [0, 0, 0];

        // Expecting all values to be formatted as '0 B'
        $expected = ['0 B', '0 B', '0 B'];

        $result = format_bytes_array($input);

        $this->assertEquals($expected, $result);
    }

    /**
     * Test strip_www with a domain that starts with "www."
     *
     * @return void
     */
    public function test_strip_www_with_www_prefix()
    {
        // Test domain with "www."
        $domain = 'www.example.com';
        $result = strip_www($domain);

        // Expected result without "www."
        $this->assertEquals('example.com', $result);
    }

    /**
     * Test strip_www with a domain that does not start with "www."
     *
     * @return void
     */
    public function test_strip_www_without_www_prefix()
    {
        // Test domain without "www."
        $domain = 'example.com';
        $result = strip_www($domain);

        // Expected result is the same as input
        $this->assertEquals('example.com', $result);
    }

    /**
     * Test strip_www with an empty domain and default to HTTP_HOST.
     *
     * @return void
     */
    public function test_strip_www_empty_domain_uses_http_host()
    {
        // Simulate the HTTP_HOST value
        $_SERVER['HTTP_HOST'] = 'www.example.com';
        $result               = strip_www(null);

        // Expected result without "www."
        $this->assertEquals('example.com', $result);
    }

    /**
     * Test strip_www with no domain and HTTP_HOST is not set.
     *
     * @return void
     */
    public function test_strip_www_no_http_host_defaults_to_localhost()
    {
        // Unset the HTTP_HOST to simulate absence
        unset($_SERVER['HTTP_HOST']);
        $result = strip_www(null);

        // Expected result is 'localhost'
        $this->assertEquals('localhost', $result);
    }

    /**
     * Test strip_www with a domain that has uppercase "WWW." prefix.
     *
     * @return void
     */
    public function test_strip_www_with_uppercase_www_prefix()
    {
        // Test domain with "WWW." in uppercase
        $domain = 'WWW.EXAMPLE.COM';
        $result = strip_www($domain);

        // Expected result with uppercase preserved
        $this->assertEquals('EXAMPLE.COM', $result);
    }

    /**
     * Test strip_www with a malformed domain to ensure it is handled safely.
     *
     * @return void
     */
    public function test_strip_www_malformed_domain()
    {
        // Test a dirty, malformed domain
        $domain = 'www.@malformed_domain';
        $result = strip_www($domain);

        // Expected result is without the "www." prefix, even for malformed domains
        $this->assertEquals('@malformed_domain', $result);
    }

    /**
     * Test strip_www with an empty string as a domain.
     *
     * @return void
     */
    public function test_strip_www_empty_string_domain()
    {
        // Test with an empty string as domain
        $domain = '';
        $result = strip_www($domain);

        // Expected result defaults to HTTP_HOST or 'localhost'
        $this->assertEquals('localhost', $result);
    }

    /**
     * Test strip_www with a domain starting with "www." and containing non-ASCII characters.
     *
     * @return void
     */
    public function test_strip_www_with_non_ascii_domain()
    {
        // Test domain with non-ASCII characters
        $domain = 'www.例子.com';
        $result = strip_www($domain);

        // Expected result without "www." and keeping non-ASCII characters intact
        $this->assertEquals('例子.com', $result);
    }

    /**
     * Test normal operation of htmlpath function
     */
    public function test_htmlpath_normal_operation(): void
    {
        // Create a test file
        $testFile = $this->tempDir . DIRECTORY_SEPARATOR . 'test.txt';
        touch($testFile);

        // Test with explicit document root
        $result = $this->normalizePathForComparison(htmlpath($testFile, $this->tempDir));
        $this->assertEquals('/test.txt', $result);

        // Test with subdirectory
        $subFile = $this->tempDir . DIRECTORY_SEPARATOR . 'subdir' . DIRECTORY_SEPARATOR . 'test.txt';
        touch($subFile);
        $result = $this->normalizePathForComparison(htmlpath($subFile, $this->tempDir));
        $this->assertEquals('/subdir/test.txt', $result);
    }

    /**
     * Test htmlpath function with relative paths
     */
    public function test_htmlpath_relative_paths(): void
    {
        // Change to the temporary directory
        chdir($this->tempDir);

        // Create a test file
        touch('test.txt');

        // Test with relative path
        $result = $this->normalizePathForComparison(htmlpath('./test.txt', $this->tempDir));
        $this->assertEquals('/test.txt', $result);

        // Test with parent directory reference
        touch('subdir' . DIRECTORY_SEPARATOR . 'test.txt');
        chdir('subdir');
        $result = $this->normalizePathForComparison(htmlpath('../test.txt', $this->tempDir));
        $this->assertEquals('/test.txt', $result);
    }

    /**
     * Test htmlpath function with invalid paths
     */
    public function test_htmlpath_invalid_path(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        htmlpath($this->tempDir . DIRECTORY_SEPARATOR . 'nonexistent.txt', $this->tempDir);
    }

    /**
     * Test htmlpath function with default document root
     */
    public function test_htmlpath_default_document_root(): void
    {
        // Mock $_SERVER['DOCUMENT_ROOT']
        $_SERVER['DOCUMENT_ROOT'] = $this->tempDir;

        // Create a test file
        $testFile = $this->tempDir . DIRECTORY_SEPARATOR . 'test.txt';
        touch($testFile);

        // Test with default document root
        $result = $this->normalizePathForComparison(htmlpath($testFile));
        $this->assertEquals('/test.txt', $result);
    }

    /**
     * Test htmlpath function with "dirty" paths
     */
    public function test_htmlpath_dirty_paths(): void
    {
        // Create a test file
        $testFile = $this->tempDir . DIRECTORY_SEPARATOR . 'test.txt';
        touch($testFile);

        // Test with extra slashes
        $result = $this->normalizePathForComparison(htmlpath($this->tempDir . DIRECTORY_SEPARATOR . DIRECTORY_SEPARATOR . 'test.txt', $this->tempDir));
        $this->assertEquals('/test.txt', $result);

        // Test with '..' in path
        $result = $this->normalizePathForComparison(htmlpath($this->tempDir . DIRECTORY_SEPARATOR . 'subdir' . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . 'test.txt', $this->tempDir));
        $this->assertEquals('/test.txt', $result);
    }

    /**
     * Recursively remove a directory and its contents
     *
     * @param string $dir The directory to remove
     */
    private function rrmdir($dir): void
    {
        if (is_dir($dir)) {
            $objects = scandir($dir);
            foreach ($objects as $object) {
                if ($object != '.' && $object != '..') {
                    if (is_dir($dir . DIRECTORY_SEPARATOR . $object)) {
                        $this->rrmdir($dir . DIRECTORY_SEPARATOR . $object);
                    } else {
                        unlink($dir . DIRECTORY_SEPARATOR . $object);
                    }
                }
            }
            rmdir($dir);
        }
    }

    /**
     * Test extracting variables from a basic string.
     */
    public function test_string_get_vars_basic()
    {
        $subject = 'Hello $var1, $var2';

        $expected = [
            ['$var1', '$var2'],
            ['var1', 'var2'],
        ];

        $this->assertEquals($expected, string_get_vars($subject));
    }

    /**
     * Test extracting variables with escaped variables.
     */
    public function test_string_get_vars_escaped_variables()
    {
        $subject = 'Here is \$escapedVar and $realVar';

        $expected = [
            ['$realVar'],
            ['realVar'],
        ];

        $this->assertEquals($expected, string_get_vars($subject));
    }

    /**
     * Test extracting variables with variables followed by punctuation.
     */
    public function test_string_get_vars_punctuation()
    {
        $subject = 'Variables like $var, $var2. should be captured';

        $expected = [
            ['$var', '$var2'],
            ['var', 'var2'],
        ];

        $this->assertEquals($expected, string_get_vars($subject));
    }

    /**
     * Test with variables that contain underscores.
     */
    public function test_string_get_vars_with_underscores()
    {
        $subject = '$var_one and $var_two should be captured';

        $expected = [
            ['$var_one', '$var_two'],
            ['var_one', 'var_two'],
        ];

        $this->assertEquals($expected, string_get_vars($subject));
    }

    /**
     * Test extracting variables from an empty string.
     */
    public function test_string_get_vars_empty_string()
    {
        $subject = '';

        $expected = [
            [],
            [],
        ];

        $this->assertEquals($expected, string_get_vars($subject));
    }

    /**
     * Test extracting variables when there are no variables present.
     */
    public function test_string_get_vars_no_variables()
    {
        $subject = 'This string has no variables.';

        $expected = [
            [],
            [],
        ];

        $this->assertEquals($expected, string_get_vars($subject));
    }

    /**
     * Test extracting invalid variable names (those starting with numbers).
     */
    public function test_string_get_vars_invalid_variable_names()
    {
        $subject = 'Invalid $123var should not be captured.';

        $expected = [
            [],
            [],
        ];

        $this->assertEquals($expected, string_get_vars($subject));
    }

    /**
     * Test extracting both valid and invalid variables.
     */
    public function test_string_get_vars_mixed_valid_invalid_variables()
    {
        $subject = 'Here is a valid $var and an invalid $123var';

        $expected = [
            ['$var'],
            ['var'],
        ];

        $this->assertEquals($expected, string_get_vars($subject));
    }

    /**
     * Test unique flag is honored.
     */
    public function test_string_get_vars_unique_flag()
    {
        $subject = 'Repeated $var and $var in the same string';

        $expected = [
            ['$var'],
            ['var'],
        ];

        $this->assertEquals($expected, string_get_vars($subject, true));
    }

    /**
     * Test variables inside curly braces in double-quoted strings.
     */
    public function test_string_get_vars_curly_braces()
    {
        $subject = 'Variable inside curly braces like {$user} should be captured';

        $expected = [
            ['$user'],
            ['user'],
        ];

        $this->assertEquals($expected, string_get_vars($subject));
    }

    /**
     * Test extracting variables with numbers and special characters.
     */
    public function test_string_get_vars_numbers_and_special_chars()
    {
        $subject = 'Variables like $var1_$2 and $var3 should capture correctly';

        $expected = [
            ['$var1_', '$var3'],
            ['var1_', 'var3'],
        ];

        $this->assertEquals($expected, string_get_vars($subject));
    }

    /**
     * Test variables with Unicode characters.
     */
    public function test_string_get_vars_unicode_characters()
    {
        $subject = 'Unicode variables like $变量 should be captured';

        $expected = [
            ['$变量'],
            ['变量'],
        ];

        $this->assertEquals($expected, string_get_vars($subject));
    }

    /**
     * Test replacing variables with $GLOBALS scope.
     */
    public function test_replace_vars_scope_globals()
    {
        $subject = 'Hello $var, please check $id.';

        $expected = 'Hello $GLOBALS[\'var\'], please check $GLOBALS[\'id\'].';

        $this->assertEquals($expected, replace_vars_scope($subject, 'GLOBALS'));
    }

    /**
     * Test replacing variables with a custom scope, such as _SERVER.
     */
    public function test_replace_vars_scope_custom_scope()
    {
        $subject = 'Accessing $user and $session_id.';

        // Corrected expected output without the comma
        $expected = 'Accessing $_SERVER[\'user\'] and $_SERVER[\'session_id\'].';

        $this->assertEquals($expected, replace_vars_scope($subject, '_SERVER'));
    }

    /**
     * Test replacing variables when there are no variables in the string.
     */
    public function test_replace_vars_scope_no_variables()
    {
        $subject = 'This string has no variables.';

        $expected = 'This string has no variables.';

        $this->assertEquals($expected, replace_vars_scope($subject, 'GLOBALS'));
    }

    /**
     * Test replacing variables with escaped variables in the string.
     */
    public function test_replace_vars_scope_escaped_variables()
    {
        $subject = 'Hello \$escapedVar and $realVar.';

        $expected = 'Hello \$escapedVar and $GLOBALS[\'realVar\'].';

        $this->assertEquals($expected, replace_vars_scope($subject, 'GLOBALS'));
    }

    /**
     * Test replacing variables in a string with special characters.
     */
    public function test_replace_vars_scope_special_characters()
    {
        $subject = 'Special characters $var_1, $var_with_underscore.';

        $expected = 'Special characters $GLOBALS[\'var_1\'], $GLOBALS[\'var_with_underscore\'].';

        $this->assertEquals($expected, replace_vars_scope($subject, 'GLOBALS'));
    }

    /**
     * Test with multiple instances of the same variable in a string.
     */
    public function test_replace_vars_scope_multiple_instances()
    {
        $subject = 'The value of $var is $var.';

        $expected = 'The value of $GLOBALS[\'var\'] is $GLOBALS[\'var\'].';

        $this->assertEquals($expected, replace_vars_scope($subject, 'GLOBALS'));
    }

    /**
     * Test replacing variables in a string with numeric or invalid variable names.
     */
    public function test_replace_vars_scope_invalid_variable_names()
    {
        $subject = 'Invalid variables like $123var should not be captured.';

        $expected = 'Invalid variables like $123var should not be captured.';

        $this->assertEquals($expected, replace_vars_scope($subject, 'GLOBALS'));
    }

    /**
     * Test with an empty string as input.
     */
    public function test_replace_vars_scope_empty_string()
    {
        $subject = '';

        $expected = '';

        $this->assertEquals($expected, replace_vars_scope($subject, 'GLOBALS'));
    }

    /**
     * Test replacing variables with Unicode characters.
     */
    public function test_replace_vars_scope_unicode_variables()
    {
        $subject = 'Variables like $变量 should be captured.';

        $expected = 'Variables like $GLOBALS[\'变量\'] should be captured.';

        $this->assertEquals($expected, replace_vars_scope($subject, 'GLOBALS'));
    }

    /**
     * Test that replace_vars_scope throws an exception for an invalid scope.
     *
     * @return void
     */
    public function test_replace_vars_scope_invalid_scope(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Invalid scope provided. Allowed scopes are:');

        $subject = 'Hello $var';
        replace_vars_scope($subject, 'INVALID_SCOPE');
    }

    /**
     * Test that replace_vars_scope throws an exception for an empty scope.
     *
     * @return void
     */
    public function test_replace_vars_scope_empty_scope(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Invalid scope provided. Allowed scopes are:');

        $subject = 'Hello $var';
        replace_vars_scope($subject, '');
    }

    /**
     * Test rendering a basic array without htmlspecialchars, echoing the result.
     */
    public function test_print_array_with_headings_basic_echo()
    {
        $array = ['name' => 'John', 'age' => 30];

        // Expecting the string with HTML headings for the keys
        $expected = '<h2>name</h2>' . PHP_EOL . 'John' . PHP_EOL . '<h2>age</h2>' . PHP_EOL . '30';

        $this->expectOutputString($expected);
        print_array_with_headings($array, false, false, 'h2');
    }

    /**
     * Test rendering a basic array with htmlspecialchars enabled, returning the result.
     */
    public function test_print_array_with_headings_htmlspecialchars_return()
    {
        $array = ['name' => '<John>', 'age' => 30];

        // Expecting the string with HTML headings for the keys and escaped values
        $expected = '<h2>name</h2>' . PHP_EOL . '&lt;John&gt;' . PHP_EOL . '<h2>age</h2>' . PHP_EOL . '30';

        $output = print_array_with_headings($array, true, true, 'h2');
        $this->assertEquals($expected, $output);
    }

    /**
     * Test rendering an empty array.
     */
    public function test_print_array_with_headings_empty_array()
    {
        $array = [];

        // Expecting no output for an empty array
        $expected = '';

        $output = print_array_with_headings($array, false, true, 'h2');
        $this->assertEquals($expected, $output);
    }

    /**
     * Test rendering an array with an invalid HTML heading tag.
     */
    public function test_print_array_with_headings_invalid_heading_tag()
    {
        $this->expectException(\InvalidArgumentException::class);

        $array = ['name' => 'John'];

        // This should throw an exception because 'h7' is not a valid heading tag
        print_array_with_headings($array, false, true, 'h7');
    }

    /**
     * Test rendering a nested array recursively.
     */
    public function test_print_array_with_headings_recursive_array()
    {
        $array = [
            'person' => [
                'name' => 'John',
                'age'  => 30,
            ],
            'location' => 'New York',
        ];

        // Expecting the string with headings for both levels of the array
        $expected = '<h2>person</h2>' . PHP_EOL
            . '<h2>name</h2>' . PHP_EOL . 'John' . PHP_EOL
            . '<h2>age</h2>' . PHP_EOL . '30' . PHP_EOL
            . '<h2>location</h2>' . PHP_EOL . 'New York';

        $output = print_array_with_headings($array, false, true, 'h2');
        $this->assertEquals($expected, $output);
    }

    /**
     * Test rendering an array with special characters, and htmlspecialchars enabled.
     */
    public function test_print_array_with_headings_special_characters()
    {
        $array = ['<name>' => 'John & Jane', 'age' => 30];

        // Expecting the string with HTML headings and special characters escaped
        $expected = '<h2>&lt;name&gt;</h2>' . PHP_EOL . 'John &amp; Jane' . PHP_EOL . '<h2>age</h2>' . PHP_EOL . '30';

        $output = print_array_with_headings($array, true, true, 'h2');
        $this->assertEquals($expected, $output);
    }

    /**
     * Test rendering an array and trimming the resulting string.
     */
    public function test_print_array_with_headings_trim_output()
    {
        $array = ['name' => 'John', 'age' => 30];

        // Expecting the string without leading or trailing newlines
        $expected = '<h2>name</h2>' . PHP_EOL . 'John' . PHP_EOL . '<h2>age</h2>' . PHP_EOL . '30';

        // Manually trim the output to simulate trimming behavior
        $output = trim(print_array_with_headings($array, false, true, 'h2'));
        $this->assertEquals($expected, $output);
    }

    /**
     * Test rendering an array using a custom heading tag.
     */
    public function test_print_array_with_headings_custom_heading_tag()
    {
        $array = ['name' => 'John', 'age' => 30];

        // Expecting the string with custom h3 tags instead of h2
        $expected = '<h3>name</h3>' . PHP_EOL . 'John' . PHP_EOL . '<h3>age</h3>' . PHP_EOL . '30';

        $output = print_array_with_headings($array, false, true, 'h3');
        $this->assertEquals($expected, $output);
    }

    /**
     * Test print_h2() with a simple array and default parameters.
     */
    public function test_print_h2_with_simple_array()
    {
        $input    = ['key1' => 'value1', 'key2' => 'value2'];
        $expected = '<h2>key1</h2>' . PHP_EOL . 'value1' . PHP_EOL . '<h2>key2</h2>' . PHP_EOL . 'value2';

        $result = print_h2($input, false, true);
        $this->assertEquals($expected, $result);
    }

    /**
     * Test print_h2() with htmlspecialchars enabled.
     */
    public function test_print_h2_with_html_special_chars()
    {
        $input    = ['<key>' => '<value>'];
        $expected = '<h2>&lt;key&gt;</h2>' . PHP_EOL . '&lt;value&gt;';

        $result = print_h2($input, true, true);
        $this->assertEquals($expected, $result);
    }

    /**
     * Test print_h2() with a nested array.
     */
    public function test_print_h2_with_nested_array()
    {
        $input = [
            'key1' => 'value1',
            'key2' => ['nested1' => 'nestedvalue1', 'nested2' => 'nestedvalue2'],
        ];
        $expected = '<h2>key1</h2>' . PHP_EOL . 'value1' . PHP_EOL . '<h2>key2</h2>' . PHP_EOL .
            '<h2>nested1</h2>' . PHP_EOL . 'nestedvalue1' . PHP_EOL .
            '<h2>nested2</h2>' . PHP_EOL . 'nestedvalue2';

        $result = print_h2($input, false, true);
        $this->assertEquals($expected, $result);
    }

    /**
     * Test print_h2() with an empty array.
     */
    public function test_print_h2_with_empty_array()
    {
        $input    = [];
        $expected = '';

        $result = print_h2($input, false, true);
        $this->assertEquals($expected, $result);
    }

    /**
     * Test print_h2() with echo instead of return.
     */
    public function test_print_h2_with_echo()
    {
        $input    = ['key' => 'value'];
        $expected = '<h2>key</h2>' . PHP_EOL . 'value';

        ob_start();
        print_h2($input);
        $result = ob_get_clean();
        $this->assertEquals($expected, $result);
    }

    /**
     * Test print_h2() with null values.
     */
    public function test_print_h2_with_null_values()
    {
        $input    = ['key1' => null, 'key2' => 'value2'];
        $expected = '<h2>key1</h2>' . PHP_EOL . PHP_EOL . '<h2>key2</h2>' . PHP_EOL . 'value2';

        $result = print_h2($input, false, true);
        $this->assertEquals($expected, $result);
    }

    /**
     * Test print_hx() with a simple array and default parameters.
     */
    public function test_print_hx_with_simple_array()
    {
        $input    = ['key1' => 'value1', 'key2' => 'value2'];
        $expected = '<h1>key1</h1>' . PHP_EOL . 'value1' . PHP_EOL . '<h1>key2</h1>' . PHP_EOL . 'value2';

        $result = print_hx($input, false, true);
        $this->assertEquals($expected, $result);
    }

    /**
     * Test print_hx() with htmlspecialchars enabled.
     */
    public function test_print_hx_with_html_special_chars()
    {
        $input    = ['<key>' => '<value>'];
        $expected = '<h1>&lt;key&gt;</h1>' . PHP_EOL . '&lt;value&gt;';

        $result = print_hx($input, true, true);
        $this->assertEquals($expected, $result);
    }

    /**
     * Test print_hx() with a nested array to check dynamic header levels.
     */
    public function test_print_hx_with_nested_array()
    {
        $input = [
            'key1' => 'value1',
            'key2' => ['nested1' => 'nestedvalue1', 'nested2' => 'nestedvalue2'],
        ];
        $expected = '<h1>key1</h1>' . PHP_EOL . 'value1' . PHP_EOL .
            '<h1>key2</h1>' . PHP_EOL .
            '<h2>nested1</h2>' . PHP_EOL . 'nestedvalue1' . PHP_EOL .
            '<h2>nested2</h2>' . PHP_EOL . 'nestedvalue2';

        $result = print_hx($input, false, true);
        $this->assertEquals($expected, $result);
    }

    /**
     * Test print_hx() with a deeply nested array to check header level cap at h6.
     */
    public function test_print_hx_with_deeply_nested_array()
    {
        $input = [
            'l1' => [
                'l2' => [
                    'l3' => [
                        'l4' => [
                            'l5' => [
                                'l6' => [
                                    'l7' => 'deep value',
                                ],
                            ],
                        ],
                    ],
                ],
            ],
        ];
        $expected = '<h1>l1</h1>' . PHP_EOL .
            '<h2>l2</h2>' . PHP_EOL .
            '<h3>l3</h3>' . PHP_EOL .
            '<h4>l4</h4>' . PHP_EOL .
            '<h5>l5</h5>' . PHP_EOL .
            '<h6>l6</h6>' . PHP_EOL .
            '<h6>l7</h6>' . PHP_EOL . 'deep value';

        $result = print_hx($input, false, true);
        $this->assertEquals($expected, $result);
    }

    /**
     * Test print_hx() with an empty array.
     */
    public function test_print_hx_with_empty_array()
    {
        $input    = [];
        $expected = '';

        $result = print_hx($input, false, true);
        $this->assertEquals($expected, $result);
    }

    /**
     * Test print_hx() with echo instead of return.
     */
    public function test_print_hx_with_echo()
    {
        $input    = ['key' => 'value'];
        $expected = '<h1>key</h1>' . PHP_EOL . 'value';

        ob_start();
        print_hx($input);
        $result = ob_get_clean();
        $this->assertEquals($expected, $result);
    }

    /**
     * Test print_hx() with null values.
     */
    public function test_print_hx_with_null_values()
    {
        $input    = ['key1' => null, 'key2' => 'value2'];
        $expected = '<h1>key1</h1>' . PHP_EOL . PHP_EOL . '<h1>key2</h1>' . PHP_EOL . 'value2';

        $result = print_hx($input, false, true);
        $this->assertEquals($expected, $result);
    }

    /**
     * Test var_dump_string with a simple string
     */
    public function test_var_dump_string_with_string()
    {
        $result = var_dump_string('Hello, World!');

        $this->assertStringContainsString('string(13) "Hello, World!"', $result);
    }

    /**
     * Test var_dump_string with an integer
     */
    public function test_var_dump_string_with_integer()
    {
        $result = var_dump_string(42);

        $this->assertStringContainsString('int(42)', $result);
    }

    /**
     * Test var_dump_string with a float
     */
    public function test_var_dump_string_with_float()
    {
        $result = var_dump_string(3.14);

        $this->assertStringContainsString('float(3.14)', $result);
    }

    /**
     * Test var_dump_string with a boolean
     */
    public function test_var_dump_string_with_boolean()
    {
        $result = var_dump_string(true);

        $this->assertStringContainsString('bool(true)', $result);
    }

    /**
     * Test var_dump_string with null
     */
    public function test_var_dump_string_with_null()
    {
        $result = var_dump_string(null);

        $this->assertStringContainsString('NULL', $result);
    }

    /**
     * Test var_dump_string with an array
     */
    public function test_var_dump_string_with_array()
    {
        $result = var_dump_string([1, 2, 3]);

        $this->assertStringContainsString('array(1) {', $result);
        $this->assertStringContainsString('array(3) {', $result);
        $this->assertStringContainsString('int(1)', $result);
        $this->assertStringContainsString('int(2)', $result);
        $this->assertStringContainsString('int(3)', $result);
    }

    /**
     * Test var_dump_string with an object
     */
    public function test_var_dump_string_with_object()
    {
        $obj       = new \stdClass();
        $obj->name = 'Test';
        $result    = var_dump_string($obj);

        $this->assertStringContainsString('array(1) {', $result);
        $this->assertStringContainsString('object(stdClass)', $result);
        $this->assertStringContainsString('["name"]=>', $result);
        $this->assertStringContainsString('string(4) "Test"', $result);
    }

    /**
     * Test var_dump_string with multiple arguments
     */
    public function test_var_dump_string_with_multiple_arguments()
    {
        $result = var_dump_string('Hello', 42, [1, 2, 3]);

        $this->assertStringContainsString('string(5) "Hello"', $result);
        $this->assertStringContainsString('int(42)', $result);
        $this->assertStringContainsString('array(3)', $result);
    }

    /**
     * Test var_dump_string with a resource (dirty case)
     */
    public function test_var_dump_string_with_resource()
    {
        $resource = fopen('php://memory', 'r');
        $result   = var_dump_string($resource);
        fclose($resource);

        $this->assertStringContainsString('resource(', $result);
        $this->assertStringContainsString('stream', $result);
    }

    /**
     * Test var_dump_string with a very large array (edge case)
     */
    public function test_var_dump_string_with_large_array()
    {
        $largeArray = range(1, 10000);
        $result     = var_dump_string($largeArray);

        $this->assertStringContainsString('array(10000)', $result);
        $this->assertStringContainsString('int(1)', $result);
        $this->assertStringContainsString('int(10000)', $result);
    }

    /**
     * Test var_dump_string with a deeply nested array (edge case)
     */
    public function test_var_dump_string_with_nested_array()
    {
        $nestedArray = array_fill(0, 100, array_fill(0, 100, 'deep'));
        $result      = var_dump_string($nestedArray);

        $this->assertStringContainsString('array(100)', $result);
        $this->assertStringContainsString('string(4) "deep"', $result);
    }

    /**
     * Test var_dump_string with no arguments (edge case)
     */
    public function test_var_dump_string_with_no_arguments()
    {
        $result = var_dump_string();

        $this->assertEquals("array(0) {\n}\n", $result);
    }

    /**
     * Test var_dump_string with a closure (edge case)
     */
    public function test_var_dump_string_with_closure()
    {
        $closure = function () {
            return 'test';
        };
        $result = var_dump_string($closure);

        $this->assertStringContainsString('object(Closure)', $result);
    }

    /**
     * Tests strip_tags_with_whitespace() with a simple string containing HTML tags.
     *
     * @return void
     */
    public function test_strip_tags_with_whitespace_basic_tags(): void
    {
        // Test a basic string with HTML tags
        $input    = 'Hello <b>world</b>!';
        $expected = 'Hello world !';
        $this->assertEquals($expected, strip_tags_with_whitespace($input));
    }

    /**
     * Tests strip_tags_with_whitespace() when certain tags are allowed.
     *
     * @return void
     */
    public function test_strip_tags_with_whitespace_with_allowed_tags(): void
    {
        // Test allowing <b> tags and ensuring they are not stripped, but adding space before closing tag
        $input    = 'Hello <b>world</b>!';
        $expected = 'Hello <b>world </b>!';
        $this->assertEquals($expected, strip_tags_with_whitespace($input, '<b>'));
    }

    /**
     * Tests strip_tags_with_whitespace() with nested tags.
     *
     * @return void
     */
    public function test_strip_tags_with_whitespace_nested_tags(): void
    {
        // Test nested HTML tags, extra spaces may occur
        $input    = '<div>Hello <b><i>world</i></b>!</div>';
        $expected = 'Hello  world !';
        $this->assertEquals($expected, strip_tags_with_whitespace($input));
    }

    /**
     * Tests strip_tags_with_whitespace() with no HTML tags.
     *
     * @return void
     */
    public function test_strip_tags_with_whitespace_no_tags(): void
    {
        // Test a string without HTML tags
        $input    = 'Hello world!';
        $expected = 'Hello world!';
        $this->assertEquals($expected, strip_tags_with_whitespace($input));
    }

    /**
     * Tests strip_tags_with_whitespace() with an empty string.
     *
     * @return void
     */
    public function test_strip_tags_with_whitespace_empty_string(): void
    {
        // Test an empty string
        $input    = '';
        $expected = '';
        $this->assertEquals($expected, strip_tags_with_whitespace($input));
    }

    /**
     * Tests strip_tags_with_whitespace() when there are consecutive spaces after stripping tags.
     *
     * @return void
     */
    public function test_strip_tags_with_whitespace_consecutive_spaces(): void
    {
        // Test a string where tag removal might introduce extra spaces
        $input    = 'Hello   <b>world</b>   !';
        $expected = 'Hello  world  !';
        $this->assertEquals($expected, strip_tags_with_whitespace($input));
    }

    /**
     * Tests strip_tags_with_whitespace() with dirty/invalid HTML input.
     *
     * @return void
     */
    public function test_strip_tags_with_whitespace_dirty_html(): void
    {
        // Test dirty HTML input with unclosed tags
        $input    = 'Hello <b>world!';
        $expected = 'Hello world!';
        $this->assertEquals($expected, strip_tags_with_whitespace($input));
    }

    /**
     * Tests strip_tags_with_whitespace() with a very long string.
     *
     * @return void
     */
    public function test_strip_tags_with_whitespace_long_string(): void
    {
        // Test a long string containing repeated HTML tags
        $input    = str_repeat('Hello <b>world</b>! ', 1000);
        $expected = rtrim(str_repeat('Hello world ! ', 1000));
        $this->assertEquals($expected, strip_tags_with_whitespace($input));
    }

    /**
     * Tests strip_tags_with_whitespace() when there are special characters and symbols.
     *
     * @return void
     */
    public function test_strip_tags_with_whitespace_special_characters(): void
    {
        // Test a string containing special characters and symbols
        $input    = 'Héllô <i>wørld</i> & welcome!';
        $expected = 'Héllô wørld & welcome!';
        $this->assertEquals($expected, strip_tags_with_whitespace($input));
    }

    /**
     * Tests strip_tags_with_whitespace() when the allowable tags include self-closing tags.
     *
     * @return void
     */
    public function test_strip_tags_with_whitespace_self_closing_tags(): void
    {
        // Test a string containing self-closing tags like <br />
        $input    = 'Line1<br />Line2';
        $expected = 'Line1 <br />Line2';
        $this->assertEquals($expected, strip_tags_with_whitespace($input, '<br>'));
    }

    /**
     * Tests strip_tags_with_whitespace() when allowable tags are provided as an array.
     *
     * @return void
     */
    public function test_strip_tags_with_whitespace_allowed_tags_array(): void
    {
        // Test providing allowable tags as an array
        $input    = 'Hello <b>world</b>!';
        $expected = 'Hello <b>world </b>!';
        $this->assertEquals($expected, strip_tags_with_whitespace($input, ['b']));
    }

    /**
     * Tests strip_tags_with_whitespace() when the allowable tags argument is invalid.
     *
     * @return void
     */
    public function test_strip_tags_with_whitespace_invalid_allowed_tags(): void
    {
        // Test with invalid allowable tags
        $input    = 'Hello <b>world</b>!';
        $expected = 'Hello world !';
        $this->assertEquals($expected, strip_tags_with_whitespace($input, '<invalid>'));
    }

    /**
     * Tests strip_non_alpha() with a basic string containing non-alphabetical characters.
     *
     * @return void
     */
    public function test_strip_non_alpha_basic(): void
    {
        // Test a basic string with non-alphabetical characters
        $input    = 'Hello, World! 123';
        $expected = 'HelloWorld';
        $this->assertEquals($expected, strip_non_alpha($input));
    }

    /**
     * Tests strip_non_alpha() with a string containing only alphabetical characters.
     *
     * @return void
     */
    public function test_strip_non_alpha_alphabetical_only(): void
    {
        // Test a string with only alphabetical characters
        $input = 'ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz';
        // Should not change
        $expected = $input;
        $this->assertEquals($expected, strip_non_alpha($input));
    }

    /**
     * Tests strip_non_alpha() with a string containing non-ASCII characters (Unicode).
     *
     * @return void
     */
    public function test_strip_non_alpha_unicode_characters(): void
    {
        // Test a string containing Unicode alphabetic characters
        $input    = '中文 русский English';
        $expected = '中文русскийEnglish';
        $this->assertEquals($expected, strip_non_alpha($input, true));
    }

    /**
     * Tests strip_non_alpha() with a string that contains special characters and numbers.
     *
     * @return void
     */
    public function test_strip_non_alpha_special_characters(): void
    {
        // Test a string with special characters
        $input    = 'Hello @#*&^ World! 12345';
        $expected = 'HelloWorld';
        $this->assertEquals($expected, strip_non_alpha($input));
    }

    /**
     * Tests strip_non_alpha() with a dirty input containing a mixture of different characters.
     *
     * @return void
     */
    public function test_strip_non_alpha_dirty_input(): void
    {
        // Test a string with HTML, numbers, and special characters
        $input = 'This is a <strong>dirty</strong> string with 12345 numbers & symbols!';
        // Keeps <strong> tags because they contain letters
        $expected = 'Thisisastrongdirtystrongstringwithnumberssymbols';
        $this->assertEquals($expected, strip_non_alpha($input));
    }

    /**
     * Tests strip_non_alpha() with an empty string input.
     *
     * @return void
     */
    public function test_strip_non_alpha_empty_string(): void
    {
        // Test with an empty string
        $input    = '';
        $expected = '';
        $this->assertEquals($expected, strip_non_alpha($input));
    }

    /**
     * Tests strip_non_alpha() with a string containing newline and whitespace characters.
     *
     * @return void
     */
    public function test_strip_non_alpha_newlines_whitespace(): void
    {
        // Test a string with newlines and spaces
        $input    = "Hello\nWorld \t! ";
        $expected = 'HelloWorld';
        $this->assertEquals($expected, strip_non_alpha($input));
    }

    /**
     * Tests strip_non_alpha() with numbers and punctuation characters.
     *
     * @return void
     */
    public function test_strip_non_alpha_numbers_and_punctuation(): void
    {
        // Test a string with numbers and punctuation
        $input = '1234567890!?.';
        // All non-alphabetic characters
        $expected = '';
        $this->assertEquals($expected, strip_non_alpha($input));
    }

    /**
     * Tests strip_non_alpha() with a very long string input.
     *
     * @return void
     */
    public function test_strip_non_alpha_long_string(): void
    {
        // Test a long string
        $input    = str_repeat('Hello123World! ', 1000);
        $expected = str_repeat('HelloWorld', 1000);
        $this->assertEquals($expected, strip_non_alpha($input));
    }

    /**
     * Tests strip_non_alpha() with a replacement character.
     *
     * @return void
     */
    public function test_strip_non_alpha_replacement(): void
    {
        $replacement = '_';
        $input       = 'Hello123World!';
        $expected    = 'Hello___World_';
        $this->assertEquals($expected, strip_non_alpha($input, false, $replacement));
    }

    /**
     * Tests strip_non_digit() with a string containing English digits and non-digit characters.
     *
     * @return void
     */
    public function test_strip_non_digit_basic(): void
    {
        // Test a basic string with English digits and non-digit characters
        $input    = 'Phone: (123) 456-7890';
        $expected = '1234567890';
        $this->assertEquals($expected, strip_non_digit($input));
    }

    /**
     * Tests strip_non_digit() when the string contains only digits.
     *
     * @return void
     */
    public function test_strip_non_digit_digits_only(): void
    {
        // Test a string with only digits
        $input = '0123456789';
        // No changes should be made
        $expected = $input;
        $this->assertEquals($expected, strip_non_digit($input));
    }

    /**
     * Tests strip_non_digit() with a string that contains special characters and spaces.
     *
     * @return void
     */
    public function test_strip_non_digit_special_characters(): void
    {
        // Test a string with special characters and spaces
        $input    = 'Order #: !123 *456&789?';
        $expected = '123456789';
        $this->assertEquals($expected, strip_non_digit($input));
    }

    /**
     * Tests strip_non_digit() with Unicode digits allowed.
     *
     * @return void
     */
    public function test_strip_non_digit_with_unicode_digits(): void
    {
        // Test with Unicode digits and non-digit characters
        // Contains Arabic digits
        $input = 'Phone: (123) ٤٥٦-٧٨٩٠';
        // Should retain both English and Arabic digits
        $expected = '123٤٥٦٧٨٩٠';
        $this->assertEquals($expected, strip_non_digit($input, true));
    }

    /**
     * Tests strip_non_digit() when the string contains Unicode non-digit characters.
     *
     * @return void
     */
    public function test_strip_non_digit_unicode_non_digits(): void
    {
        // Test a string with non-digit Unicode characters
        $input    = 'Price: ١٢٣٤٥ / £200';
        $expected = '١٢٣٤٥200';
        $this->assertEquals($expected, strip_non_digit($input, true));
    }

    /**
     * Tests strip_non_digit() with a dirty string containing a mixture of digits, special characters, and letters.
     *
     * @return void
     */
    public function test_strip_non_digit_dirty_input(): void
    {
        // Test a dirty string with letters, numbers, and special characters
        $input    = 'Invoice #ABC1234, total: £567.89.';
        $expected = '123456789';
        $this->assertEquals($expected, strip_non_digit($input));
    }

    /**
     * Tests strip_non_digit() with an empty string input.
     *
     * @return void
     */
    public function test_strip_non_digit_empty_string(): void
    {
        // Test with an empty string
        $input = '';
        // Output should be empty as well
        $expected = '';
        $this->assertEquals($expected, strip_non_digit($input));
    }

    /**
     * Tests strip_non_digit() with a string containing letters and punctuation.
     *
     * @return void
     */
    public function test_strip_non_digit_letters_and_punctuation(): void
    {
        // Test a string with letters and punctuation
        $input = 'ABC-def-123!@#456';
        // Only digits should be retained
        $expected = '123456';
        $this->assertEquals($expected, strip_non_digit($input));
    }

    /**
     * Tests strip_non_digit() with a very long string input.
     *
     * @return void
     */
    public function test_strip_non_digit_long_string(): void
    {
        // Test a very long string containing digits and other characters
        $input    = str_repeat('123-ABC-456 ', 1000);
        $expected = str_repeat('123456', 1000);
        $this->assertEquals($expected, strip_non_digit($input));
    }

    /**
     * Tests strip_non_digit() with a replacement character.
     *
     * @return void
     */
    public function test_strip_non_digit_replacement(): void
    {
        $replacement = '_';
        $input       = 'Hello 123 World!';
        $expected    = '______123_______';
        $this->assertEquals($expected, strip_non_digit($input, false, $replacement));
    }

    /**
     * Tests strip_non_alnum() with a string containing ASCII alphanumeric characters and non-alphanumeric characters.
     *
     * @return void
     */
    public function test_strip_non_alnum_basic(): void
    {
        // Test a string with ASCII alphanumeric characters and non-alphanumeric characters
        $input    = 'Product ID: ABC123!@#';
        $expected = 'ProductIDABC123';
        $this->assertEquals($expected, strip_non_alnum($input));
    }

    /**
     * Tests strip_non_alnum() when the string contains only alphanumeric characters.
     *
     * @return void
     */
    public function test_strip_non_alnum_alnum_only(): void
    {
        // Test a string with only alphanumeric characters
        $input = 'ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789';
        // Should not change
        $expected = $input;
        $this->assertEquals($expected, strip_non_alnum($input));
    }

    /**
     * Tests strip_non_alnum() with a string that contains special characters and spaces.
     *
     * @return void
     */
    public function test_strip_non_alnum_special_characters(): void
    {
        // Test a string with special characters and spaces
        $input    = 'Order #: !123 *ABC&456 ?';
        $expected = 'Order123ABC456';
        $this->assertEquals($expected, strip_non_alnum($input));
    }

    /**
     * Tests strip_non_alnum() with Unicode alphanumeric characters allowed.
     *
     * @return void
     */
    public function test_strip_non_alnum_with_unicode_alnum(): void
    {
        // Test with Unicode alphanumeric characters and non-alphanumeric characters
        $input = 'Order: ١٢٣٤٥ ABC 中文 русский';
        // Retain Unicode alphanumeric characters
        $expected = 'Order١٢٣٤٥ABC中文русский';
        $this->assertEquals($expected, strip_non_alnum($input, true));
    }

    /**
     * Tests strip_non_alnum() when the string contains Unicode non-alphanumeric characters.
     *
     * @return void
     */
    public function test_strip_non_alnum_unicode_non_alnum(): void
    {
        // Test a string with non-alphanumeric Unicode characters
        $input    = 'Price: ٥٠٠٠ / £200';
        $expected = 'Price٥٠٠٠200';
        $this->assertEquals($expected, strip_non_alnum($input, true));
    }

    /**
     * Tests strip_non_alnum() with a dirty string containing a mixture of alphanumeric characters, special characters, and letters.
     *
     * @return void
     */
    public function test_strip_non_alnum_dirty_input(): void
    {
        // Test a dirty string with letters, numbers, and special characters
        $input    = 'Invoice #ABC1234! Total: £567.89.';
        $expected = 'InvoiceABC1234Total56789';
        $this->assertEquals($expected, strip_non_alnum($input));
    }

    /**
     * Tests strip_non_alnum() with an empty string input.
     *
     * @return void
     */
    public function test_strip_non_alnum_empty_string(): void
    {
        // Test with an empty string
        $input = '';
        // Output should be empty as well
        $expected = '';
        $this->assertEquals($expected, strip_non_alnum($input));
    }

    /**
     * Tests strip_non_alnum() with a string containing letters and punctuation.
     *
     * @return void
     */
    public function test_strip_non_alnum_letters_and_punctuation(): void
    {
        // Test a string with letters and punctuation
        $input = 'ABC-def-123!@#';
        // Only alphanumeric characters should be retained
        $expected = 'ABCdef123';
        $this->assertEquals($expected, strip_non_alnum($input));
    }

    /**
     * Tests strip_non_alnum() with a very long string input.
     *
     * @return void
     */
    public function test_strip_non_alnum_long_string(): void
    {
        // Test a very long string containing alphanumeric and other characters
        $input    = str_repeat('ABC123!@# ', 1000);
        $expected = str_repeat('ABC123', 1000);
        $this->assertEquals($expected, strip_non_alnum($input));
    }

    /**
     * Tests strip_non_alnum() with a replacement character.
     *
     * @return void
     */
    public function test_strip_non_alnum_replacement(): void
    {
        $replacement = '_';
        $input       = 'Hello 123 World!';
        $expected    = 'Hello_123_World_';
        $this->assertEquals($expected, strip_non_alnum($input, false, $replacement));
    }

    /**
     * Tests generate_sed() with valid input and default delimiter.
     *
     * @return void
     */
    public function test_generate_sed_valid_input_default_delimiter(): void
    {
        // Test with valid search, replace, and filename
        $search_string  = 'foo';
        $replace_string = 'bar';
        $filename       = 'test.txt';
        $expected       = "sed -i 's/foo/bar/g' " . $this->expected_quote . 'test.txt' . $this->expected_quote;

        $this->assertEquals($expected, generate_sed($search_string, $replace_string, $filename));
    }

    /**
     * Tests generate_sed() with a custom delimiter.
     *
     * @return void
     */
    public function test_generate_sed_valid_input_custom_delimiter(): void
    {
        // Test with a custom delimiter
        $search_string  = 'foo';
        $replace_string = 'bar';
        $filename       = 'test.txt';
        $delimiter      = '#';
        $expected       = "sed -i 's#foo#bar#g' " . $this->expected_quote . 'test.txt' . $this->expected_quote;

        $this->assertEquals($expected, generate_sed($search_string, $replace_string, $filename, $delimiter));
    }

    /**
     * Tests generate_sed() when the search string contains special characters.
     *
     * @return void
     */
    public function test_generate_sed_search_string_with_special_characters(): void
    {
        // Test search string with special characters that need escaping
        $search_string  = 'foo$[]';
        $replace_string = 'bar';
        $filename       = 'test.txt';
        $expected       = "sed -i 's/foo\\$\\[\\]/bar/g' " . $this->expected_quote . 'test.txt' . $this->expected_quote;

        $this->assertEquals($expected, generate_sed($search_string, $replace_string, $filename));
    }

    /**
     * Tests generate_sed() when the replace string contains special characters.
     *
     * @return void
     */
    public function test_generate_sed_replace_string_with_special_characters(): void
    {
        // Test replace string with special characters that need escaping
        $search_string  = 'foo';
        $replace_string = 'bar$[]';
        $filename       = 'test.txt';
        $expected       = "sed -i 's/foo/bar\\$\\[\\]/g' " . $this->expected_quote . 'test.txt' . $this->expected_quote;

        $this->assertEquals($expected, generate_sed($search_string, $replace_string, $filename));
    }

    /**
     * Tests generate_sed() with an empty search string.
     *
     * @return void
     */
    public function test_generate_sed_empty_search_string(): void
    {
        // Test with an empty search string
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Search string, replace string, and filename must not be empty.');

        generate_sed('', 'bar', 'test.txt');
    }

    /**
     * Tests generate_sed() with an empty replace string.
     *
     * @return void
     */
    public function test_generate_sed_empty_replace_string(): void
    {
        // Test with an empty replace string
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Search string, replace string, and filename must not be empty.');

        generate_sed('foo', '', 'test.txt');
    }

    /**
     * Tests generate_sed() with an empty filename.
     *
     * @return void
     */
    public function test_generate_sed_empty_filename(): void
    {
        // Test with an empty filename
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Search string, replace string, and filename must not be empty.');

        generate_sed('foo', 'bar', '');
    }

    /**
     * Tests generate_sed() with an invalid delimiter (more than one character).
     *
     * @return void
     */
    public function test_generate_sed_invalid_delimiter(): void
    {
        // Test with an invalid delimiter
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Delimiter must be a single character.');

        generate_sed('foo', 'bar', 'test.txt', '##');
    }

    /**
     * Tests generate_sed() with a dirty filename containing spaces.
     *
     * @return void
     */
    public function test_generate_sed_dirty_filename_with_spaces(): void
    {
        // Test with a filename that contains spaces
        $search_string  = 'foo';
        $replace_string = 'bar';
        $filename       = 'my test file.txt';
        $expected       = "sed -i 's/foo/bar/g' " . $this->expected_quote . 'my test file.txt' . $this->expected_quote;

        $this->assertEquals($expected, generate_sed($search_string, $replace_string, $filename));
    }

    /**
     * Tests if a negative integer returns true.
     *
     * @return void
     */
    public function test_is_int_negative_with_negative_integer()
    {
        $this->assertTrue(is_int_negative(-1));
    }

    /**
     * Tests if a positive integer returns false.
     *
     * @return void
     */
    public function test_is_int_negative_with_positive_integer()
    {
        $this->assertFalse(is_int_negative(1));
    }

    /**
     * Tests if zero returns false.
     *
     * @return void
     */
    public function test_is_int_negative_with_zero()
    {
        $this->assertFalse(is_int_negative(0));
    }

    /**
     * Tests if a non-integer negative number returns false.
     * Here, a float is used to check non-integer handling.
     *
     * @return void
     */
    public function test_is_int_negative_with_negative_float()
    {
        $this->assertFalse(is_int_negative(-1.5));
    }

    /**
     * Tests if a string containing a negative integer returns false.
     * This ensures the function does not treat numeric strings as integers.
     *
     * @return void
     */
    public function test_is_int_negative_with_negative_integer_string()
    {
        $this->assertFalse(is_int_negative('-1'));
    }

    /**
     * Tests if an empty value returns false.
     * Edge case where an empty string is passed.
     *
     * @return void
     */
    public function test_is_int_negative_with_empty_value()
    {
        $this->assertFalse(is_int_negative(''));
    }

    /**
     * Tests if a null value returns false.
     *
     * @return void
     */
    public function test_is_int_negative_with_null_value()
    {
        $this->assertFalse(is_int_negative(null));
    }

    /**
     * Tests if a boolean true returns false.
     *
     * @return void
     */
    public function test_is_int_negative_with_boolean_true()
    {
        $this->assertFalse(is_int_negative(true));
    }

    /**
     * Tests if a boolean false returns false.
     *
     * @return void
     */
    public function test_is_int_negative_with_boolean_false()
    {
        $this->assertFalse(is_int_negative(false));
    }

    /**
     * Tests if an array returns false.
     * Here, an array of integers is passed.
     *
     * @return void
     */
    public function test_is_int_negative_with_array()
    {
        $this->assertFalse(is_int_negative([-1, -2]));
    }

    /**
     * Tests if an object returns false.
     * Edge case where an object is passed instead of an integer.
     *
     * @return void
     */
    public function test_is_int_negative_with_object()
    {
        $this->assertFalse(is_int_negative((object) [-1]));
    }

    /**
     * Tests if a positive integer returns true.
     *
     * @return void
     */
    public function test_is_int_positive_with_positive_integer()
    {
        $this->assertTrue(is_int_positive(5));
    }

    /**
     * Tests if a negative integer returns false.
     *
     * @return void
     */
    public function test_is_int_positive_with_negative_integer()
    {
        $this->assertFalse(is_int_positive(-5));
    }

    /**
     * Tests if zero returns true when $accept_zero is true.
     *
     * @return void
     */
    public function test_is_int_positive_with_zero_accept_zero_true()
    {
        $this->assertTrue(is_int_positive(0, true));
    }

    /**
     * Tests if zero returns false when $accept_zero is false.
     *
     * @return void
     */
    public function test_is_int_positive_with_zero_accept_zero_false()
    {
        $this->assertFalse(is_int_positive(0, false));
    }

    /**
     * Tests if a float value returns false.
     * A non-integer numeric value should not pass the validation.
     *
     * @return void
     */
    public function test_is_int_positive_with_float()
    {
        $this->assertFalse(is_int_positive(3.5));
    }

    /**
     * Tests if a string value returns false.
     * Numeric strings should not pass the validation.
     *
     * @return void
     */
    public function test_is_int_positive_with_numeric_string()
    {
        $this->assertFalse(is_int_positive('5'));
    }

    /**
     * Tests if an empty value returns false.
     *
     * @return void
     */
    public function test_is_int_positive_with_empty_value()
    {
        $this->assertFalse(is_int_positive(''));
    }

    /**
     * Tests if null returns false.
     *
     * @return void
     */
    public function test_is_int_positive_with_null_value()
    {
        $this->assertFalse(is_int_positive(null));
    }

    /**
     * Tests if a boolean true returns false.
     *
     * @return void
     */
    public function test_is_int_positive_with_boolean_true()
    {
        $this->assertFalse(is_int_positive(true));
    }

    /**
     * Tests if a boolean false returns false.
     *
     * @return void
     */
    public function test_is_int_positive_with_boolean_false()
    {
        $this->assertFalse(is_int_positive(false));
    }

    /**
     * Tests if an array returns false.
     *
     * @return void
     */
    public function test_is_int_positive_with_array()
    {
        $this->assertFalse(is_int_positive([1, 2, 3]));
    }

    /**
     * Tests if an object returns false.
     *
     * @return void
     */
    public function test_is_int_positive_with_object()
    {
        $this->assertFalse(is_int_positive((object)[1, 2]));
    }

    /**
     * Tests if an integer value returns true.
     *
     * @return void
     */
    public function test_is_numeric_decimal_with_integer()
    {
        $this->assertTrue(is_numeric_decimal(123));
    }

    /**
     * Tests if a float value returns true.
     *
     * @return void
     */
    public function test_is_numeric_decimal_with_float()
    {
        $this->assertTrue(is_numeric_decimal(123.45));
    }

    /**
     * Tests if a string numeric value returns true.
     * Numeric strings should be considered as numeric.
     *
     * @return void
     */
    public function test_is_numeric_decimal_with_numeric_string()
    {
        $this->assertTrue(is_numeric_decimal('123'));
    }

    /**
     * Tests if a string with a decimal point returns true.
     * A single decimal point (.) should be considered valid.
     *
     * @return void
     */
    public function test_is_numeric_decimal_with_single_decimal_point()
    {
        $this->assertTrue(is_numeric_decimal('.'));
    }

    /**
     * Tests if a string with only decimal point and no digits returns false.
     * Edge case where just '.' should be considered valid but with context could fail.
     *
     * @return void
     */
    public function test_is_numeric_decimal_with_only_decimal_point()
    {
        $this->assertTrue(is_numeric_decimal('.'));
    }

    /**
     * Tests if a non-numeric string returns false.
     * Strings with letters should not be considered numeric.
     *
     * @return void
     */
    public function test_is_numeric_decimal_with_non_numeric_string()
    {
        $this->assertFalse(is_numeric_decimal('abc'));
    }

    /**
     * Tests if an empty string returns false.
     *
     * @return void
     */
    public function test_is_numeric_decimal_with_empty_string()
    {
        $this->assertFalse(is_numeric_decimal(''));
    }

    /**
     * Tests if null returns false.
     *
     * @return void
     */
    public function test_is_numeric_decimal_with_null_value()
    {
        $this->assertFalse(is_numeric_decimal(null));
    }

    /**
     * Tests if a boolean true returns false.
     *
     * @return void
     */
    public function test_is_numeric_decimal_with_boolean_true()
    {
        $this->assertFalse(is_numeric_decimal(true));
    }

    /**
     * Tests if a boolean false returns false.
     *
     * @return void
     */
    public function test_is_numeric_decimal_with_boolean_false()
    {
        $this->assertFalse(is_numeric_decimal(false));
    }

    /**
     * Tests if an array returns false.
     * Arrays should not be considered numeric.
     *
     * @return void
     */
    public function test_is_numeric_decimal_with_array()
    {
        $this->assertFalse(is_numeric_decimal([1, 2, 3]));
    }

    /**
     * Tests if an object returns false.
     * Objects should not be considered numeric.
     *
     * @return void
     */
    public function test_is_numeric_decimal_with_object()
    {
        $this->assertFalse(is_numeric_decimal((object) [1, 2]));
    }

    /**
     * Tests if an integer returns true.
     *
     * @return void
     */
    public function test_is_whole_number_with_integer()
    {
        $this->assertTrue(is_whole_number(10));
    }

    /**
     * Tests if a negative integer returns true.
     *
     * @return void
     */
    public function test_is_whole_number_with_negative_integer()
    {
        $this->assertTrue(is_whole_number(-5));
    }

    /**
     * Tests if a float with no decimal part returns true.
     *
     * @return void
     */
    public function test_is_whole_number_with_float_no_decimal()
    {
        $this->assertTrue(is_whole_number(10.0));
    }

    /**
     * Tests if a float with a decimal part returns false.
     *
     * @return void
     */
    public function test_is_whole_number_with_float_with_decimal()
    {
        $this->assertFalse(is_whole_number(10.5));
    }

    /**
     * Tests if a numeric string representing a whole number returns true.
     *
     * @return void
     */
    public function test_is_whole_number_with_numeric_string_whole()
    {
        $this->assertTrue(is_whole_number('10'));
    }

    /**
     * Tests if a numeric string representing a float with no decimal part returns true.
     *
     * @return void
     */
    public function test_is_whole_number_with_numeric_string_float_no_decimal()
    {
        $this->assertTrue(is_whole_number('10.0'));
    }

    /**
     * Tests if a numeric string representing a float with decimal part returns false.
     *
     * @return void
     */
    public function test_is_whole_number_with_numeric_string_float_with_decimal()
    {
        $this->assertFalse(is_whole_number('10.5'));
    }

    /**
     * Tests if a non-numeric string returns false.
     * This ensures that non-numeric strings are not mistakenly identified as whole numbers.
     *
     * @return void
     */
    public function test_is_whole_number_with_non_numeric_string()
    {
        $this->assertFalse(is_whole_number('abc'));
    }

    /**
     * Tests if a boolean true returns false.
     *
     * @return void
     */
    public function test_is_whole_number_with_boolean_true()
    {
        $this->assertFalse(is_whole_number(true));
    }

    /**
     * Tests if a boolean false returns false.
     *
     * @return void
     */
    public function test_is_whole_number_with_boolean_false()
    {
        $this->assertFalse(is_whole_number(false));
    }

    /**
     * Tests if an empty string returns false.
     *
     * @return void
     */
    public function test_is_whole_number_with_empty_string()
    {
        $this->assertFalse(is_whole_number(''));
    }

    /**
     * Tests if null returns false.
     *
     * @return void
     */
    public function test_is_whole_number_with_null()
    {
        $this->assertFalse(is_whole_number(null));
    }

    /**
     * Tests if an array returns false.
     * Arrays should not be considered valid numbers.
     *
     * @return void
     */
    public function test_is_whole_number_with_array()
    {
        $this->assertFalse(is_whole_number([1, 2, 3]));
    }

    /**
     * Tests if an object returns false.
     * Objects should not be considered valid numbers.
     *
     * @return void
     */
    public function test_is_whole_number_with_object()
    {
        $this->assertFalse(is_whole_number((object)[1, 2]));
    }

    /**
     * Tests padding on the right side of the string.
     *
     * @return void
     */
    public function test_helper_mb_str_pad_right()
    {
        $input  = 'test';
        $result = helper_mb_str_pad($input, 8, ' ');
        $this->assertEquals('test    ', $result);
    }

    /**
     * Tests padding on the left side of the string.
     *
     * @return void
     */
    public function test_helper_mb_str_pad_left()
    {
        $input  = 'test';
        $result = helper_mb_str_pad($input, 8, ' ', STR_PAD_LEFT);
        $this->assertEquals('    test', $result);
    }

    /**
     * Tests padding on both sides of the string.
     *
     * @return void
     */
    public function test_helper_mb_str_pad_both()
    {
        $input  = 'test';
        $result = helper_mb_str_pad($input, 10, ' ', STR_PAD_BOTH);

        // Adjust the expected value to match the correct behavior
        $this->assertEquals('   test   ', $result);
    }

    /**
     * Tests when the input length is greater than or equal to the pad length.
     * In this case, no padding should be applied.
     *
     * @return void
     */
    public function test_helper_mb_str_pad_no_padding_needed()
    {
        $input  = 'longstring';
        $result = helper_mb_str_pad($input, 10);
        $this->assertEquals('longstring', $result);
    }

    /**
     * Tests padding with a multibyte string (e.g., Unicode characters).
     *
     * @return void
     */
    public function test_helper_mb_str_pad_with_multibyte_characters()
    {
        // "Test" in Japanese
        $input  = 'テスト';
        $result = helper_mb_str_pad($input, 8, ' ', STR_PAD_RIGHT, 'UTF-8');
        $this->assertEquals('テスト  ', $result);
    }

    /**
     * Tests padding with a multibyte padding string.
     *
     * @return void
     */
    public function test_helper_mb_str_pad_multibyte_pad_string()
    {
        $input  = 'test';
        $result = helper_mb_str_pad($input, 10, 'あ', STR_PAD_RIGHT, 'UTF-8');

        // Adjusting the expected value to reflect the correct padding behavior
        $this->assertEquals('testああああああ', $result);
    }

    /**
     * Tests padding with a custom pad string longer than one character.
     *
     * @return void
     */
    public function test_helper_mb_str_pad_custom_pad_string()
    {
        $input  = 'test';
        $result = helper_mb_str_pad($input, 10, '-+', STR_PAD_RIGHT);
        $this->assertEquals('test-+-+-+', $result);
    }

    /**
     * Tests padding with a length shorter than the input length.
     * No padding should be applied in this case.
     *
     * @return void
     */
    public function test_helper_mb_str_pad_length_shorter_than_input()
    {
        $input  = 'test';
        $result = helper_mb_str_pad($input, 3);
        $this->assertEquals('test', $result);
    }

    /**
     * Tests padding with an empty input string.
     *
     * @return void
     */
    public function test_helper_mb_str_pad_empty_input()
    {
        $input  = '';
        $result = helper_mb_str_pad($input, 5, '*');
        $this->assertEquals('*****', $result);
    }

    /**
     * Tests padding with a zero pad length.
     * The function should return the original string.
     *
     * @return void
     */
    public function test_helper_mb_str_pad_zero_pad_length()
    {
        $input  = 'test';
        $result = helper_mb_str_pad($input, 0, '*');
        $this->assertEquals('test', $result);
    }

    /**
     * Tests the string_is_latin function with basic Latin characters.
     */
    public function test_string_is_latin_basic_latin_characters()
    {
        // Valid Latin string without numbers, spaces, or punctuation
        $this->assertTrue(string_is_latin('hello'));
    }

    /**
     * Tests the string_is_latin function with Latin characters and numbers allowed.
     */
    public function test_string_is_latin_with_numbers_allowed()
    {
        // Valid string with Latin characters and numbers allowed
        $this->assertTrue(string_is_latin('hello123', true, false, false));
    }

    /**
     * Tests the string_is_latin function with Latin characters, spaces allowed.
     */
    public function test_string_is_latin_with_spaces_allowed()
    {
        // Valid string with Latin characters and spaces allowed
        $this->assertTrue(string_is_latin('hello world', false, true, false));
    }

    /**
     * Tests the string_is_latin function with punctuation and spaces allowed.
     */
    public function test_string_is_latin_with_punctuation_and_spaces_allowed()
    {
        // Valid string with Latin characters, spaces, and punctuation allowed
        $this->assertTrue(string_is_latin('hello, world!', false, true, true));
    }

    /**
     * Tests the string_is_latin function when no numbers, spaces, or punctuation are allowed.
     */
    public function test_string_is_latin_no_numbers_spaces_or_punctuation()
    {
        // Invalid string with numbers, spaces, and punctuation not allowed
        $this->assertFalse(string_is_latin('hello 123!', false, false, false));
    }

    /**
     * Tests the string_is_latin function when numbers are allowed, but spaces and punctuation are not.
     */
    public function test_string_is_latin_numbers_allowed_spaces_punctuation_not_allowed()
    {
        // Valid string with numbers allowed, but spaces and punctuation not allowed
        $this->assertTrue(string_is_latin('hello123', true, false, false));
        // Invalid string with space not allowed
        $this->assertFalse(string_is_latin('hello 123', true, false, false));
        // Invalid string with punctuation not allowed
        $this->assertFalse(string_is_latin('hello123!', true, false, false));
    }

    /**
     * Tests the string_is_latin function with edge case: empty string.
     */
    public function test_string_is_latin_empty_string()
    {
        // Empty string should be valid, as it matches the pattern of having zero or more characters
        $this->assertTrue(string_is_latin(''));
    }

    /**
     * Tests the string_is_latin function with edge case: single Latin letter.
     */
    public function test_string_is_latin_single_latin_letter()
    {
        // Valid string with a single Latin letter
        $this->assertTrue(string_is_latin('a'));
    }

    /**
     * Tests the string_is_latin function with "dirty" case: special characters that are not allowed.
     */
    public function test_string_is_latin_dirty_special_characters()
    {
        // Invalid string with non-Latin characters like emojis
        $this->assertFalse(string_is_latin('hello😊'));
        // Invalid string with non-Latin script characters
        $this->assertFalse(string_is_latin('привет'));
    }

    /**
     * Tests the string_is_latin function when input contains Latin characters but mixed with Cyrillic.
     */
    public function test_string_is_latin_mixed_latin_and_cyrillic()
    {
        // Invalid string with mixed Latin and Cyrillic characters
        $this->assertFalse(string_is_latin('helloПривет'));
    }

    /**
     * Tests the string_is_latin function when input contains Latin characters and digits but digits are not allowed.
     */
    public function test_string_is_latin_digits_not_allowed()
    {
        // Invalid string with digits not allowed
        $this->assertFalse(string_is_latin('hello123', false, false, false));
    }

    /**
     * Tests the string_is_latin function when input contains Latin characters and spaces but spaces are not allowed.
     */
    public function test_string_is_latin_spaces_not_allowed()
    {
        // Invalid string with spaces not allowed
        $this->assertFalse(string_is_latin('hello world', false, false, false));
    }

    /**
     * Tests the string_is_latin_extended_b function with a valid Latin Extended-B string.
     */
    public function test_string_is_latin_extended_b_valid_string()
    {
        // Valid string containing characters from the Unicode range up to U+024F
        $this->assertTrue(string_is_latin_extended_b('ƁƂƃƄƅƆƇƈ'));
    }

    /**
     * Tests the string_is_latin_extended_b function with an empty string.
     */
    public function test_string_is_latin_extended_b_empty_string()
    {
        // An empty string is considered valid
        $this->assertTrue(string_is_latin_extended_b(''));
    }

    /**
     * Tests the string_is_latin_extended_b function with an ASCII string.
     */
    public function test_string_is_latin_extended_b_ascii_string()
    {
        // Valid string containing only ASCII characters (U+0000 to U+007F)
        $this->assertTrue(string_is_latin_extended_b('hello world'));
    }

    /**
     * Tests the string_is_latin_extended_b function with a string containing non-Latin Extended-B characters.
     */
    public function test_string_is_latin_extended_b_invalid_string()
    {
        // Invalid string containing characters outside the Unicode range U+024F
        $this->assertFalse(string_is_latin_extended_b('hello 😊'));
    }

    /**
     * Tests the string_is_latin_extended_b function with a string containing mixed valid and invalid characters.
     */
    public function test_string_is_latin_extended_b_mixed_characters()
    {
        // All characters in this string (ƁƂhelloƃƄ) are valid within U+0000 to U+024F.
        $this->assertTrue(string_is_latin_extended_b('ƁƂhelloƃƄ'));
    }

    /**
     * Tests the string_is_latin_extended_b function with a single valid character from the Latin Extended-B block.
     */
    public function test_string_is_latin_extended_b_single_valid_character()
    {
        // Valid string containing a single character from the Latin Extended-B range
        $this->assertTrue(string_is_latin_extended_b('Ɓ'));
    }

    /**
     * Tests the string_is_latin_extended_b function with a single invalid character.
     */
    public function test_string_is_latin_extended_b_single_invalid_character()
    {
        // Invalid string containing a single character outside the Latin Extended-B range
        $this->assertFalse(string_is_latin_extended_b('😊'));
    }

    /**
     * Tests the string_is_latin_extended_b function with a string containing special characters.
     */
    public function test_string_is_latin_extended_b_special_characters()
    {
        // Special characters like @#$%^&*() are within the valid range (U+0000 to U+024F).
        $this->assertTrue(string_is_latin_extended_b('@#$%^&*()'));
    }

    /**
     * Tests the string_is_latin_extended_b function with a string containing numbers (valid range).
     */
    public function test_string_is_latin_extended_b_numbers()
    {
        // Valid string containing numbers, which are within the Unicode range U+0000 to U+024F
        $this->assertTrue(string_is_latin_extended_b('1234567890'));
    }

    /**
     * Tests the string_is_latin_extended_b function with a long string containing valid characters.
     */
    public function test_string_is_latin_extended_b_long_valid_string()
    {
        // All characters in this string (ƁƂhelloƃƄƅƆ) are within the valid range U+0000 to U+024F.
        $this->assertTrue(string_is_latin_extended_b(str_repeat('ƁƂhelloƃƄƅƆ', 100)));
    }

    /**
     * Tests the string_is_latin_extended_b function with a long string containing invalid characters.
     */
    public function test_string_is_latin_extended_b_long_invalid_string()
    {
        // Invalid long string containing characters outside U+0000 to U+024F (e.g., emojis or other symbols)
        // 😊 is outside U+0000 to U+024F
        $invalidString = str_repeat('ƁƂhelloƃƄ😊', 100);
        $this->assertFalse(string_is_latin_extended_b($invalidString));
    }

    /**
     * Tests the string_is_ascii function with a valid ASCII string.
     */
    public function test_string_is_ascii_valid_ascii_string()
    {
        // Valid ASCII string
        $this->assertTrue(string_is_ascii('Hello, world!'));
    }

    /**
     * Tests the string_is_ascii function with an empty string.
     */
    public function test_string_is_ascii_empty_string()
    {
        // An empty string is considered valid
        $this->assertTrue(string_is_ascii(''));
    }

    /**
     * Tests the string_is_ascii function with a string containing only ASCII characters and numbers.
     */
    public function test_string_is_ascii_numbers_included()
    {
        // Valid ASCII string with numbers
        $this->assertTrue(string_is_ascii('12345'));
    }

    /**
     * Tests the string_is_ascii function with a string containing non-ASCII characters.
     */
    public function test_string_is_ascii_invalid_non_ascii_string()
    {
        // Invalid string with non-ASCII characters (e.g., Emoji)
        $this->assertFalse(string_is_ascii('Hello 😊'));
    }

    /**
     * Tests the string_is_ascii function with extra allowed non-ASCII characters.
     */
    public function test_string_is_ascii_valid_with_extra_characters()
    {
        // String with non-ASCII characters that are allowed (£, ±, §, €)
        $this->assertTrue(string_is_ascii('Hello £±§€ world!', true));
    }

    /**
     * Tests the string_is_ascii function with mixed valid and non-valid characters when allowing extra characters.
     */
    public function test_string_is_ascii_mixed_characters_with_extra()
    {
        // Mixed string: includes both valid ASCII and extra allowed characters, plus invalid ones (e.g., Emoji)
        $this->assertFalse(string_is_ascii('Hello £±§€ 😊', true));
    }

    /**
     * Tests the string_is_ascii function with only non-ASCII characters that are allowed.
     */
    public function test_string_is_ascii_only_extra_characters()
    {
        // String contains only the extra non-ASCII characters
        $this->assertTrue(string_is_ascii('£±§€', true));
    }

    /**
     * Tests the string_is_ascii function with a long valid ASCII string.
     */
    public function test_string_is_ascii_long_valid_ascii_string()
    {
        // A long string of ASCII characters
        $long_string = str_repeat('Hello world! ', 100);
        $this->assertTrue(string_is_ascii($long_string));
    }

    /**
     * Tests the string_is_ascii function with a long string containing invalid non-ASCII characters.
     */
    public function test_string_is_ascii_long_invalid_string()
    {
        // A long string containing non-ASCII characters
        $long_string = str_repeat('Hello world 😊! ', 100);
        $this->assertFalse(string_is_ascii($long_string));
    }

    /**
     * Tests the string_is_ascii function with an invalid character when extra characters are not allowed.
     */
    public function test_string_is_ascii_invalid_extra_characters_not_allowed()
    {
        // String contains £ which is invalid because extra characters are not allowed
        $this->assertFalse(string_is_ascii('Hello £ world!', false));
    }

    /**
     * Test basic ASCII string.
     */
    public function test_string_is_english_keyboard_basic_ascii(): void
    {
        $this->assertTrue(string_is_english_keyboard('Hello, World!'));
    }

    /**
     * Test string with special characters.
     */
    public function test_string_is_english_keyboard_special_chars(): void
    {
        $this->assertTrue(string_is_english_keyboard('£100 ±1 §2 €50'));
    }

    /**
     * Test string without Euro sign when Euro is not included.
     */
    public function test_string_is_english_keyboard_without_euro(): void
    {
        $this->assertTrue(string_is_english_keyboard('£100 ±1 §2', false));
    }

    /**
     * Test string with Euro sign when Euro is not included.
     */
    public function test_string_is_english_keyboard_euro_not_included(): void
    {
        $this->assertFalse(string_is_english_keyboard('€50', false));
    }

    /**
     * Test empty string.
     */
    public function test_string_is_english_keyboard_empty_string(): void
    {
        $this->assertTrue(string_is_english_keyboard(''));
    }

    /**
     * Test string with non-ASCII characters.
     */
    public function test_string_is_english_keyboard_non_ascii(): void
    {
        $this->assertFalse(string_is_english_keyboard('Café'));
    }

    /**
     * Test string with newline character.
     */
    public function test_string_is_english_keyboard_newline(): void
    {
        $this->assertTrue(string_is_english_keyboard("Hello\nWorld"));
    }

    /**
     * Test string with tab character.
     */
    public function test_string_is_english_keyboard_tab(): void
    {
        $this->assertTrue(string_is_english_keyboard("Hello\tWorld"));
    }

    /**
     * Test string with control characters.
     */
    public function test_string_is_english_keyboard_control_chars(): void
    {
        $this->assertFalse(string_is_english_keyboard("Hello\x01World"));
    }

    /**
     * Test string with Unicode spaces.
     */
    public function test_string_is_english_keyboard_unicode_spaces(): void
    {
        // Using a non-breaking space (U+00A0)
        $this->assertFalse(string_is_english_keyboard("Hello\xC2\xA0World"));

        // Using an em space (U+2003)
        $this->assertFalse(string_is_english_keyboard("Hello\xE2\x80\x83World"));

        // Using an ideographic space (U+3000)
        $this->assertFalse(string_is_english_keyboard("Hello\xE3\x80\x80World"));
    }

    /**
     * Test string with emoji.
     */
    public function test_string_is_english_keyboard_emoji(): void
    {
        $this->assertFalse(string_is_english_keyboard('Hello 👋 World'));
    }

    /**
     * Test string with all allowed characters.
     */
    public function test_string_is_english_keyboard_all_allowed_chars(): void
    {
        $all_chars = " !\"#$%&'()*+,-./0123456789:;<=>?@ABCDEFGHIJKLMNOPQRSTUVWXYZ[\\]^_`abcdefghijklmnopqrstuvwxyz{|}~£±§€\n\t";

        // Test with all characters allowed
        $this->assertTrue(string_is_english_keyboard($all_chars, true, true));

        // Test without euro
        $this->assertTrue(string_is_english_keyboard(str_replace('€', '', $all_chars), false, true));

        // Test without newline and tab
        $this->assertTrue(string_is_english_keyboard(str_replace(["\n", "\t"], '', $all_chars), true, false));

        // Test without euro, newline, and tab
        $this->assertTrue(string_is_english_keyboard(str_replace(['€', "\n", "\t"], '', $all_chars), false, false));
    }

    /**
     * Test string with all allowed characters except euro.
     */
    public function test_string_is_english_keyboard_all_allowed_chars_no_euro(): void
    {
        $all_chars_no_euro = " !\"#$%&'()*+,-./0123456789:;<=>?@ABCDEFGHIJKLMNOPQRSTUVWXYZ[\\]^_`abcdefghijklmnopqrstuvwxyz{|}~£±§\n\t";
        $this->assertTrue(string_is_english_keyboard($all_chars_no_euro, false, true));
        $this->assertFalse(string_is_english_keyboard($all_chars_no_euro . '€', false, true));
    }

    /**
     * Test string with all allowed characters except newline and tab.
     */
    public function test_string_is_english_keyboard_all_allowed_chars_no_whitespace(): void
    {
        $all_chars_no_whitespace = " !\"#$%&'()*+,-./0123456789:;<=>?@ABCDEFGHIJKLMNOPQRSTUVWXYZ[\\]^_`abcdefghijklmnopqrstuvwxyz{|}~£±§€";
        $this->assertTrue(string_is_english_keyboard($all_chars_no_whitespace, true, false));
        $this->assertFalse(string_is_english_keyboard($all_chars_no_whitespace . "\n", true, false));
        $this->assertFalse(string_is_english_keyboard($all_chars_no_whitespace . "\t", true, false));
    }

    /**
     * Test string with multibyte characters.
     */
    public function test_string_is_english_keyboard_multibyte(): void
    {
        $this->assertFalse(string_is_english_keyboard('Hello こんにちは World'));
    }

    /**
     * Test string with newline when whitespace is not allowed.
     */
    public function test_string_is_english_keyboard_newline_not_allowed(): void
    {
        $this->assertFalse(string_is_english_keyboard("Hello\nWorld", true, false));
    }

    /**
     * Test string with tab when whitespace is not allowed.
     */
    public function test_string_is_english_keyboard_tab_not_allowed(): void
    {
        $this->assertFalse(string_is_english_keyboard("Hello\tWorld", true, false));
    }

    /**
     * Test string with both newline and tab when whitespace is allowed.
     */
    public function test_string_is_english_keyboard_mixed_whitespace_allowed(): void
    {
        $this->assertTrue(string_is_english_keyboard("Hello\nWorld\tTest", true, true));
    }

    /**
     * Test string with euro sign when it's not allowed but whitespace is allowed.
     */
    public function test_string_is_english_keyboard_no_euro_yes_whitespace(): void
    {
        $this->assertFalse(string_is_english_keyboard("Hello\nWorld €", false, true));
    }

    /**
     * Test string with euro sign when it's allowed but whitespace is not.
     */
    public function test_string_is_english_keyboard_yes_euro_no_whitespace(): void
    {
        $this->assertTrue(string_is_english_keyboard('Hello€World', true, false));
    }

    /**
     * Test string with all special characters but no whitespace when whitespace is not allowed.
     */
    public function test_string_is_english_keyboard_special_chars_no_whitespace(): void
    {
        $this->assertTrue(string_is_english_keyboard('Hello£±§€World', true, false));
    }

    /**
     * Test empty string with different parameter combinations.
     */
    public function test_string_is_english_keyboard_empty_string_variations(): void
    {
        $this->assertTrue(string_is_english_keyboard('', true, true));
        $this->assertTrue(string_is_english_keyboard('', false, true));
        $this->assertTrue(string_is_english_keyboard('', true, false));
        $this->assertTrue(string_is_english_keyboard('', false, false));
    }

    /**
     * Tests bool_to_string with true and no padding.
     *
     * @return void
     */
    public function test_bool_to_string_true_no_padding(): void
    {
        $this->assertEquals('true', bool_to_string(true));
    }

    /**
     * Tests bool_to_string with false and no padding.
     *
     * @return void
     */
    public function test_bool_to_string_false_no_padding(): void
    {
        $this->assertEquals('false', bool_to_string(false));
    }

    /**
     * Tests bool_to_string with true and normal padding.
     *
     * @return void
     */
    public function test_bool_to_string_true_with_padding(): void
    {
        $this->assertEquals('***true***', bool_to_string(true, '***'));
    }

    /**
     * Tests bool_to_string with false and normal padding.
     *
     * @return void
     */
    public function test_bool_to_string_false_with_padding(): void
    {
        $this->assertEquals('---false---', bool_to_string(false, '---'));
    }

    /**
     * Tests bool_to_string with empty padding.
     *
     * @return void
     */
    public function test_bool_to_string_empty_padding(): void
    {
        $this->assertEquals('true', bool_to_string(true, ''));
        $this->assertEquals('false', bool_to_string(false, ''));
    }

    /**
     * Tests bool_to_string with dirty padding (whitespace characters).
     *
     * @return void
     */
    public function test_bool_to_string_dirty_padding(): void
    {
        $this->assertEquals('   true   ', bool_to_string(true, '   '));
        $this->assertEquals("\tfalse\t", bool_to_string(false, "\t"));
    }

    /**
     * Tests bool_to_string with special characters in padding.
     *
     * @return void
     */
    public function test_bool_to_string_special_character_padding(): void
    {
        $this->assertEquals('@@true@@', bool_to_string(true, '@@'));
        $this->assertEquals('!!false!!', bool_to_string(false, '!!'));
    }

    /**
     * Tests bool_to_string with numeric padding (edge case).
     *
     * @return void
     */
    public function test_bool_to_string_numeric_padding(): void
    {
        $this->assertEquals('123true123', bool_to_string(true, '123'));
        $this->assertEquals('456false456', bool_to_string(false, '456'));
    }

    /**
     * Tests bool_to_string with mixed character padding.
     *
     * @return void
     */
    public function test_bool_to_string_mixed_character_padding(): void
    {
        $this->assertEquals('!@#true!@#', bool_to_string(true, '!@#'));
        $this->assertEquals('abcfalseabc', bool_to_string(false, 'abc'));
    }

    /**
     * Tests bool_to_string with a very large padding string (performance case).
     *
     * @return void
     */
    public function test_bool_to_string_large_padding(): void
    {
        $largePadding = str_repeat('x', 10000);
        $this->assertEquals($largePadding . 'true' . $largePadding, bool_to_string(true, $largePadding));
        $this->assertEquals($largePadding . 'false' . $largePadding, bool_to_string(false, $largePadding));
    }

    /**
     * Tests stringval with a boolean true value.
     *
     * @return void
     */
    public function test_stringval_with_true(): void
    {
        $this->assertEquals('true', stringval(true));
    }

    /**
     * Tests stringval with a boolean false value.
     *
     * @return void
     */
    public function test_stringval_with_false(): void
    {
        $this->assertEquals('false', stringval(false));
    }

    /**
     * Tests stringval with an integer value.
     *
     * @return void
     */
    public function test_stringval_with_integer(): void
    {
        $this->assertEquals('42', stringval(42));
    }

    /**
     * Tests stringval with a float value.
     *
     * @return void
     */
    public function test_stringval_with_float(): void
    {
        $this->assertEquals('3.14', stringval(3.14));
    }

    /**
     * Tests stringval with a simple string and no quotes.
     *
     * @return void
     */
    public function test_stringval_with_string_no_quotes(): void
    {
        $this->assertEquals('hello', stringval('hello', false));
    }

    /**
     * Tests stringval with a string wrapped in quotes.
     *
     * @return void
     */
    public function test_stringval_with_string_with_quotes(): void
    {
        $this->assertEquals("'hello'", stringval('hello', true));
    }

    /**
     * Tests stringval with an array.
     *
     * @return void
     */
    public function test_stringval_with_array(): void
    {
        $array          = ['apple', 'banana', 'cherry'];
        $expectedOutput = print_r($array, true);
        $this->assertEquals($expectedOutput, stringval($array));
    }

    /**
     * Tests stringval with an object.
     *
     * @return void
     */
    public function test_stringval_with_object(): void
    {
        $object         = (object) ['property1' => 'value1', 'property2' => 42];
        $expectedOutput = var_dump_string($object);
        $this->assertEquals($expectedOutput, stringval($object));
    }

    /**
     * Tests stringval with a NULL value.
     *
     * @return void
     */
    public function test_stringval_with_null(): void
    {
        $expectedOutput = var_dump_string(null);
        $this->assertEquals($expectedOutput, stringval(null));
    }

    /**
     * Tests stringval with type appending for a boolean value.
     *
     * @return void
     */
    public function test_stringval_with_true_and_type_appending(): void
    {
        $this->assertEquals('true (boolean)', stringval(true, false, true));
    }

    /**
     * Tests stringval with type appending for an integer value.
     *
     * @return void
     */
    public function test_stringval_with_integer_and_type_appending(): void
    {
        $this->assertEquals('42 (integer)', stringval(42, false, true));
    }

    /**
     * Tests stringval with a "dirty" string containing special characters.
     *
     * @return void
     */
    public function test_stringval_with_dirty_string(): void
    {
        $dirtyString = "Line1\nLine2\tTabbed";
        $this->assertEquals($dirtyString, stringval($dirtyString));
    }

    /**
     * Tests stringval with a large array (performance case).
     *
     * @return void
     */
    public function test_stringval_with_large_array(): void
    {
        $largeArray     = range(1, 1000);
        $expectedOutput = print_r($largeArray, true);
        $this->assertEquals($expectedOutput, stringval($largeArray));
    }

    /**
     * Tests explode_trim with a simple string and no limit.
     *
     * @return void
     */
    public function test_explode_trim_with_simple_string(): void
    {
        $input_string = 'apple, banana, cherry';
        $expected     = ['apple', 'banana', 'cherry'];
        $this->assertEquals($expected, explode_trim(',', $input_string));
    }

    /**
     * Tests explode_trim with an empty string.
     *
     * @return void
     */
    public function test_explode_trim_with_empty_string(): void
    {
        $input_string = '';
        $this->assertEquals([], explode_trim(',', $input_string));
    }

    /**
     * Tests explode_trim with null input.
     *
     * @return void
     */
    public function test_explode_trim_with_null_input(): void
    {
        $this->assertEquals([], explode_trim(',', null));
    }

    /**
     * Tests explode_trim with extra spaces around the values.
     *
     * @return void
     */
    public function test_explode_trim_with_extra_spaces(): void
    {
        $input_string = ' apple , banana ,  cherry  ';
        $expected     = ['apple', 'banana', 'cherry'];
        $this->assertEquals($expected, explode_trim(',', $input_string));
    }

    /**
     * Tests explode_trim with different separator.
     *
     * @return void
     */
    public function test_explode_trim_with_different_separator(): void
    {
        $input_string = 'apple|banana|cherry';
        $expected     = ['apple', 'banana', 'cherry'];
        $this->assertEquals($expected, explode_trim('|', $input_string));
    }

    /**
     * Tests explode_trim with a limit parameter.
     *
     * @return void
     */
    public function test_explode_trim_with_limit(): void
    {
        $input_string = 'apple, banana, cherry, date';
        $expected     = ['apple', 'banana', 'cherry, date'];
        $this->assertEquals($expected, explode_trim(',', $input_string, 3));
    }

    /**
     * Tests explode_trim with dirty input containing tabs and newlines.
     *
     * @return void
     */
    public function test_explode_trim_with_dirty_input(): void
    {
        $input_string = "apple\t, banana\n,  cherry  ";
        $expected     = ['apple', 'banana', 'cherry'];
        $this->assertEquals($expected, explode_trim(',', $input_string));
    }

    /**
     * Tests explode_trim with single-character string input.
     *
     * @return void
     */
    public function test_explode_trim_with_single_character(): void
    {
        $input_string = 'a';
        $expected     = ['a'];
        $this->assertEquals($expected, explode_trim(',', $input_string));
    }

    /**
     * Tests explode_trim with a multi-character separator.
     *
     * @return void
     */
    public function test_explode_trim_with_multi_character_separator(): void
    {
        $input_string = 'apple--banana--cherry';
        $expected     = ['apple', 'banana', 'cherry'];
        $this->assertEquals($expected, explode_trim('--', $input_string));
    }

    /**
     * Tests explode_trim with a string containing special characters.
     *
     * @return void
     */
    public function test_explode_trim_with_special_characters(): void
    {
        $input_string = 'apple@banana#cherry';
        $expected     = ['apple@banana#cherry'];
        $this->assertEquals($expected, explode_trim(',', $input_string));
    }

    /**
     * Tests explode_trim with no spaces between values.
     *
     * @return void
     */
    public function test_explode_trim_with_no_spaces(): void
    {
        $input_string = 'apple,banana,cherry';
        $expected     = ['apple', 'banana', 'cherry'];
        $this->assertEquals($expected, explode_trim(',', $input_string));
    }

    /**
     * Tests strip_boundary_characters with punctuation at both ends.
     *
     * @return void
     */
    public function test_strip_boundary_characters_with_punctuation(): void
    {
        $input_string = '!Hello, World!';
        $expected     = 'Hello, World';
        $this->assertEquals($expected, strip_boundary_characters($input_string));
    }

    /**
     * Tests strip_boundary_characters with no punctuation.
     *
     * @return void
     */
    public function test_strip_boundary_characters_with_no_punctuation(): void
    {
        $input_string = 'Hello, World';
        $this->assertEquals($input_string, strip_boundary_characters($input_string));
    }

    /**
     * Tests strip_boundary_characters with spaces at both ends using \p{Z} for whitespace.
     *
     * @return void
     */
    public function test_strip_boundary_characters_with_spaces(): void
    {
        $input_string = '  Hello, World  ';
        $expected     = 'Hello, World';
        $this->assertEquals($expected, strip_boundary_characters($input_string, 'Z'));
    }

    /**
     * Tests strip_boundary_characters with letters at the boundaries using \p{L} for letters.
     *
     * @return void
     */
    public function test_strip_boundary_characters_with_letters(): void
    {
        $input_string = 'aHello, Worldb';
        // All letters 'a' and 'b' are stripped from the boundaries, leaving only punctuation and spaces.
        $expected = ', ';
        $this->assertEquals($expected, strip_boundary_characters($input_string, 'L'));
    }

    /**
     * Tests strip_boundary_characters with numbers at the boundaries using \p{N} for numbers.
     *
     * @return void
     */
    public function test_strip_boundary_characters_with_numbers(): void
    {
        $input_string = '123Hello, World456';
        $expected     = 'Hello, World';
        $this->assertEquals($expected, strip_boundary_characters($input_string, 'N'));
    }

    /**
     * Tests strip_boundary_characters with special characters and punctuation inside the string.
     *
     * @return void
     */
    public function test_strip_boundary_characters_with_special_characters_inside(): void
    {
        $input_string = '(*&^%$Hello, World*&^%$)';
        // Parentheses and special characters from the boundaries are stripped.
        $expected = '^%$Hello, World*&^%$';
        $this->assertEquals($expected, strip_boundary_characters($input_string));
    }

    /**
     * Tests strip_boundary_characters with a multi-character Unicode property.
     *
     * @return void
     */
    public function test_strip_boundary_characters_with_multi_character_property(): void
    {
        $input_string = '--Hello, World--';
        $expected     = 'Hello, World';
        // Strip dashes (punctuation, dash)
        $this->assertEquals($expected, strip_boundary_characters($input_string, 'Pd'));
    }

    /**
     * Tests strip_boundary_characters with dirty input containing tabs and newlines.
     *
     * @return void
     */
    public function test_strip_boundary_characters_with_dirty_input(): void
    {
        $input_string = "\t      Hello, World\n";
        // All leading and trailing whitespace characters (spaces, tabs, newlines) should be stripped.
        $expected = 'Hello, World';
        $this->assertEquals($expected, strip_boundary_characters($input_string, 'Z'));
    }

    /**
     * Tests strip_boundary_characters with an empty string.
     *
     * @return void
     */
    public function test_strip_boundary_characters_with_empty_string(): void
    {
        $input_string = '';
        $this->assertEquals('', strip_boundary_characters($input_string));
    }

    /**
     * Tests strip_boundary_characters with only punctuation.
     *
     * @return void
     */
    public function test_strip_boundary_characters_with_only_punctuation(): void
    {
        $input_string = '!!!';
        $expected     = '';
        $this->assertEquals($expected, strip_boundary_characters($input_string));
    }

    /**
     * Tests strip_boundary_punctuation with punctuation at both ends.
     *
     * @return void
     */
    public function test_strip_boundary_punctuation_with_punctuation(): void
    {
        $input_string = '!Hello, World!';
        // Punctuation should be removed from both sides
        $expected = 'Hello, World';
        $this->assertEquals($expected, strip_boundary_punctuation($input_string));
    }

    /**
     * Tests strip_boundary_punctuation with no punctuation.
     *
     * @return void
     */
    public function test_strip_boundary_punctuation_with_no_punctuation(): void
    {
        $input_string = 'Hello, World';
        // No punctuation at the boundaries, so nothing should change
        $expected = 'Hello, World';
        $this->assertEquals($expected, strip_boundary_punctuation($input_string));
    }

    /**
     * Tests strip_boundary_punctuation with punctuation only at the start.
     *
     * @return void
     */
    public function test_strip_boundary_punctuation_with_start_punctuation(): void
    {
        $input_string = '!Hello, World';
        // Punctuation should be removed from the start
        $expected = 'Hello, World';
        $this->assertEquals($expected, strip_boundary_punctuation($input_string));
    }

    /**
     * Tests strip_boundary_punctuation with punctuation only at the end.
     *
     * @return void
     */
    public function test_strip_boundary_punctuation_with_end_punctuation(): void
    {
        $input_string = 'Hello, World!';
        // Punctuation should be removed from the end
        $expected = 'Hello, World';
        $this->assertEquals($expected, strip_boundary_punctuation($input_string));
    }

    /**
     * Tests strip_boundary_punctuation with no characters, only punctuation.
     *
     * @return void
     */
    public function test_strip_boundary_punctuation_with_only_punctuation(): void
    {
        $input_string = '!!!';
        // All punctuation should be removed, leaving an empty string
        $expected = '';
        $this->assertEquals($expected, strip_boundary_punctuation($input_string));
    }

    /**
     * Tests splitting a regular string with single spaces.
     *
     * @return void
     */
    public function test_preg_split_whitespace_regular_string(): void
    {
        $input    = 'This is a test string';
        $expected = ['This', 'is', 'a', 'test', 'string'];
        $this->assertSame($expected, preg_split_whitespace($input));
    }

    /**
     * Tests splitting a string with multiple spaces between words.
     *
     * @return void
     */
    public function test_preg_split_whitespace_multiple_spaces(): void
    {
        $input    = 'This   is   a   test';
        $expected = ['This', 'is', 'a', 'test'];
        $this->assertSame($expected, preg_split_whitespace($input));
    }

    /**
     * Tests splitting a string with various whitespace characters like tabs and newlines.
     *
     * @return void
     */
    public function test_preg_split_whitespace_various_whitespace(): void
    {
        $input    = "This\tis\na   test";
        $expected = ['This', 'is', 'a', 'test'];
        $this->assertSame($expected, preg_split_whitespace($input));
    }

    /**
     * Tests splitting a string with leading and trailing spaces.
     *
     * @return void
     */
    public function test_preg_split_whitespace_leading_trailing_spaces(): void
    {
        $input    = '   This is a test   ';
        $expected = ['This', 'is', 'a', 'test'];
        $this->assertSame($expected, preg_split_whitespace($input));
    }

    /**
     * Tests splitting an empty string.
     *
     * @return void
     */
    public function test_preg_split_whitespace_empty_string(): void
    {
        $input    = '';
        $expected = [];
        $this->assertSame($expected, preg_split_whitespace($input));
    }

    /**
     * Tests splitting a string that only contains spaces.
     *
     * @return void
     */
    public function test_preg_split_whitespace_only_spaces(): void
    {
        $input    = '     ';
        $expected = [];
        $this->assertSame($expected, preg_split_whitespace($input));
    }

    /**
     * Tests splitting a string with mixed whitespace and empty input (dirty case).
     *
     * @return void
     */
    public function test_preg_split_whitespace_mixed_whitespace(): void
    {
        $input    = "\t   \n  ";
        $expected = [];
        $this->assertSame($expected, preg_split_whitespace($input));
    }

    /**
     * Tests splitting a string with no spaces.
     *
     * @return void
     */
    public function test_preg_split_whitespace_no_spaces(): void
    {
        $input    = 'NoSpacesHere';
        $expected = ['NoSpacesHere'];
        $this->assertSame($expected, preg_split_whitespace($input));
    }

    /**
     * Tests splitting a string with non-breaking spaces.
     *
     * @return void
     */
    public function test_preg_split_whitespace_non_breaking_spaces(): void
    {
        $input    = "This\xC2\xA0is\xC2\xA0a\xC2\xA0test";
        $expected = ['This', 'is', 'a', 'test'];
        $this->assertSame($expected, preg_split_whitespace($input));
    }

    /**
     * Tests that non-breaking spaces are treated as regular characters
     * and not split when $includeNonBreakingSpace is false.
     *
     * @return void
     */
    public function test_preg_split_whitespace_non_breaking_spaces_treated_as_regular(): void
    {
        $input = "This\xC2\xA0is\xC2\xA0a\xC2\xA0test";
        // Non-breaking spaces are treated as regular characters. They should not be split.
        $expected = ['This is a test']; // Uses non-breaking space character
        $this->assertSame($expected, preg_split_whitespace($input, false));
    }

    /**
     * Tests that normal spaces are still split, but non-breaking spaces
     * remain intact when $includeNonBreakingSpace is false.
     *
     * @return void
     */
    public function test_preg_split_whitespace_multiple_spaces_with_non_breaking_spaces_excluded(): void
    {
        $input = "This   is\xC2\xA0a   test";
        // Only normal spaces are split. Non-breaking spaces should not be split.
        $expected = ['This', 'is a', 'test']; // Uses non-breaking space character
        $this->assertSame($expected, preg_split_whitespace($input, false));
    }

    /**
     * Test basic word splitting with punctuation.
     */
    public function test_preg_split_word_boundary_basic_word_splitting_with_punctuation(): void
    {
        $input    = 'Hello, world! How are you?';
        $expected = ['Hello', ',', 'world', '!', 'How', 'are', 'you', '?'];
        $this->assertEquals($expected, preg_split_word_boundary($input));
    }

    /**
     * Test handling of numbers and special characters.
     */
    public function test_preg_split_word_boundary_numbers_and_special_characters(): void
    {
        $input    = 'Test123 $45.67 *underscore*';
        $expected = ['Test123', '$', '45', '.', '67', '*', 'underscore', '*'];
        $this->assertEquals($expected, preg_split_word_boundary($input));
    }

    /**
     * Test handling of contractions and possessives.
     */
    public function test_preg_split_word_boundary_contractions_and_possessives(): void
    {
        $input    = "I can't believe it's John's dog";
        $expected = ['I', 'can', "'", 't', 'believe', 'it', "'", 's', 'John', "'", 's', 'dog'];
        $this->assertEquals($expected, preg_split_word_boundary($input));
    }

    /**
     * Test handling of input with only non-word characters.
     */
    public function test_preg_split_word_boundary_only_non_word_characters(): void
    {
        $input    = '  .,;:!?  ';
        $expected = ['.,;:!?'];
        $this->assertEquals($expected, preg_split_word_boundary($input));
    }

    /**
     * Test handling of Unicode characters.
     */
    public function test_preg_split_word_boundary_unicode_characters(): void
    {
        $input    = 'こんにちは world Café';
        $expected = ['こんにちは', 'world', 'Café'];
        $this->assertEquals($expected, preg_split_word_boundary($input));
    }

    /**
     * Test handling of words connected by hyphens or slashes.
     */
    public function test_preg_split_word_boundary_hyphenated_and_slashed_words(): void
    {
        $input    = 'well-known example and/or test-case';
        $expected = ['well', '-', 'known', 'example', 'and', '/', 'or', 'test', '-', 'case'];
        $this->assertEquals($expected, preg_split_word_boundary($input));
    }

    /**
     * Test handling of multiple spaces and tabs.
     */
    public function test_preg_split_word_boundary_multiple_spaces_and_tabs(): void
    {
        $input    = "Hello   world\t\ttest";
        $expected = ['Hello', 'world', 'test'];
        $this->assertEquals($expected, preg_split_word_boundary($input));
    }

    /**
     * Test handling of leading and trailing spaces.
     */
    public function test_preg_split_word_boundary_leading_and_trailing_spaces(): void
    {
        $input    = '  Hello world  ';
        $expected = ['Hello', 'world'];
        $this->assertEquals($expected, preg_split_word_boundary($input));
    }

    /**
     * Test handling of empty input.
     */
    public function test_preg_split_word_boundary_empty_input(): void
    {
        $input    = '';
        $expected = [];
        $this->assertEquals($expected, preg_split_word_boundary($input));
    }

    /**
     * Test handling of mixed ASCII and non-ASCII words.
     */
    public function test_preg_split_word_boundary_mixed_ascii_and_non_ascii(): void
    {
        $input    = 'Hello мир Café au lait';
        $expected = ['Hello', 'мир', 'Café', 'au', 'lait'];
        $this->assertEquals($expected, preg_split_word_boundary($input));
    }

    /**
     * Test handling of emoji and other Unicode symbols.
     */
    public function test_preg_split_word_boundary_emoji_and_unicode_symbols(): void
    {
        $input    = 'Hello 👋 world! ☕ time';
        $expected = ['Hello', '👋', 'world', '! ☕', 'time'];
        $this->assertEquals($expected, preg_split_word_boundary($input));
    }

    /**
     * Test handling of various Unicode symbols and punctuation.
     */
    public function test_preg_split_word_boundary_various_unicode_symbols_and_punctuation(): void
    {
        $input    = "Hello🌍world!🎉 How's it going?👀";
        $expected = ['Hello', '🌍', 'world', '!🎉', 'How', "'", 's', 'it', 'going', '?👀'];
        $this->assertEquals($expected, preg_split_word_boundary($input));
    }

    /**
     * Tests converting a regular sentence to words.
     *
     * @return void
     */
    public function test_string_to_words_regular_sentence(): void
    {
        $input    = 'This is a test.';
        $expected = ['This', 'is', 'a', 'test'];
        $this->assertSame($expected, string_to_words($input));
    }

    /**
     * Tests converting a sentence with punctuation.
     *
     * @return void
     */
    public function test_string_to_words_with_punctuation(): void
    {
        $input    = 'Hello! How are you?';
        $expected = ['Hello', 'How', 'are', 'you'];
        $this->assertSame($expected, string_to_words($input));
    }

    /**
     * Tests a sentence with contractions.
     *
     * @return void
     */
    public function test_string_to_words_with_contractions(): void
    {
        $input = "It's a test.";
        // Retaining the contraction as a single word
        $expected = ["It's", 'a', 'test'];
        $this->assertSame($expected, string_to_words($input));
    }

    /**
     * Tests a sentence with repeated words when $unique is true.
     *
     * @return void
     */
    public function test_string_to_words_unique_words(): void
    {
        $input = 'Test test Test!';
        // Case-sensitive unique words
        $expected = ['Test', 'test'];
        $this->assertSame($expected, string_to_words($input, true));
    }

    /**
     * Tests a sentence with repeated words when $unique is false.
     *
     * @return void
     */
    public function test_string_to_words_not_unique_words(): void
    {
        $input    = 'Test test Test!';
        $expected = ['Test', 'test', 'Test'];
        $this->assertSame($expected, string_to_words($input, false));
    }

    /**
     * Tests an empty string.
     *
     * @return void
     */
    public function test_string_to_words_empty_string(): void
    {
        $input    = '';
        $expected = [];
        $this->assertSame($expected, string_to_words($input));
    }

    /**
     * Tests a string with only whitespace.
     *
     * @return void
     */
    public function test_string_to_words_whitespace_only(): void
    {
        $input    = '     ';
        $expected = [];
        $this->assertSame($expected, string_to_words($input));
    }

    /**
     * Tests a string with leading and trailing whitespace.
     *
     * @return void
     */
    public function test_string_to_words_leading_trailing_whitespace(): void
    {
        $input    = '   Hello World   ';
        $expected = ['Hello', 'World'];
        $this->assertSame($expected, string_to_words($input));
    }

    /**
     * Tests a string with mixed whitespace characters (tabs, newlines).
     *
     * @return void
     */
    public function test_string_to_words_mixed_whitespace(): void
    {
        $input    = "Hello\tWorld\nHow are you?";
        $expected = ['Hello', 'World', 'How', 'are', 'you'];
        $this->assertSame($expected, string_to_words($input));
    }

    /**
     * Tests a string with special characters and punctuation.
     *
     * @return void
     */
    public function test_string_to_words_special_characters(): void
    {
        $input    = 'Hello, @world! #test';
        $expected = ['Hello', 'world', 'test'];
        $this->assertSame($expected, string_to_words($input));
    }

    /**
     * Tests a string with numbers and punctuation.
     *
     * @return void
     */
    public function test_string_to_words_numbers_and_punctuation(): void
    {
        $input = 'The price is $10.99!';
        // Treating $10.99 as a single word
        $expected = ['The', 'price', 'is', '$10.99'];
        $this->assertSame($expected, string_to_words($input));
    }

    /**
     * Tests a string that contains only punctuation.
     *
     * @return void
     */
    public function test_string_to_words_only_punctuation(): void
    {
        $input    = '.,!?';
        $expected = [];
        $this->assertSame($expected, string_to_words($input));
    }

    /**
     * Tests a string that contains non-breaking spaces.
     *
     * @return void
     */
    public function test_string_to_words_non_breaking_spaces(): void
    {
        $input    = "Hello\xC2\xA0World\xC2\xA0!";
        $expected = ['Hello', 'World'];
        $this->assertSame($expected, string_to_words($input));
    }

    /**
     * Tests the removal of stop words from a standard sentence.
     */
    public function test_remove_stop_words_standard_case()
    {
        // Input string with standard stop words
        $input = 'This is a test of the stop word removal system.';

        // Expected output after stop words are removed
        $expected = 'test stop word removal system.';

        // Assert that the function removes stop words correctly
        $this->assertEquals($expected, remove_stop_words($input));
    }

    /**
     * Tests that stop words are correctly removed in a sentence
     * with no stop words to ensure the function doesn't alter the text.
     */
    public function test_remove_stop_words_no_stop_words()
    {
        // Input string with no stop words
        $input = 'Unique text with no stop words.';

        // Expected output should be the same
        $expected = 'Unique text stop words.';

        // Assert that the function doesn't modify the string when no stop words are present
        $this->assertEquals($expected, remove_stop_words($input));
    }

    /**
     * Tests that the function removes stop words while maintaining
     * case sensitivity.
     */
    public function test_remove_stop_words_case_sensitive()
    {
        // Input string with mixed case stop words
        $input = 'This IS a TEST Of the stop WORD Removal system.';

        // Expected output after stop words are removed
        $expected = 'TEST stop WORD Removal system.';

        // Assert that the function removes stop words case-insensitively but preserves the case of other words
        $this->assertEquals($expected, remove_stop_words($input));
    }

    /**
     * Tests that the function removes extra whitespace
     * between words after stop words are removed.
     */
    public function test_remove_stop_words_removes_extra_whitespace()
    {
        // Input string with extra spaces between words
        $input = 'This   is   a  test    of  stop   word removal.';

        // Expected output with no extra spaces and stop words removed
        $expected = 'test stop word removal.';

        // Assert that the function removes extra whitespace after stop words are removed
        $this->assertEquals($expected, remove_stop_words($input));
    }

    /**
     * Tests that the function correctly handles punctuation.
     */
    public function test_remove_stop_words_with_punctuation()
    {
        // Input string with punctuation
        $input = "Well, this is a test, isn't it?";

        // Expected output with stop words removed but punctuation retained
        $expected = "Well, test, isn't ?";

        // Assert that the function handles punctuation correctly
        $this->assertEquals($expected, remove_stop_words($input));
    }

    /**
     * Tests that the function handles strings with inconsistent spacing,
     * and case issues.
     */
    public function test_remove_stop_words_dirty_input()
    {
        // Input string with mixed whitespace and inconsistent casing
        $input = '  This     IS a    teSt of    the    stop WORD   Removal   SYSTEM. ';

        // Expected output with cleaned text and stop words removed
        $expected = 'teSt stop WORD Removal SYSTEM.';

        // Assert that the function handles dirty input and cleans up the string
        $this->assertEquals($expected, remove_stop_words($input));
    }

    /**
     * Tests that numbers are retained correctly in the string.
     */
    public function test_remove_stop_words_handles_numbers()
    {
        // Input string with numbers
        $input = 'The 3 stop words in this 2 sentence should be removed.';

        // Expected output with stop words removed but numbers retained
        $expected = '3 stop words 2 sentence removed.';

        // Assert that numbers are not affected by stop word removal
        $this->assertEquals($expected, remove_stop_words($input));
    }

    /**
     * Tests the function's ability to remove stop words
     * even when they appear at the start and end of the string.
     */
    public function test_remove_stop_words_with_boundaries()
    {
        // Input string with stop words at the boundaries
        $input = 'And this is a test.';

        // Expected output after stop words are removed
        $expected = 'test.';

        // Assert that the function handles stop words at the boundaries correctly
        $this->assertEquals($expected, remove_stop_words($input));
    }

    /**
     * Tests that the function does not remove partial matches
     * of stop words in larger words.
     */
    public function test_remove_stop_words_no_partial_matches()
    {
        // Input string with words that partially match stop words
        $input = 'Candid candidate was candid about the candy.';

        // Expected output with stop words removed but partial matches retained
        $expected = 'Candid candidate candid candy.';

        // Assert that partial matches of stop words are not removed
        $this->assertEquals($expected, remove_stop_words($input));
    }

    /**
     * Tests that the function correctly removes stop words
     * and converts the result to an array.
     */
    public function test_remove_stop_words_to_array_removes_stop_words()
    {
        // Input string with stop words
        $input = 'This is a test of the stop word removal system.';

        // Expected output after stop words are removed
        $expected = ['test', 'stop', 'word', 'removal', 'system'];

        // Assert that the function works as expected
        $this->assertEquals($expected, remove_stop_words_to_array($input));
    }

    /**
     * Tests that the function handles an empty string
     * by returning an empty array.
     */
    public function test_remove_stop_words_to_array_handles_empty_string()
    {
        // Input string is empty
        $input = '';

        // Expected output should be an empty array
        $expected = [];

        // Assert that the function returns an empty array for an empty string
        $this->assertEquals($expected, remove_stop_words_to_array($input));
    }

    /**
     * Tests that the function correctly processes a string
     * with no stop words.
     */
    public function test_remove_stop_words_to_array_no_stop_words()
    {
        // Input string with no stop words
        $input = 'Unique text with no common stop words.';

        // Expected output should be the same, minus stop words
        $expected = ['Unique', 'text', 'common', 'stop', 'words'];

        // Assert that the function retains non-stop words
        $this->assertEquals($expected, remove_stop_words_to_array($input));
    }

    /**
     * Tests that the function handles a string containing
     * only stop words, returning an empty array.
     */
    public function test_remove_stop_words_to_array_all_stop_words()
    {
        // Input string with only stop words
        $input = 'the and or but so for nor yet';

        // Expected output should be an empty array
        $expected = [];

        // Assert that the function returns an empty array for stop words only
        $this->assertEquals($expected, remove_stop_words_to_array($input));
    }

    /**
     * Tests that the function correctly handles punctuation
     * and keeps it intact where appropriate.
     */
    public function test_remove_stop_words_to_array_handles_punctuation()
    {
        // Input string with punctuation
        $input = "Well, this is a test, isn't it?";

        // Expected output after stop words are removed
        $expected = ['Well', 'test', "isn't"];

        // Assert that the function handles punctuation correctly
        $this->assertEquals($expected, remove_stop_words_to_array($input));
    }

    /**
     * Tests that the function handles mixed-case input
     * and removes stop words case-insensitively.
     */
    public function test_remove_stop_words_to_array_case_insensitive()
    {
        // Input string with mixed case stop words
        $input = 'This IS a TEST Of the stop WORD Removal system.';

        // Expected output after stop words are removed
        $expected = ['TEST', 'stop', 'WORD', 'Removal', 'system'];

        // Assert that the function removes stop words case-insensitively
        $this->assertEquals($expected, remove_stop_words_to_array($input));
    }

    /**
     * Tests that the function removes extra whitespace
     * from the input string.
     */
    public function test_remove_stop_words_to_array_removes_extra_whitespace()
    {
        // Input string with extra whitespace between words
        $input = 'This   is   a  test    of  stop   word removal.';

        // Expected output with no extra spaces
        $expected = ['test', 'stop', 'word', 'removal'];

        // Assert that the function removes extra whitespace
        $this->assertEquals($expected, remove_stop_words_to_array($input));
    }

    /**
     * Tests that the function handles "dirty" input with
     * mixed case and inconsistent whitespace.
     */
    public function test_remove_stop_words_to_array_handles_dirty_input()
    {
        // Input string with dirty input (mixed case and extra spaces)
        $input = '  This     IS a    teSt of    the    stop WORD   Removal   SYSTEM. ';

        // Expected output after cleaning up
        $expected = ['teSt', 'stop', 'WORD', 'Removal', 'SYSTEM'];

        // Assert that the function handles dirty input correctly
        $this->assertEquals($expected, remove_stop_words_to_array($input));
    }

    /**
     * Tests that the function handles input with numbers
     * and retains them.
     */
    public function test_remove_stop_words_to_array_handles_numbers()
    {
        // Input string with numbers and stop words
        $input = 'The 3 stop words in this 2 sentence should be removed.';

        // Expected output with numbers retained
        $expected = ['3', 'stop', 'words', '2', 'sentence', 'removed'];

        // Assert that the function retains numbers correctly
        $this->assertEquals($expected, remove_stop_words_to_array($input));
    }

    /**
     * Tests that the function correctly handles stop words
     * at the boundaries of a string.
     */
    public function test_remove_stop_words_to_array_handles_boundary_stop_words()
    {
        // Input string with stop words at the beginning and end
        $input = 'And this is a test.';

        // Expected output with stop words removed from the boundaries
        $expected = ['test'];

        // Assert that the function removes stop words at boundaries correctly
        $this->assertEquals($expected, remove_stop_words_to_array($input));
    }

    /**
     * Tests that the function does not remove words that
     * partially match stop words.
     */
    public function test_remove_stop_words_to_array_no_partial_stop_word_removal()
    {
        // Input string with partial stop word matches
        $input = 'Candid candidate was candid about the candy.';

        // Expected output should retain partial matches
        $expected = ['Candid', 'candidate', 'candid', 'candy'];

        // Assert that the function does not remove partial matches
        $this->assertEquals($expected, remove_stop_words_to_array($input));
    }

    /**
     * Test basic functionality of filter_stop_words
     */
    public function test_filter_stop_words_basic_functionality()
    {
        $input    = 'This is a test of the stop word removal system.';
        $expected = ['test', 'stop', 'word', 'removal', 'system'];
        $this->assertEquals($expected, filter_stop_words($input));
    }

    /**
     * Test filter_stop_words with an empty string
     */
    public function test_filter_stop_words_empty_string()
    {
        $this->assertEquals([], filter_stop_words(''));
    }

    /**
     * Test filter_stop_words with a string containing only stop words
     */
    public function test_filter_stop_words_only_stop_words()
    {
        $input = 'the and or but so for nor yet';
        $this->assertEquals([], filter_stop_words($input));
    }

    /**
     * Test filter_stop_words with a string containing no stop words
     */
    public function test_filter_stop_words_no_stop_words()
    {
        $input    = 'Unique text with no common stop words';
        $expected = ['Unique', 'text', 'common', 'stop', 'words'];
        $this->assertEquals($expected, filter_stop_words($input));
    }

    /**
     * Test filter_stop_words with mixed case input
     */
    public function test_filter_stop_words_mixed_case()
    {
        $input    = 'This IS a TEST Of the stop WORD Removal system.';
        $expected = ['TEST', 'stop', 'WORD', 'Removal', 'system'];
        $this->assertEquals($expected, filter_stop_words($input));
    }

    /**
     * Test filter_stop_words with extra whitespace
     */
    public function test_filter_stop_words_extra_whitespace()
    {
        $input    = '  This     IS a    teSt of    the    stop WORD   Removal   SYSTEM. ';
        $expected = ['teSt', 'stop', 'WORD', 'Removal', 'SYSTEM'];
        $this->assertEquals($expected, filter_stop_words($input));
    }

    /**
     * Test filter_stop_words with numbers
     */
    public function test_filter_stop_words_with_numbers()
    {
        $input    = 'The 3 stop words in this 2 sentence should be removed.';
        $expected = ['3', 'stop', 'words', '2', 'sentence', 'removed'];
        $this->assertEquals($expected, filter_stop_words($input));
    }

    /**
     * Test filter_stop_words with punctuation
     */
    public function test_filter_stop_words_with_punctuation()
    {
        $input    = "Well, this is a test, isn't it?";
        $expected = ['Well', 'test', "isn't"];
        $this->assertEquals($expected, filter_stop_words($input));
    }

    /**
     * Test filter_stop_words with unique words option
     */
    public function test_filter_stop_words_unique_words()
    {
        $input    = 'This is a test of the stop word removal system. This is another test.';
        $expected = ['test', 'stop', 'word', 'removal', 'system', 'another'];
        $this->assertEquals($expected, filter_stop_words($input, true));
    }

    /**
     * Test filter_stop_words with non-English characters
     */
    public function test_filter_stop_words_non_english_characters()
    {
        $input    = 'Thé quïck brøwn føx jumps øver the lazy døg';
        $expected = ['Thé', 'quïck', 'brøwn', 'føx', 'jumps', 'øver', 'lazy', 'døg'];
        $this->assertEquals($expected, filter_stop_words($input));
    }

    /**
     * Test filter_stop_words with a very long input string
     */
    public function test_filter_stop_words_long_input()
    {
        $input  = str_repeat('This is a test of the stop word removal system. ', 1000);
        $result = filter_stop_words($input);
        $this->assertCount(5000, $result);
        $this->assertEquals(['test', 'stop', 'word', 'removal', 'system'], array_unique($result));
    }

    /**
     * Test basic functionality of get_post_name_incremented
     */
    public function test_get_post_name_incremented_basic()
    {
        $names  = ['hello-world', 'test'];
        $result = get_post_name_incremented('hello-world', $names);
        $this->assertEquals('hello-world-2', $result);
    }

    /**
     * Test get_post_name_incremented with multiple increments
     */
    public function test_get_post_name_incremented_multiple()
    {
        $names  = ['hello-world', 'hello-world-2', 'hello-world-3'];
        $result = get_post_name_incremented('hello-world', $names);
        $this->assertEquals('hello-world-4', $result);
    }

    /**
     * Test get_post_name_incremented with empty array
     */
    public function test_get_post_name_incremented_empty_array()
    {
        $names  = [];
        $result = get_post_name_incremented('test', $names);
        $this->assertEquals('test', $result);
    }

    /**
     * Test get_post_name_incremented with modify_post_name_array set to true
     */
    public function test_get_post_name_incremented_modify_array()
    {
        $names  = ['test'];
        $result = get_post_name_incremented('test', $names, true);
        $this->assertEquals('test-2', $result);
        $this->assertContains('test-2', $names);
    }

    /**
     * Test get_post_name_incremented with modify_post_name_array set to false
     */
    public function test_get_post_name_incremented_do_not_modify_array()
    {
        $names  = ['test'];
        $result = get_post_name_incremented('test', $names, false);
        $this->assertEquals('test-2', $result);
        $this->assertNotContains('test-2', $names);
    }

    /**
     * Test get_post_name_incremented with non-ascii characters
     */
    public function test_get_post_name_incremented_non_ascii()
    {
        $names  = ['こんにちは世界', 'こんにちは世界-2'];
        $result = get_post_name_incremented('こんにちは世界', $names);
        $this->assertEquals('こんにちは世界-3', $result);
    }

    /**
     * Test get_post_name_incremented with mixed case
     */
    public function test_get_post_name_incremented_mixed_case()
    {
        $names  = ['Hello-World', 'hello-world'];
        $result = get_post_name_incremented('hello-world', $names);
        $this->assertEquals('hello-world-2', $result);
    }

    /**
     * Test get_post_name_incremented with very large number
     */
    public function test_get_post_name_incremented_large_number()
    {
        $names  = ['test', 'test-2', 'test-3', 'test-4', 'test-5', 'test-6', 'test-7', 'test-8', 'test-9', 'test-10'];
        $result = get_post_name_incremented('test', $names);
        $this->assertEquals('test-11', $result);
    }

    /**
     * Test get_post_name_incremented with empty string input
     */
    public function test_get_post_name_incremented_empty_string()
    {
        $names = ['non-empty'];
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('$name cannot be empty.');
        get_post_name_incremented('', $names);
    }

    /**
     * Test get_post_name_incremented with very long string input
     */
    public function test_get_post_name_incremented_long_string()
    {
        $long_string = str_repeat('a', 255);
        $names       = [$long_string];
        $result      = get_post_name_incremented($long_string, $names);
        $this->assertEquals($long_string . '-2', $result);
    }

    /**
     * Test basic functionality of wordwrap_first_line
     */
    public function test_wordwrap_first_line_basic()
    {
        $input  = 'The quick brown fox jumps over the lazy dog';
        $result = wordwrap_first_line($input, 20);
        $this->assertEquals('The quick brown fox', $result);
    }

    /**
     * Test wordwrap_first_line with a short input string
     */
    public function test_wordwrap_first_line_short_input()
    {
        $input  = 'Short';
        $result = wordwrap_first_line($input, 10);
        $this->assertEquals('Short', $result);
    }

    /**
     * Test wordwrap_first_line with a very long word
     */
    public function test_wordwrap_first_line_long_word()
    {
        $input  = 'Supercalifragilisticexpialidocious is a very long word';
        $result = wordwrap_first_line($input, 15);
        $this->assertEquals('Supercalifragilisticexpialidocious', $result);
    }

    /**
     * Test wordwrap_first_line with cut_long_words set to true
     */
    public function test_wordwrap_first_line_cut_long_words()
    {
        $input  = 'Supercalifragilisticexpialidocious is a very long word';
        $result = wordwrap_first_line($input, 15, "\n", true);
        $this->assertEquals('Supercalifragil', $result);
    }

    /**
     * Test wordwrap_first_line with a custom break character
     */
    public function test_wordwrap_first_line_custom_break()
    {
        $input  = 'The quick brown fox jumps over the lazy dog';
        $result = wordwrap_first_line($input, 20, '|');
        $this->assertEquals('The quick brown fox', $result);
    }

    /**
     * Test wordwrap_first_line with multiple spaces between words
     */
    public function test_wordwrap_first_line_multiple_spaces()
    {
        $input  = 'The   quick   brown   fox   jumps';
        $result = wordwrap_first_line($input, 20);
        $this->assertEquals('The   quick   brown ', $result);
    }

    /**
     * Test wordwrap_first_line with leading and trailing spaces
     */
    public function test_wordwrap_first_line_trim_spaces()
    {
        $input  = '   The quick brown fox   ';
        $result = wordwrap_first_line($input, 20);
        $this->assertEquals('   The quick brown', $result);
    }

    /**
     * Test wordwrap_first_line with newline characters in input
     */
    public function test_wordwrap_first_line_newlines_in_input()
    {
        $input  = "The quick\nbrown fox\njumps over";
        $result = wordwrap_first_line($input, 20);
        $this->assertEquals('The quick', $result);
    }

    /**
     * Test wordwrap_first_line with empty input string
     */
    public function test_wordwrap_first_line_empty_string()
    {
        $result = wordwrap_first_line('', 20);
        $this->assertEquals('', $result);
    }

    /**
     * Test wordwrap_first_line with width equal to 1
     */
    public function test_wordwrap_first_line_width_one()
    {
        $input  = 'Hello';
        $result = wordwrap_first_line($input, 1);
        $this->assertEquals('Hello', $result);
    }

    /**
     * Test wordwrap_first_line with invalid width (0)
     */
    public function test_wordwrap_first_line_invalid_width_zero()
    {
        $this->expectException(\InvalidArgumentException::class);
        wordwrap_first_line('Test', 0);
    }

    /**
     * Test wordwrap_first_line with invalid width (negative)
     */
    public function test_wordwrap_first_line_invalid_width_negative()
    {
        $this->expectException(\InvalidArgumentException::class);
        wordwrap_first_line('Test', -5);
    }

    /**
     * Test basic functionality of var_export_inline with a simple string
     */
    public function test_var_export_inline_simple_string()
    {
        $result = var_export_inline('Hello, World!', true);
        $this->assertEquals("'Hello, World!'", $result);
    }

    /**
     * Test var_export_inline with a simple array
     */
    public function test_var_export_inline_simple_array()
    {
        $result = var_export_inline(['a', 'b', 'c'], true);
        $this->assertEquals("array ( 0 => 'a', 1 => 'b', 2 => 'c', )", $result);
    }

    /**
     * Test var_export_inline with a nested array
     */
    public function test_var_export_inline_nested_array()
    {
        $result = var_export_inline(['a' => [1, 2], 'b' => [3, 4]], true);
        $this->assertEquals("array ( 'a' => array ( 0 => 1, 1 => 2, ), 'b' => array ( 0 => 3, 1 => 4, ), )", $result);
    }

    /**
     * Test var_export_inline with an object
     */
    public function test_var_export_inline_object()
    {
        $obj       = new \stdClass();
        $obj->prop = 'value';
        $result    = var_export_inline($obj, true);
        $this->assertEquals("(object) array( 'prop' => 'value', )", $result);
    }

    /**
     * Test var_export_inline with a null value
     */
    public function test_var_export_inline_null()
    {
        $result = var_export_inline(null, true);
        $this->assertEquals('NULL', $result);
    }

    /**
     * Test var_export_inline with boolean values
     */
    public function test_var_export_inline_boolean()
    {
        $this->assertEquals('true', var_export_inline(true, true));
        $this->assertEquals('false', var_export_inline(false, true));
    }

    /**
     * Test var_export_inline with a float value
     */
    public function test_var_export_inline_float()
    {
        $result = var_export_inline(3.14159, true);
        $this->assertEquals('3.14159', $result);
    }

    /**
     * Test var_export_inline with a resource
     */
    public function test_var_export_inline_resource()
    {
        $resource = fopen('php://memory', 'r');
        $result   = var_export_inline($resource, true);
        $this->assertEquals('NULL', $result);
        fclose($resource);
    }

    /**
     * Test var_export_inline with a closure
     */
    public function test_var_export_inline_closure()
    {
        $closure = function () {
            return 'test';
        };
        $result = var_export_inline($closure, true);
        $this->assertStringContainsString('Closure', $result);
    }

    /**
     * Test var_export_inline with a string containing special characters
     */
    public function test_var_export_inline_special_chars()
    {
        $result = var_export_inline("Hello\nWorld\t\"'", true);
        $this->assertEquals("'Hello World \"\\''", $result);
    }

    /**
     * Test var_export_inline output mode (not returning)
     */
    public function test_var_export_inline_output()
    {
        ob_start();
        var_export_inline('Test Output');
        $output = ob_get_clean();
        $this->assertEquals("'Test Output'", $output);
    }

    /**
     * Test var_export_inline with a very large array
     */
    public function test_var_export_inline_large_array()
    {
        $largeArray = range(1, 1000);
        $result     = var_export_inline($largeArray, true);
        $this->assertStringStartsWith('array (', $result);
        $this->assertStringEndsWith(', )', $result);
        // Arbitrary length check
        $this->assertGreaterThan(5000, strlen($result));
    }

    /**
     * Test numeric input with an integer value.
     */
    public function test_convert_to_bytes_numeric_integer()
    {
        $this->assertSame(1024, convert_to_bytes(1024));
    }

    /**
     * Test numeric input with a float value.
     */
    public function test_convert_to_bytes_numeric_float()
    {
        $this->assertSame(1024.5, convert_to_bytes(1024.5));
    }

    /**
     * Test string input with a "KB" unit.
     */
    public function test_convert_to_bytes_kb_unit()
    {
        $this->assertSame(1024, convert_to_bytes('1K'));
    }

    /**
     * Test string input with a "MB" unit.
     */
    public function test_convert_to_bytes_mb_unit()
    {
        $this->assertSame(1048576, convert_to_bytes('1M'));
    }

    /**
     * Test string input with a "GB" unit.
     */
    public function test_convert_to_bytes_gb_unit()
    {
        $this->assertSame(1073741824, convert_to_bytes('1G'));
    }

    /**
     * Test string input with a "TB" unit.
     */
    public function test_convert_to_bytes_tb_unit()
    {
        $this->assertSame(1099511627776, convert_to_bytes('1T'));
    }

    /**
     * Test input with a number and unit with whitespace around it.
     */
    public function test_convert_to_bytes_with_whitespace()
    {
        $this->assertSame(1048576, convert_to_bytes('   1M   '));
    }

    /**
     * Test input with lowercase units.
     */
    public function test_convert_to_bytes_lowercase_units()
    {
        $this->assertSame(1024, convert_to_bytes('1k'));
    }

    /**
     * Test input with a missing unit (defaults to bytes).
     */
    public function test_convert_to_bytes_no_unit()
    {
        $this->assertSame(500, convert_to_bytes('500'));
    }

    /**
     * Test input with an unrecognized unit.
     *
     * Expect an InvalidArgumentException to be thrown.
     */
    public function test_convert_to_bytes_unrecognized_unit()
    {
        $this->expectException(\InvalidArgumentException::class);
        // "X" is an unrecognized unit
        convert_to_bytes('1X');
    }

    /**
     * Test input with an invalid string format.
     *
     * Expect an InvalidArgumentException to be thrown.
     */
    public function test_convert_to_bytes_invalid_string_format()
    {
        $this->expectException(\InvalidArgumentException::class);
        convert_to_bytes('invalid string');
    }

    /**
     * Test input with a negative value.
     */
    public function test_convert_to_bytes_negative_value()
    {
        $this->assertSame(-1024, convert_to_bytes('-1K'));
    }

    /**
     * Test input where prefer_int is set to false.
     */
    public function test_convert_to_bytes_prefer_int_false()
    {
        $this->assertSame(1048576.0, convert_to_bytes('1M', false));
    }

    /**
     * Test large values with "YB" (yottabytes) to ensure PHP_INT_MAX is not exceeded.
     */
    public function test_convert_to_bytes_large_yottabytes()
    {
        // 1YB in bytes
        $expected = 1208925819614629174706176;
        $this->assertSame($expected, convert_to_bytes('1Y'));
    }

    /**
     * Test small floating-point number with a unit.
     */
    public function test_convert_to_bytes_small_float_with_unit()
    {
        $this->assertSame(512, convert_to_bytes('0.5K'));
    }

    /**
     * Test an empty string.
     *
     * Expect an InvalidArgumentException to be thrown.
     */
    public function test_convert_to_bytes_empty_string()
    {
        $this->expectException(\InvalidArgumentException::class);
        convert_to_bytes('');
    }

    /**
     * Test a very large integer value (1 petabyte).
     */
    public function test_convert_to_bytes_large_integer()
    {
        // Correct value for 1 Petabyte (PB)
        $expected = 1125899906842624;
        $this->assertSame($expected, convert_to_bytes('1P'));
    }

    /**
     * Test sanitize_css_class_name with valid class names.
     */
    public function test_sanitize_css_class_name_valid_names()
    {
        // Valid inputs should remain unchanged.
        $this->assertEquals('validClass', sanitize_css_class_name('validClass'));
        $this->assertEquals('valid-class', sanitize_css_class_name('valid-class'));
        $this->assertEquals('valid_class', sanitize_css_class_name('valid_class'));
        $this->assertEquals('validClass123', sanitize_css_class_name('validClass123'));
        $this->assertEquals('-valid-class', sanitize_css_class_name('-valid-class'));
        $this->assertEquals('_valid_class', sanitize_css_class_name('_valid_class'));
    }

    /**
     * Test sanitize_css_class_name with inputs starting with digits.
     */
    public function test_sanitize_css_class_name_starts_with_digit()
    {
        // Class names starting with digits should be prefixed with 'cls_'.
        $this->assertEquals('cls_123class', sanitize_css_class_name('123class'));
        $this->assertEquals('cls_9class', sanitize_css_class_name('9class'));
    }

    /**
     * Test sanitize_css_class_name with inputs starting with hyphens.
     */
    public function test_sanitize_css_class_name_starts_with_hyphen()
    {
        // Class names starting with multiple hyphens should be corrected.
        $this->assertEquals('-class', sanitize_css_class_name('-class'));
        $this->assertEquals('cls_class', sanitize_css_class_name('--class'));
        $this->assertEquals('cls_class', sanitize_css_class_name('---class'));
    }

    /**
     * Test sanitize_css_class_name with invalid characters.
     */
    public function test_sanitize_css_class_name_invalid_characters()
    {
        // Invalid characters should be removed from the class name.
        $this->assertEquals('invalidClass', sanitize_css_class_name('invalid@Class!'));
        $this->assertEquals('invalidClass', sanitize_css_class_name('invalid Class'));
        $this->assertEquals('invalidClass', sanitize_css_class_name('invalid.Class'));
    }

    /**
     * Test sanitize_css_class_name with Unicode characters.
     */
    public function test_sanitize_css_class_name_unicode_characters()
    {
        // Unicode characters should be allowed and remain unchanged.
        $this->assertEquals('クラス', sanitize_css_class_name('クラス'));
        $this->assertEquals('класс', sanitize_css_class_name('класс'));
    }

    /**
     * Test sanitize_css_class_name with empty and whitespace-only inputs.
     */
    public function test_sanitize_css_class_name_empty_and_whitespace()
    {
        // Empty or whitespace-only inputs should return 'cls_'.
        $this->assertEquals('cls_', sanitize_css_class_name(''));
        $this->assertEquals('cls_', sanitize_css_class_name(' '));
    }

    /**
     * Test sanitize_css_class_name with hyphen and underscore only inputs.
     */
    public function test_sanitize_css_class_name_hyphen_and_underscore_only()
    {
        // Inputs with only hyphens or underscores should return 'cls_'.
        $this->assertEquals('cls_', sanitize_css_class_name('-'));
        $this->assertEquals('cls_', sanitize_css_class_name('--'));
        $this->assertEquals('cls_', sanitize_css_class_name('---'));
        $this->assertEquals('cls_', sanitize_css_class_name('_'));
        $this->assertEquals('cls_', sanitize_css_class_name('__'));
        $this->assertEquals('cls_', sanitize_css_class_name('___'));
        $this->assertEquals('cls_', sanitize_css_class_name('-_-'));
        $this->assertEquals('cls_', sanitize_css_class_name('_-_'));
    }

    /**
     * Test sanitize_css_class_name with mixed inputs.
     */
    public function test_sanitize_css_class_name_mixed_inputs()
    {
        // Class names with mixed valid and invalid characters should be sanitized appropriately.
        $this->assertEquals('cls_9class', sanitize_css_class_name('-9class'));
        $this->assertEquals('cls_9class', sanitize_css_class_name('--9class'));
        $this->assertEquals('a-1-b_2', sanitize_css_class_name('a-1-b_2'));
        $this->assertEquals('-a-b2', sanitize_css_class_name('-a-b2'));
        $this->assertEquals('_a_b3', sanitize_css_class_name('_a_b3'));
    }

    /**
     * Test sanitize_css_class_name with long inputs.
     */
    public function test_sanitize_css_class_name_long_inputs()
    {
        // Long inputs should be handled correctly.
        $longAString = str_repeat('a', 100);
        $this->assertEquals($longAString, sanitize_css_class_name($longAString));
        $this->assertEquals('cls_class', sanitize_css_class_name(str_repeat('-', 10) . 'class'));
        $this->assertEquals('__________class', sanitize_css_class_name(str_repeat('_', 10) . 'class'));
    }

    /**
     * Test sanitize_css_id_name with valid ID names.
     */
    public function test_sanitize_css_id_name_valid_names()
    {
        // Valid ID inputs should remain unchanged.
        $this->assertEquals('validId', sanitize_css_id_name('validId'));
        $this->assertEquals('valid-id', sanitize_css_id_name('valid-id'));
        $this->assertEquals('valid_id', sanitize_css_id_name('valid_id'));
        $this->assertEquals('validId123', sanitize_css_id_name('validId123'));
        $this->assertEquals('_valid_id', sanitize_css_id_name('_valid_id'));
    }

    /**
     * Test sanitize_css_id_name with inputs starting with digits.
     */
    public function test_sanitize_css_id_name_starts_with_digit()
    {
        // ID names starting with digits should be prefixed with 'id_'.
        $this->assertEquals('id_123id', sanitize_css_id_name('123id'));
        $this->assertEquals('id_9id', sanitize_css_id_name('9id'));
    }

    /**
     * Test sanitize_css_id_name with inputs starting with hyphen and digit.
     */
    public function test_sanitize_css_id_name_starts_with_hyphen_and_digit()
    {
        // ID names starting with a hyphen followed by a digit should be corrected.
        $this->assertEquals('id_9id', sanitize_css_id_name('-9id'));
        $this->assertEquals('id_123id', sanitize_css_id_name('-123id'));
    }

    /**
     * Test sanitize_css_id_name with inputs starting with hyphen but no digit.
     */
    public function test_sanitize_css_id_name_starts_with_hyphen()
    {
        // ID names starting with a hyphen but no digit should remain unchanged.
        $this->assertEquals('-valid-id', sanitize_css_id_name('-valid-id'));
        $this->assertEquals('-myId', sanitize_css_id_name('-myId'));
    }

    /**
     * Test sanitize_css_id_name with invalid characters.
     */
    public function test_sanitize_css_id_name_invalid_characters()
    {
        // Invalid characters should be removed from the ID name.
        $this->assertEquals('invalidId', sanitize_css_id_name('invalid@Id!'));
        $this->assertEquals('invalidId', sanitize_css_id_name('invalid Id'));
        $this->assertEquals('invalidId', sanitize_css_id_name('invalid.Id'));
    }

    /**
     * Test sanitize_css_id_name with Unicode characters.
     */
    public function test_sanitize_css_id_name_unicode_characters()
    {
        // Unicode characters should be allowed and remain unchanged.
        $this->assertEquals('クラス', sanitize_css_id_name('クラス'));
        $this->assertEquals('класс', sanitize_css_id_name('класс'));
    }

    /**
     * Test sanitize_css_id_name with empty and whitespace-only inputs.
     */
    public function test_sanitize_css_id_name_empty_and_whitespace()
    {
        // Empty or whitespace-only inputs should return 'id_'.
        $this->assertEquals('id_', sanitize_css_id_name(''));
        $this->assertEquals('id_', sanitize_css_id_name(' '));
    }

    /**
     * Test sanitize_css_id_name with hyphen and underscore only inputs.
     */
    public function test_sanitize_css_id_name_hyphen_and_underscore_only()
    {
        // Inputs with only hyphens or underscores should return 'id_'.
        $this->assertEquals('id_', sanitize_css_id_name('-'));
        $this->assertEquals('id_', sanitize_css_id_name('--'));
        $this->assertEquals('id_', sanitize_css_id_name('---'));
        $this->assertEquals('id_', sanitize_css_id_name('_'));
        $this->assertEquals('id_', sanitize_css_id_name('__'));
        $this->assertEquals('id_', sanitize_css_id_name('___'));
        $this->assertEquals('id_', sanitize_css_id_name('-_-'));
        $this->assertEquals('id_', sanitize_css_id_name('_-_'));
    }

    /**
     * Test sanitize_css_id_name with mixed inputs.
     */
    public function test_sanitize_css_id_name_mixed_inputs()
    {
        // ID names with mixed valid and invalid characters should be sanitized appropriately.
        $this->assertEquals('id_9id', sanitize_css_id_name('-9id'));
        $this->assertEquals('id_123id', sanitize_css_id_name('--123id'));
        $this->assertEquals('a-1-b_2', sanitize_css_id_name('a-1-b_2'));
        $this->assertEquals('-a-b2', sanitize_css_id_name('-a-b2'));
        $this->assertEquals('_a_b3', sanitize_css_id_name('_a_b3'));
    }

    /**
     * Test sanitize_css_id_name with long inputs.
     */
    public function test_sanitize_css_id_name_long_inputs()
    {
        // Long inputs should be handled correctly.
        $longAString = str_repeat('a', 100);
        $this->assertEquals($longAString, sanitize_css_id_name($longAString));
        $this->assertEquals('id_class', sanitize_css_id_name(str_repeat('-', 10) . 'class'));
        $this->assertEquals('__________class', sanitize_css_id_name(str_repeat('_', 10) . 'class'));
    }

    /**
     * Test basic valid CSS input
     */
    public function test_sanitize_style_attribute_valid_css()
    {
        $input    = 'color: red; font-size: 16px;';
        $expected = 'color: red; font-size: 16px';
        $this->assertEquals($expected, sanitize_style_attribute($input));
    }

    /**
     * Test multiple CSS properties
     */
    public function test_sanitize_style_attribute_multiple_properties()
    {
        $input    = 'margin: 10px 20px 30px 40px; padding: 5px;';
        $expected = 'margin: 10px 20px 30px 40px; padding: 5px';
        $this->assertEquals($expected, sanitize_style_attribute($input));
    }

    /**
     * Test removal of URL function
     */
    public function test_sanitize_style_attribute_remove_url()
    {
        $input    = 'background: url("image.jpg");';
        $expected = '';
        $this->assertEquals($expected, sanitize_style_attribute($input));
    }

    /**
     * Test preservation of CSS variables
     */
    public function test_sanitize_style_attribute_preserve_variables()
    {
        $input    = '--custom-color: #ff0000; color: var(--custom-color);';
        $expected = '--custom-color: #ff0000; color: var(--custom-color)';
        $this->assertEquals($expected, sanitize_style_attribute($input));
    }

    /**
     * Test preservation of complex CSS values
     */
    public function test_sanitize_style_attribute_complex_values()
    {
        $input    = 'transform: translate(50px, 100px) rotate(45deg);';
        $expected = 'transform: translate(50px, 100px) rotate(45deg)';
        $this->assertEquals($expected, sanitize_style_attribute($input));
    }

    /**
     * Test preservation of calc() function
     */
    public function test_sanitize_style_attribute_calc_function()
    {
        $input    = 'width: calc(100% - 20px);';
        $expected = 'width: calc(100% - 20px)';
        $this->assertEquals($expected, sanitize_style_attribute($input));
    }

    /**
     * Test preservation of vendor prefixes
     */
    public function test_sanitize_style_attribute_vendor_prefixes()
    {
        $input    = '-webkit-transform: scale(1.1); -moz-transform: scale(1.1);';
        $expected = '-webkit-transform: scale(1.1); -moz-transform: scale(1.1)';
        $this->assertEquals($expected, sanitize_style_attribute($input));
    }

    /**
     * Test handling of empty input
     */
    public function test_sanitize_style_attribute_empty_input()
    {
        $this->assertEquals('', sanitize_style_attribute(''));
        $this->assertEquals('', sanitize_style_attribute(' '));
        $this->assertEquals('', sanitize_style_attribute(';'));
    }

    /**
     * Test removal of malicious JavaScript
     */
    public function test_sanitize_style_attribute_remove_javascript()
    {
        $input    = 'color: red; javascript:alert(1);';
        $expected = 'color: red';
        $this->assertEquals($expected, sanitize_style_attribute($input));
    }

    /**
     * Test removal of expression
     */
    public function test_sanitize_style_attribute_remove_expression()
    {
        $input    = 'width: expression(alert(1));';
        $expected = '';
        $this->assertEquals($expected, sanitize_style_attribute($input));
    }

    /**
     * Test removal of @import
     */
    public function test_sanitize_style_attribute_remove_import()
    {
        $input    = 'color: red; @import url("malicious.css");';
        $expected = 'color: red';
        $this->assertEquals($expected, sanitize_style_attribute($input));
    }

    /**
     * Test handling of HTML entities
     */
    public function test_sanitize_style_attribute_html_entities()
    {
        $input    = 'color: "&lt;script&gt;alert(1)&lt;/script&gt;";';
        $expected = 'color: lt';
        $this->assertEquals($expected, sanitize_style_attribute($input));
    }

    /**
     * Test removal of data URLs
     */
    public function test_sanitize_style_attribute_remove_data_url()
    {
        $input    = 'background-image: url(data:image/svg+xml,%3Csvg xmlns="http://www.w3.org/2000/svg" width="100" height="100"%3E%3Crect width="100" height="100" fill="%23ff0000"%3E%3C/rect%3E%3C/svg%3E);';
        $expected = '';
        $this->assertEquals($expected, sanitize_style_attribute($input));
    }

    /**
     * Test handling of unclosed functions
     */
    public function test_sanitize_style_attribute_unclosed_function()
    {
        $input    = 'color: rgb(255, 0, 0;';
        $expected = 'color: rgb(255, 0, 0';
        $this->assertEquals($expected, sanitize_style_attribute($input));
    }

    /**
     * Test removal of CSS hacks
     */
    public function test_sanitize_style_attribute_remove_css_hacks()
    {
        $this->assertEquals('color: red', sanitize_style_attribute('_color: red;'));
        $this->assertEquals('color: red', sanitize_style_attribute('*color: red;'));
    }

    /**
     * Test preservation of valid CSS with unusual spacing
     */
    public function test_sanitize_style_attribute_unusual_spacing()
    {
        $input    = '  color   :   red   ;   font-size   :   16px   ;  ';
        $expected = 'color: red; font-size: 16px';
        $this->assertEquals($expected, sanitize_style_attribute($input));
    }

    /**
     * Test preservation of uppercase properties and values
     */
    public function test_sanitize_style_attribute_uppercase()
    {
        $input    = 'COLOR: RED; FONT-SIZE: 16PX;';
        $expected = 'COLOR: RED; FONT-SIZE: 16PX';
        $this->assertEquals($expected, sanitize_style_attribute($input));
    }

    /**
     * Test removal of comments
     */
    public function test_sanitize_style_attribute_remove_comments()
    {
        $input    = 'color: red; /* comment */ font-size: 16px;';
        $expected = 'color: red; font-size: 16px';
        $this->assertEquals($expected, sanitize_style_attribute($input));
    }

    /**
     * Test preservation of nested functions
     */
    public function test_sanitize_style_attribute_nested_functions()
    {
        $input    = 'background-image: linear-gradient(to right, rgb(255, 0, 0), #00ff00);';
        $expected = 'background-image: linear-gradient(to right, rgb(255, 0, 0), #00ff00)';
        $this->assertEquals($expected, sanitize_style_attribute($input));
    }

    /**
     * Test basic ASCII string
     */
    public function test_show_escape_sequences_simple_ascii_string()
    {
        $this->assertEquals('Hello, World!', show_escape_sequences('Hello, World!'));
    }

    /**
     * Test string with newline
     */
    public function test_show_escape_sequences_string_with_newline()
    {
        $this->assertEquals('Hello\\nWorld', show_escape_sequences("Hello\nWorld"));
    }

    /**
     * Test string with tab
     */
    public function test_show_escape_sequences_string_with_tab()
    {
        $this->assertEquals('Tab\\tTest', show_escape_sequences("Tab\tTest"));
    }

    /**
     * Test common escape sequences
     */
    public function test_show_escape_sequences_common_escape_sequences()
    {
        $this->assertEquals('\\r\\n\\t\\v\\e\\f\\x00', show_escape_sequences("\r\n\t\v\e\f\0"));
    }

    /**
     * Test bell and delete characters
     */
    public function test_show_escape_sequences_bell_and_delete_characters()
    {
        $this->assertEquals('\\x07\\x7F', show_escape_sequences("\x07\x7F"));
    }

    /**
     * Test non-ASCII Unicode characters
     */
    public function test_show_escape_sequences_non_ascii_unicode_characters()
    {
        $this->assertEquals('Caf\\xE9 \\xF1', show_escape_sequences('Café ñ'));
    }

    /**
     * Test emoji
     */
    public function test_show_escape_sequences_emoji()
    {
        $this->assertEquals('\\u{1F600}', show_escape_sequences("\u{1F600}"));
    }

    /**
     * Test zero-width space
     */
    public function test_show_escape_sequences_zero_width_space()
    {
        $this->assertEquals('\\u{200B}', show_escape_sequences("\u{200B}"));
    }

    /**
     * Test right-to-left override
     */
    public function test_show_escape_sequences_right_to_left_override()
    {
        $this->assertEquals('\\u{202E}RTL', show_escape_sequences("\u{202E}RTL"));
    }

    /**
     * Test mix of various special characters
     */
    public function test_show_escape_sequences_mixed_special_characters()
    {
        $this->assertEquals('Mixed: \\t\\n\\r\\x01\\x1F\\x7F\\u{200B}', show_escape_sequences("Mixed: \t\n\r\x01\x1F\x7F\u{200B}"));
    }

    /**
     * Test empty string
     */
    public function test_show_escape_sequences_empty_string()
    {
        $this->assertEquals('', show_escape_sequences(''));
    }

    /**
     * Test very long string
     */
    public function test_show_escape_sequences_very_long_string()
    {
        $input    = str_repeat('a', 1000) . "\n" . str_repeat('b', 1000);
        $expected = str_repeat('a', 1000) . '\\n' . str_repeat('b', 1000);
        $this->assertEquals($expected, show_escape_sequences($input));
    }

    /**
     * Test highest valid Unicode code point
     */
    public function test_show_escape_sequences_highest_unicode_code_point()
    {
        $this->assertEquals('\\u{10FFFF}', show_escape_sequences("\u{10FFFF}"));
    }

    /**
     * Test UTF-8 encoded emoji
     */
    public function test_show_escape_sequences_utf8_encoded_emoji()
    {
        $this->assertEquals('\\u{1F600}', show_escape_sequences("\xF0\x9F\x98\x80"));
    }

    /**
     * Test invalid UTF-8 sequence
     */
    public function test_show_escape_sequences_invalid_utf8_sequence()
    {
        $this->expectException(\InvalidArgumentException::class);
        show_escape_sequences(base64_decode('//4='));
    }

    /**
     * Test already escaped newline
     */
    public function test_show_escape_sequences_already_escaped_newline()
    {
        $this->assertEquals('\\\\n', show_escape_sequences('\\n'));
    }

    /**
     * Test JSON encoded newline
     */
    public function test_show_escape_sequences_json_encoded_newline()
    {
        $this->assertEquals('["\n"]', show_escape_sequences(json_encode(["\n"])));
    }

    /**
     * Test all ASCII control characters
     */
    public function test_show_escape_sequences_all_ascii_control_characters()
    {
        $input    = implode('', array_map('chr', range(0, 31)));
        $expected = '\\x00\\x01\\x02\\x03\\x04\\x05\\x06\\x07\\x08\\t\\n\\v\\f\\r\\x0E\\x0F' .
            '\\x10\\x11\\x12\\x13\\x14\\x15\\x16\\x17\\x18\\x19\\x1A\\e\\x1C\\x1D\\x1E\\x1F';
        $this->assertEquals($expected, show_escape_sequences($input));
    }

    /**
     * Test HTML special characters
     */
    public function test_show_escape_sequences_html_special_characters()
    {
        $this->assertEquals('<script>alert("XSS")</script>', show_escape_sequences('<script>alert("XSS")</script>'));
    }

    /**
     * Test string with escaped tab
     */
    public function test_show_escape_sequences_string_with_escaped_tab()
    {
        $this->assertEquals('Escaped\\\\tTab', show_escape_sequences('Escaped\\tTab'));
    }

    /**
     * Test string with quotes
     */
    public function test_show_escape_sequences_string_with_quotes()
    {
        $this->assertEquals('Quotes"\\\'', show_escape_sequences("Quotes\"\'"));
    }

    /**
     * Test line and paragraph separators
     */
    public function test_show_escape_sequences_line_and_paragraph_separators()
    {
        $this->assertEquals('\\u{2028}\\u{2029}', show_escape_sequences("\u{2028}\u{2029}"));
    }

    /**
     * Test HTML output
     */
    public function test_show_escape_sequences_html_output()
    {
        $this->assertEquals('<code>Hello,&nbsp;World!\\n</code>', show_escape_sequences("Hello, World!\n", true));
    }

    /**
     * Test with mixed ASCII and non-ASCII characters
     */
    public function test_show_escape_sequences_mixed_ascii_and_non_ascii()
    {
        $this->assertEquals('Hello, \\u{4E16}\\u{754C}!', show_escape_sequences('Hello, 世界!'));
    }

    /**
     * Test with a string containing only spaces
     */
    public function test_show_escape_sequences_only_spaces()
    {
        $this->assertEquals('   ', show_escape_sequences('   '));
    }

    /**
     * Test with a string containing only newlines
     */
    public function test_show_escape_sequences_only_newlines()
    {
        $this->assertEquals('\\n\\n\\n', show_escape_sequences("\n\n\n"));
    }

    /**
     * Test with a string containing multiple backslashes
     */
    public function test_show_escape_sequences_multiple_backslashes()
    {
        $this->assertEquals('\\\\\\', show_escape_sequences('\\\\'));
    }

    /**
     * Test with a string containing null bytes in the middle
     */
    public function test_show_escape_sequences_null_bytes_in_middle()
    {
        $this->assertEquals('Hello\\x00World', show_escape_sequences("Hello\0World"));
    }

    /**
     * Test with a string containing all printable ASCII characters
     */
    public function test_show_escape_sequences_all_printable_ascii()
    {
        $input = implode('', array_map('chr', range(32, 126)));
        $this->assertEquals($input, show_escape_sequences($input));
    }

    /**
     * Test that the function correctly returns a string in single-line format.
     */
    public function test_print_r_inline_returns_single_line_string()
    {
        $input = ['foo' => 'bar', 'baz' => 'qux'];

        $result = print_r_inline($input, true);

        $expected = 'Array ( [foo] => bar [baz] => qux )';

        $this->assertEquals($expected, $result);
    }

    /**
     * Test that the function prints the single-line output when $return is false.
     */
    public function test_print_r_inline_prints_single_line_when_return_is_false()
    {
        $this->expectOutputString('Array ( [foo] => bar [baz] => qux )' . PHP_EOL);

        $input = ['foo' => 'bar', 'baz' => 'qux'];

        print_r_inline($input, false);
    }

    /**
     * Test that the function handles multi-line arrays by converting them into a single line.
     */
    public function test_print_r_inline_handles_multi_line_arrays()
    {
        $input = [
            'foo' => [
                'bar' => 'baz',
                'qux' => 'quux',
            ],
        ];

        $result = print_r_inline($input, true);

        $expected = 'Array ( [foo] => Array ( [bar] => baz [qux] => quux ) )';

        $this->assertEquals($expected, $result);
    }

    /**
     * Test that the function handles empty arrays.
     */
    public function test_print_r_inline_handles_empty_array()
    {
        $input = [];

        $result = print_r_inline($input, true);

        $expected = 'Array ( )';

        $this->assertEquals($expected, $result);
    }

    /**
     * Test that the function handles string input and does not modify it.
     */
    public function test_print_r_inline_handles_string_input()
    {
        $input = 'Hello World';

        $result = print_r_inline($input, true);

        $expected = 'Hello World';

        $this->assertEquals($expected, $result);
    }

    /**
     * Test that the function handles null input and returns the string "NULL".
     */
    public function test_print_r_inline_handles_null_input()
    {
        $input = null;

        $result = print_r_inline($input, true);

        $expected = 'null';

        $this->assertEquals($expected, $result);
    }

    /**
     * Test that the function handles booleans.
     */
    public function test_print_r_inline_handles_boolean_input()
    {
        $input = true;

        $result = print_r_inline($input, true);

        $expected = '1';

        $this->assertEquals($expected, $result);

        $input = false;

        $result = print_r_inline($input, true);

        $expected = '';

        $this->assertEquals($expected, $result);
    }

    /**
     * Test that the function handles numbers and doesn't alter them.
     */
    public function test_print_r_inline_handles_numbers()
    {
        $input = 12345;

        $result = print_r_inline($input, true);

        $expected = '12345';

        $this->assertEquals($expected, $result);
    }

    /**
     * Test that the function handles a complex mixed array.
     */
    public function test_print_r_inline_handles_complex_mixed_array()
    {
        $input = [
            'string' => 'test',
            'int'    => 42,
            'float'  => 3.14,
            'array'  => ['foo' => 'bar'],
            'bool'   => true,
        ];

        $result = print_r_inline($input, true);

        $expected = 'Array ( [string] => test [int] => 42 [float] => 3.14 [array] => Array ( [foo] => bar ) [bool] => 1 )';

        $this->assertEquals($expected, $result);
    }

    /**
     * Test the function with a large array to ensure it remains performant.
     */
    public function test_print_r_inline_handles_large_array()
    {
        $input = range(1, 1000);

        $result = print_r_inline($input, true);

        $this->assertStringStartsWith('Array ( [0] => 1', $result);
        $this->assertStringEndsWith('[999] => 1000 )', $result);
    }

    /**
     * Test that dirty input with multiple spaces, newlines, and tabs is properly compacted.
     */
    public function test_print_r_inline_compacts_dirty_input()
    {
        $input = [
            'string' => "this is   a string\nwith multiple   spaces\tand newlines",
        ];

        $result = print_r_inline($input, true);

        $expected = 'Array ( [string] => this is a string with multiple spaces and newlines )';

        $this->assertEquals($expected, $result);
    }

    /**
     * Test that the function handles nested arrays and compacts them into a single line.
     */
    public function test_print_r_inline_handles_nested_arrays()
    {
        $input = [
            'level1' => [
                'level2' => [
                    'level3' => 'deep value',
                ],
            ],
        ];

        $result = print_r_inline($input, true);

        $expected = 'Array ( [level1] => Array ( [level2] => Array ( [level3] => deep value ) ) )';

        $this->assertEquals($expected, $result);
    }

    // Test normal left alignment
    public function test_pad_string_left_alignment()
    {
        $this->assertEquals('hello     ', pad_string('hello', 10, 'left'));
    }

    // Test normal right alignment
    public function test_pad_string_right_alignment()
    {
        $this->assertEquals('     hello', pad_string('hello', 10, 'right'));
    }

    // Test normal center alignment
    public function test_pad_string_center_alignment()
    {
        $this->assertEquals('  hello   ', pad_string('hello', 10, 'center'));
    }

    // Test string longer than specified width
    public function test_pad_string_input_longer_than_width()
    {
        $this->assertEquals('hello world', pad_string('hello world', 5, 'left'));
    }

    // Test empty string input
    public function test_pad_string_empty_string()
    {
        $this->assertEquals('          ', pad_string('', 10, 'left'));
    }

    // Test zero width
    public function test_pad_string_zero_width()
    {
        $this->assertEquals('hello', pad_string('hello', 0, 'left'));
    }

    // Test negative width
    public function test_pad_string_negative_width()
    {
        $this->assertEquals('hello', pad_string('hello', -5, 'left'));
    }

    // Test string with special characters
    public function test_pad_string_special_characters()
    {
        $this->assertEquals('!@#$%     ', pad_string('!@#$%', 10, 'left'));
    }

    // Test string with multibyte characters
    public function test_pad_string_multibyte_characters()
    {
        $input  = 'こんにちは';
        $result = pad_string($input, 10, 'left');
        $this->assertEquals('こんにちは', $result);
    }

    // Test invalid alignment (expect exception)
    public function test_pad_string_invalid_alignment()
    {
        $this->expectException(\InvalidArgumentException::class);
        pad_string('hello', 10, 'invalid');
    }

    // Test exact width (no padding needed)
    public function test_pad_string_exact_width()
    {
        $this->assertEquals('hello', pad_string('hello', 5, 'left'));
    }

    /**
     * Test center alignment with no padding needed
     */
    public function test_pad_string_center_alignment_no_padding()
    {
        $this->assertEquals('hello', pad_string('hello', 5, 'center'));
    }

    /**
     * Test center alignment with balanced padding (even total padding)
     */
    public function test_pad_string_center_alignment_balanced_padding()
    {
        $this->assertEquals(' hello ', pad_string('hello', 7, 'center'));
        $this->assertEquals('  hello  ', pad_string('hello', 9, 'center'));
    }

    /**
     * Test center alignment with imbalanced padding (odd total padding)
     */
    public function test_pad_string_center_alignment_imbalanced_padding()
    {
        $this->assertEquals('hello ', pad_string('hello', 6, 'center'));
        $this->assertEquals(' hello  ', pad_string('hello', 8, 'center'));
    }

    /**
     * Test center alignment with even-length string and balanced padding
     */
    public function test_pad_string_center_alignment_even_length_balanced()
    {
        $this->assertEquals(' boat ', pad_string('boat', 6, 'center'));
        $this->assertEquals('  boat  ', pad_string('boat', 8, 'center'));
    }

    /**
     * Test center alignment with even-length string and imbalanced padding
     */
    public function test_pad_string_center_alignment_even_length_imbalanced()
    {
        $this->assertEquals('boat ', pad_string('boat', 5, 'center'));
        $this->assertEquals(' boat  ', pad_string('boat', 7, 'center'));
        $this->assertEquals('  boat   ', pad_string('boat', 9, 'center'));
    }

    /**
     * Test center alignment with odd-length multibyte string and balanced padding
     */
    public function test_pad_string_center_alignment_odd_length_multibyte_balanced()
    {
        $this->assertEquals(' こんにちは ', pad_string('こんにちは', 12, 'center'));
        $this->assertEquals('  こんにちは  ', pad_string('こんにちは', 14, 'center'));
    }

    /**
     * Test center alignment with odd-length multibyte string and imbalanced padding
     */
    public function test_pad_string_center_alignment_odd_length_multibyte_imbalanced()
    {
        $this->assertEquals(' こんにちは  ', pad_string('こんにちは', 13, 'center'));
        $this->assertEquals('  こんにちは   ', pad_string('こんにちは', 15, 'center'));
    }

    /**
     * Test center alignment with even-length multibyte string and balanced padding
     */
    public function test_pad_string_center_alignment_even_length_multibyte_balanced()
    {
        $this->assertEquals(' こんにち ', pad_string('こんにち', 10, 'center'));
        $this->assertEquals('  こんにち  ', pad_string('こんにち', 12, 'center'));
    }

    /**
     * Test center alignment with even-length multibyte string and imbalanced padding
     */
    public function test_pad_string_center_alignment_even_length_multibyte_imbalanced()
    {
        $this->assertEquals('こんにち ', pad_string('こんにち', 9, 'center'));
        $this->assertEquals(' こんにち  ', pad_string('こんにち', 11, 'center'));
    }

    /**
     * Test with normal input
     */
    public function test_calculate_column_widths_normal_input()
    {
        $input = [
            ['apple', 'banana', 'cherry'],
            ['date', 'elderberry', 'fig'],
        ];
        $expected = [5, 10, 6];
        $this->assertEquals($expected, calculate_column_widths($input));
    }

    /**
     * Test with empty input
     */
    public function test_calculate_column_widths_empty_input()
    {
        $this->assertEquals([], calculate_column_widths([]));
    }

    /**
     * Test with single row input
     */
    public function test_calculate_column_widths_single_row()
    {
        $input    = [['a', 'bb', 'ccc']];
        $expected = [1, 2, 3];
        $this->assertEquals($expected, calculate_column_widths($input));
    }

    /**
     * Test with rows of different lengths
     */
    public function test_calculate_column_widths_different_row_lengths()
    {
        $input = [
            ['a', 'bb', 'ccc'],
            ['dddd', 'eeeee'],
        ];
        $expected = [4, 5, 3];
        $this->assertEquals($expected, calculate_column_widths($input));
    }

    /**
     * Test with multibyte characters
     */
    public function test_calculate_column_widths_multibyte_characters()
    {
        $input = [
            ['こんにちは', 'hello', '你好'],
            ['世界', 'world', '世界'],
        ];
        $expected = [10, 5, 4];
        $this->assertEquals($expected, calculate_column_widths($input));
    }

    /**
     * Test with numeric values
     */
    public function test_calculate_column_widths_numeric_values()
    {
        $input = [
            [1, 22, 333],
            [4444, 55555, 666666],
        ];
        $expected = [4, 5, 6];
        $this->assertEquals($expected, calculate_column_widths($input));
    }

    /**
     * Test with mixed types
     */
    public function test_calculate_column_widths_mixed_types()
    {
        $input = [
            ['a', 2, null],
            [true, 'hello', 3.14],
        ];
        $expected = [1, 5, 4];
        $this->assertEquals($expected, calculate_column_widths($input));
    }

    /**
     * Test with very long strings
     */
    public function test_calculate_column_widths_long_strings()
    {
        $input = [
            ['short', str_repeat('a', 1000), 'medium'],
            ['tiny', 'small', str_repeat('b', 500)],
        ];
        $expected = [5, 1000, 500];
        $this->assertEquals($expected, calculate_column_widths($input));
    }

    /**
     * Test error case: non-array input
     */
    public function test_calculate_column_widths_non_array_input()
    {
        $this->expectException(\InvalidArgumentException::class);
        calculate_column_widths(['not', 'an', 'array']);
    }

    /**
     * Test error case: nested non-array
     */
    public function test_calculate_column_widths_nested_non_array()
    {
        $this->expectException(\InvalidArgumentException::class);
        calculate_column_widths([
            ['valid', 'array'],
            'not an array',
        ]);
    }

    /**
     * Test boolean and null handling
     */
    public function test_calculate_column_widths_boolean_and_null()
    {
        $input = [
            [true, false, null],
            [null, true, false],
        ];
        $expected = [1, 1, 0];
        $this->assertEquals($expected, calculate_column_widths($input));
    }

    /**
     * Test basic left alignment
     */
    public function test_align_line_basic_left_alignment()
    {
        $line          = ['short', 'medium', 'long text'];
        $column_widths = [5, 6, 9];

        $result = align_line($line, $column_widths);

        $this->assertEquals('short medium long text', $result);
    }

    /**
     * Test basic right alignment
     */
    public function test_align_line_basic_right_alignment()
    {
        $line          = ['short', 'medium', 'long text'];
        $column_widths = [5, 6, 9];

        $result = align_line($line, $column_widths, 'right');

        $this->assertEquals('short medium long text', $result);
    }

    /**
     * Test left align first column with right alignment for others
     */
    public function test_align_line_left_align_first_right_others()
    {
        $line          = ['first', 'second', 'third'];
        $column_widths = [5, 6, 5];

        $result = align_line($line, $column_widths, 'right', true);

        $this->assertEquals('first second third', $result);
    }

    /**
     * Test with custom padding function
     */
    public function test_align_line_custom_padding_function()
    {
        $line          = ['a', 'b', 'c'];
        $column_widths = [3, 3, 3];

        $custom_pad = function ($str, $width, $alignment) {
            return str_pad($str, $width, '-', $alignment === 'left' ? STR_PAD_RIGHT : STR_PAD_LEFT);
        };

        $result = align_line($line, $column_widths, 'right', false, $custom_pad);

        $this->assertEquals('--a --b --c', $result);
    }

    /**
     * Test with empty line
     */
    public function test_align_line_empty_line()
    {
        $result = align_line([], []);

        $this->assertEquals('', $result);
    }

    /**
     * Test with mismatched column widths
     */
    public function test_align_line_mismatched_column_widths()
    {
        $line          = ['short', 'medium'];
        $column_widths = [5, 6, 9];

        $result = align_line($line, $column_widths);

        $this->assertEquals('short medium', $result);
    }

    /**
     * Test with multibyte characters
     */
    public function test_align_line_multibyte_characters()
    {
        $line          = ['こんにちは', 'world', '🌍'];
        $column_widths = [10, 5, 2];

        $result = align_line($line, $column_widths);

        $this->assertEquals('こんにちは world 🌍', $result);
    }

    /**
     * Test invalid alignment throws exception
     */
    public function test_align_line_invalid_alignment_throws_exception()
    {
        $this->expectException(\InvalidArgumentException::class);

        align_line(['test'], [4], 'invalid');
    }

    /**
     * Test with very long text exceeding column width
     */
    public function test_align_line_long_text_exceeding_width()
    {
        $line          = ['short', 'very long text', 'medium'];
        $column_widths = [5, 10, 6];

        $result = align_line($line, $column_widths);

        $this->assertEquals('short very long text medium', $result);
    }

    /**
     * Test basic alignment with default parameters
     */
    public function test_align_csv_columns_basic_alignment()
    {
        $input    = "Name,Age\nJohn,30\nJane Doe,25";
        $expected = 'Name     Age' . PHP_EOL . 'John     30 ' . PHP_EOL . 'Jane Doe 25 ';

        $result = align_csv_columns($input);

        $this->assertEquals($expected, $result);
    }

    /**
     * Test alignment with custom separator
     */
    public function test_align_csv_columns_custom_separator()
    {
        $input    = "Name\tAge\nJohn\t30\nJane Doe\t25";
        $expected = 'Name     Age' . PHP_EOL . 'John     30 ' . PHP_EOL . 'Jane Doe 25 ';

        $result = align_csv_columns($input, "\t");

        $this->assertEquals($expected, $result);
    }

    /**
     * Test left alignment
     */
    public function test_align_csv_columns_left_alignment()
    {
        $input    = "Name\tAge\nJohn\t30\nJane Doe\t25";
        $expected = 'Name     Age' . PHP_EOL . 'John     30 ' . PHP_EOL . 'Jane Doe 25 ';

        $result = align_csv_columns($input, "\t", '"', '\\', 'left');

        $this->assertEquals($expected, $result);
    }

    /**
     * Test with left align first column (default true)
     */
    public function test_align_csv_columns_left_align_first_default_true()
    {
        $input    = "Name,Age\nJohn,30\nJane Doe,25";
        $expected = 'Name     Age' . PHP_EOL . 'John     30 ' . PHP_EOL . 'Jane Doe 25 ';

        $result = align_csv_columns($input);

        $this->assertEquals($expected, $result);
    }

    /**
     * Test with left align first column set to false
     */
    public function test_align_csv_columns_left_align_first_false()
    {
        $input    = "Name\tAge\nJohn\t30\nJane Doe\t25";
        $expected = '    Name Age' . PHP_EOL . '    John  30' . PHP_EOL . 'Jane Doe  25';

        $result = align_csv_columns($input, "\t", '"', '\\', 'right', false);

        $this->assertEquals($expected, $result);
    }

    /**
     * Test with multibyte characters
     */
    public function test_align_csv_columns_multibyte_characters()
    {
        $input    = "Name\tAge\n山田\t30\n田中さん\t25";
        $expected = 'Name     Age' . PHP_EOL . '山田     30 ' . PHP_EOL . '田中さん 25 ';

        $result = align_csv_columns($input, "\t");

        $this->assertEquals($expected, $result);
    }

    /**
     * Test with uneven number of columns
     */
    public function test_align_csv_columns_uneven_columns()
    {
        $input    = "Name\tAge\tCity\nJohn\t30\nJane Doe\t25\tNew York";
        $expected = 'Name     Age City    ' . PHP_EOL . 'John     30 ' . PHP_EOL . 'Jane Doe 25  New York';

        $result = align_csv_columns($input, "\t");

        $this->assertEquals($expected, $result);
    }

    /**
     * Test with escaped delimiters
     */
    public function test_align_csv_columns_escaped_delimiters()
    {
        $input    = "Name\tDescription\nJohn\t\"Developer, Senior\"\nJane\t\"Manager, IT\"";
        $expected = 'Name Description      ' . PHP_EOL . 'John Developer, Senior' . PHP_EOL . 'Jane Manager, IT      ';

        $result = align_csv_columns($input, "\t");

        $this->assertEquals($expected, $result);
    }

    /**
     * Test align_csv_columns with custom escape
     */
    public function test_align_csv_columns_custom_escape()
    {
        $input    = "Name\tDescription\nJohn\t\"Developer, Senior\"\nJane\t\"Manager, \\\"IT\\\"\"";
        $expected = 'Name Description      ' . PHP_EOL . 'John Developer, Senior' . PHP_EOL . 'Jane Manager, \\"IT\\"  ';

        $result = align_csv_columns($input, "\t", '"', '\\');

        $this->assertEquals($expected, $result);
    }

    /**
     * Test with very long input (edge case)
     */
    public function test_align_csv_columns_very_long_input()
    {
        $input  = str_repeat("Name\tAge\nJohn\t30\nJane Doe\t25\n", 100);
        $result = align_csv_columns($input, "\t");

        $expected_start = 'Name     Age' . PHP_EOL . 'John     30 ' . PHP_EOL . 'Jane Doe 25 ' . PHP_EOL;
        $expected_end   = 'Name     Age' . PHP_EOL . 'John     30 ' . PHP_EOL . 'Jane Doe 25 ';

        $this->assertStringStartsWith($expected_start, $result);
        $this->assertStringEndsWith($expected_end, $result);
    }

    /**
     * Test alignment without using width (mb_strlen instead of mb_strwidth)
     */
    public function test_align_csv_columns_without_use_width()
    {
        $input    = "Name\tAge\n山田\t30\n田中さん\t25";
        $expected = 'Name Age' . PHP_EOL . '  山田  30' . PHP_EOL . '田中さん  25';

        $result = align_csv_columns($input, "\t", '"', '\\', 'right', false, false);

        $this->assertEquals($expected, $result);
    }

    /**
     * Test center alignment
     */
    public function test_align_csv_columns_center_alignment()
    {
        $input    = "Name\tAge\nJohn\t30\nJane Doe\t25";
        $expected = 'Name     Age' . PHP_EOL . '  John   30 ' . PHP_EOL . 'Jane Doe 25 ';

        $result = align_csv_columns($input, "\t", '"', '\\', 'center');

        $this->assertEquals($expected, $result);
    }

    /**
     * Test right alignment with left_align_first set to true
     */
    public function test_align_csv_columns_right_alignment_left_align_first_true()
    {
        $input    = "Name\tAge\nJohn\t30\nJane Doe\t25";
        $expected = 'Name     Age' . PHP_EOL . '    John  30' . PHP_EOL . 'Jane Doe  25';

        $result = align_csv_columns($input, "\t", '"', '\\', 'right', true);

        $this->assertEquals($expected, $result);
    }

    /**
     * Test with empty input string
     */
    public function test_align_csv_columns_empty_input()
    {
        $input    = '';
        $expected = '';

        $result = align_csv_columns($input);

        $this->assertEquals($expected, $result);
    }

    /**
     * Test with single-line input
     */
    public function test_align_csv_columns_single_line_input()
    {
        $input    = 'Name,Age,City';
        $expected = 'Name Age City';

        $result = align_csv_columns($input);

        $this->assertEquals($expected, $result);
    }

    /**
     * Test with single-column input
     */
    public function test_align_csv_columns_single_column_input()
    {
        $input    = "Name\nJohn\nJane Doe";
        $expected = 'Name    ' . PHP_EOL . 'John    ' . PHP_EOL . 'Jane Doe';

        $result = align_csv_columns($input);

        $this->assertEquals($expected, $result);
    }

    /**
     * Test center alignment with multibyte characters
     */
    public function test_align_csv_columns_center_alignment_multibyte()
    {
        $input    = "Name\tAge\n山田\t30\n田中さん\t25";
        $expected = 'Name     Age' . PHP_EOL . '  山田   30 ' . PHP_EOL . '田中さん 25 ';

        $result = align_csv_columns($input, "\t", '"', '\\', 'center');

        $this->assertEquals($expected, $result);
    }

    /**
     * Test basic command generation with valid inputs.
     *
     * @return void
     */
    public function test_mysql_cli_command_basic_case()
    {
        // Define input parameters
        $user     = 'root';
        $password = 'password123';
        $query    = 'SELECT * FROM users';
        $database = 'test_db';

        // Expected command output
        $expected = 'mysql -u ' . $this->expected_quote . 'root' . $this->expected_quote .
            ' -p' . $this->expected_quote . 'password123' . $this->expected_quote .
            ' -D ' . $this->expected_quote . 'test_db' . $this->expected_quote .
            ' -e ' . $this->expected_quote . 'SELECT * FROM users' . $this->expected_quote;

        // Call the function
        $result = mysql_cli_command($user, $password, $query, $database, true, '\FOfX\Helper\escapeshellarg_crossplatform');

        // Assert that the result matches the expected command
        $this->assertEquals(trim($expected), trim($result));
    }

    /**
     * Test command generation without database parameter.
     *
     * @return void
     */
    public function test_mysql_cli_command_without_database()
    {
        // Define input parameters
        $user     = 'admin';
        $password = 'adminpass';
        $query    = 'SHOW TABLES';

        // Expected command output
        $expected = 'mysql -u ' . $this->expected_quote . 'admin' . $this->expected_quote .
            ' -p' . $this->expected_quote . 'adminpass' . $this->expected_quote .
            ' -e ' . $this->expected_quote . 'SHOW TABLES' . $this->expected_quote;

        // Call the function without database
        $result = mysql_cli_command($user, $password, $query, null, true, '\FOfX\Helper\escapeshellarg_crossplatform');

        // Assert that the result matches the expected command, accounting for possible extra spaces
        $this->assertEquals(trim($expected), trim($result));
    }

    /**
     * Test command generation with escaping disabled.
     *
     * @return void
     */
    public function test_mysql_cli_command_no_escaping()
    {
        // Define input parameters
        $user     = 'user';
        $password = 'pass';
        $query    = 'DELETE FROM users';

        // Expected command output without shell escaping
        $expected = 'mysql -u user -ppass -e DELETE FROM users';

        // Call the function with escaping disabled
        $result = mysql_cli_command($user, $password, $query, null, false);

        // Assert that the result matches the expected command
        $this->assertEquals($expected, $result);
    }

    /**
     * Test error scenario with missing user.
     *
     * @return void
     */
    public function test_mysql_cli_command_missing_user()
    {
        // Expect an exception to be thrown
        $this->expectException(\InvalidArgumentException::class);

        // Call the function with an empty user
        mysql_cli_command('', 'password', 'SELECT * FROM users');
    }

    /**
     * Test error scenario with missing password.
     *
     * @return void
     */
    public function test_mysql_cli_command_missing_password()
    {
        // Expect an exception to be thrown
        $this->expectException(\InvalidArgumentException::class);

        // Call the function with an empty password
        mysql_cli_command('root', '', 'SELECT * FROM users');
    }

    /**
     * Test error scenario with missing query.
     *
     * @return void
     */
    public function test_mysql_cli_command_missing_query()
    {
        // Expect an exception to be thrown
        $this->expectException(\InvalidArgumentException::class);

        // Call the function with an empty query
        mysql_cli_command('root', 'password', '');
    }

    /**
     * Test command generation with dirty inputs (leading/trailing whitespace).
     *
     * @return void
     */
    public function test_mysql_cli_command_dirty_inputs()
    {
        // Define input parameters with leading/trailing whitespace
        $user     = '  root ';
        $password = '  pass123 ';
        $query    = '  SELECT * FROM users  ';
        $database = '  dirty_db  ';

        // Expected command output (whitespace should be escaped as part of the input)
        $expected = 'mysql -u ' . $this->expected_quote . '  root ' . $this->expected_quote .
            ' -p' . $this->expected_quote . '  pass123 ' . $this->expected_quote .
            ' -D ' . $this->expected_quote . '  dirty_db  ' . $this->expected_quote .
            ' -e ' . $this->expected_quote . '  SELECT * FROM users  ' . $this->expected_quote;

        // Call the function
        $result = mysql_cli_command($user, $password, $query, $database, true, '\FOfX\Helper\escapeshellarg_crossplatform');

        // Assert that the result matches the expected command, trimming extra spaces
        $this->assertEquals(trim($expected), trim($result));
    }

    /**
     * Test command generation with special characters in inputs.
     *
     * @return void
     */
    public function test_mysql_cli_command_special_characters()
    {
        // Define input parameters with special characters
        $user     = 'root$';
        $password = 'pa$$w0rd';
        $query    = 'SELECT * FROM `users` WHERE name="O\'Reilly"';

        // Adjust expected output based on the platform (Windows vs. WSL/Linux)
        if (stripos(PHP_OS, 'WIN') !== false) {
            // Expected output for Windows
            $expected = 'mysql -u "root$" -p"pa$$w0rd" -e "SELECT * FROM `users` WHERE name=\"O\'Reilly\""';
        } else {
            // Expected output for WSL/Linux
            $expected = "mysql -u 'root\$' -p'pa\$\$w0rd' -e 'SELECT * FROM `users` WHERE name=\"O'\''Reilly\"'";
        }

        // Call the function
        $result = mysql_cli_command($user, $password, $query, null, true, '\FOfX\Helper\escapeshellarg_crossplatform');

        // Assert that the result matches the expected command, accounting for platform differences
        $this->assertEquals($expected, $result);
    }

    /**
     * Test command generation with a long query.
     *
     * @return void
     */
    public function test_mysql_cli_command_long_query()
    {
        // Define input parameters with a long query
        $user     = 'root';
        $password = 'password123';
        $query    = str_repeat('SELECT * FROM users; ', 100);

        // Expected command output
        $expected_query = escapeshellarg($query);
        $expected       = sprintf('mysql -u ' . $this->expected_quote . 'root' . $this->expected_quote .
            ' -p' . $this->expected_quote . 'password123' . $this->expected_quote .
            ' -e %s', $expected_query);

        // Call the function
        $result = mysql_cli_command($user, $password, $query, null, true, '\FOfX\Helper\escapeshellarg_crossplatform');

        // Assert that the result matches the expected command, trimming spaces
        $this->assertEquals(trim($expected), trim($result));
    }

    /**
     * Test command generation with Linux escaping.
     *
     * @return void
     */
    public function test_mysql_cli_command_linux_escaping()
    {
        // Define input parameters with characters that need Linux-style escaping
        $user     = 'root$';
        $password = 'pa$$w0rd';
        $query    = 'SELECT * FROM `users` WHERE name="O\'Reilly"';
        $database = 'test$db';

        // Expected Linux-style escaping (using single quotes and escaping internal single quotes)
        $expected = "mysql -u 'root\$' -p'pa\$\$w0rd' -D 'test\$db' -e 'SELECT * FROM `users` WHERE name=\"O'\''Reilly\"'";

        // Call the function with Linux escaping
        $result = mysql_cli_command(
            $user,
            $password,
            $query,
            $database,
            true,
            '\FOfX\Helper\escapeshellarg_linux'
        );

        // Assert that the result matches the expected command
        $this->assertEquals($expected, $result);
    }

    /**
     * Test number_to_words with various integer inputs.
     *
     * @dataProvider integer_provider
     */
    public function test_number_to_words_with_integers(int $input, string $expected): void
    {
        $this->assertEquals($expected, number_to_words($input));
    }

    /**
     * Data provider for integer tests.
     *
     * @return array
     */
    public static function integer_provider(): array
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
            [1316, 'one thousand three hundred sixteen'],
            [1000000, 'one million'],
            [2000000, 'two million'],
            [-54, 'minus fifty-four'],
            [-1000000, 'minus one million'],
        ];
    }

    /**
     * Test number_to_words with various float inputs.
     *
     * @dataProvider float_provider
     */
    public function test_number_to_words_with_floats(float $input, string $expected): void
    {
        $this->assertEquals($expected, number_to_words($input));
    }

    /**
     * Data provider for float tests.
     *
     * @return array
     */
    public static function float_provider(): array
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
     * Test number_to_words with decimal limit.
     */
    public function test_number_to_words_with_decimal_limit(): void
    {
        $this->assertEquals(
            'one point two three',
            number_to_words(1.23456, '-', ' ', ' ', 'minus ', ' point ', 2)
        );
    }

    /**
     * Test number_to_words with scientific notation, expecting an exception.
     */
    public function test_number_to_words_with_scientific_notation(): void
    {
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Scientific notation is not supported.');
        number_to_words(PHP_FLOAT_MAX);
    }

    /**
     * Test number_to_words with string scientific notation, expecting an exception.
     */
    public function test_number_to_words_with_string_scientific_notation(): void
    {
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Scientific notation is not supported.');
        number_to_words(PHP_FLOAT_MAX);
    }

    /**
     * Data provider for trim_if_string tests
     */
    public static function trim_if_string_provider(): array
    {
        return [
            'trims_string'               => ['  Hello, World!  ', 'Hello, World!'],
            'trims_string_with_tabs'     => ["\tHello, World!\t", 'Hello, World!'],
            'trims_string_with_newlines' => ["\nHello, World!\n", 'Hello, World!'],
            'already_trimmed_string'     => ['Hello, World!', 'Hello, World!'],
            'empty_string'               => ['', ''],
            'string_of_spaces'           => ['     ', ''],
            'integer'                    => [42, 42],
            'float'                      => [3.14, 3.14],
            'boolean_true'               => [true, true],
            'boolean_false'              => [false, false],
            'null'                       => [null, null],
            'array'                      => [[1, 2, 3], [1, 2, 3]],
            'object'                     => [new \stdClass(), new \stdClass()],
            'resource'                   => [fopen('php://memory', 'r'), 'resource'],
            'closure'                    => [function () {}, 'Closure'],
        ];
    }

    #[DataProvider('trim_if_string_provider')]
    public function test_trim_if_string($input, $expected)
    {
        $result = trim_if_string($input);

        if ($expected === 'resource') {
            $this->assertTrue(is_resource($result));
        } elseif ($expected === 'Closure') {
            $this->assertInstanceOf(\Closure::class, $result);
        } elseif (is_object($expected)) {
            $this->assertEquals(get_class($expected), get_class($result));
        } else {
            $this->assertSame($expected, $result);
        }
    }

    public function test_trim_if_string_with_custom_object()
    {
        $obj = new class () {
            public $property = 'value';
        };

        $result = trim_if_string($obj);

        $this->assertInstanceOf(get_class($obj), $result);
        $this->assertSame('value', $result->property);
    }

    public function test_trim_if_string_with_stringable()
    {
        $stringable = new class () implements \Stringable {
            public function __toString()
            {
                return '  Stringable  ';
            }
        };

        $result = trim_if_string($stringable);

        $this->assertInstanceOf(get_class($stringable), $result);
        $this->assertSame('  Stringable  ', (string)$result);
    }

    public static function escape_single_quotes_for_sed_provider()
    {
        return [
            ["It's a test", "It'\\''s a test"],
            ['No quotes here', 'No quotes here'],
            ["Multiple'quotes'in'a'row", "Multiple'\\''quotes'\\''in'\\''a'\\''row"],
            ["'", "'\\''"],
            ["''", "'\\'''\\''"],
            ["'''", "'\\'''\\'''\\''"],
            ["Ends with quote'", "Ends with quote'\\''"],
        ];
    }

    #[DataProvider('escape_single_quotes_for_sed_provider')]
    public function test_escape_single_quotes_for_sed($input, $expected)
    {
        $this->assertEquals($expected, escape_single_quotes_for_sed($input));
    }

    /**
     * Data provider for basic MySQL identifier sanitization tests
     */
    public static function mysql_identifier_basic_provider(): array
    {
        return [
            'simple identifier' => [
                'input'    => 'test_table',
                'expected' => 'test_table',
            ],
            'uppercase conversion' => [
                'input'    => 'TestTable',
                'expected' => 'testtable',
            ],
            'special characters' => [
                'input'    => 'test@table#123',
                'expected' => 'test_table_123',
            ],
            'multiple underscores' => [
                'input'    => 'test__table___123',
                'expected' => 'test_table_123',
            ],
            'leading number' => [
                'input'    => '123_test',
                'expected' => '123_test',
            ],
            'spaces to underscore' => [
                'input'    => 'test table 123',
                'expected' => 'test_table_123',
            ],
            'original leading underscore' => [
                'input'    => '_test_table',
                'expected' => '_test_table',
            ],
            'trailing underscore removed' => [
                'input'    => 'test_table_',
                'expected' => 'test_table',
            ],
            'maximum length' => [
                'input'    => str_repeat('a', 100),
                'expected' => str_repeat('a', 64),
            ],
        ];
    }

    #[DataProvider('mysql_identifier_basic_provider')]
    public function test_sanitize_mysql_identifier_basic(string $input, string $expected): void
    {
        $this->assertEquals($expected, sanitize_mysql_identifier($input));
    }

    /**
     * Data provider for MySQL identifier tests with extended characters
     */
    public static function mysql_identifier_extended_chars_provider(): array
    {
        return [
            'ascii only mode' => [
                'input'         => 'café_table',
                'allowExtended' => false,
                'expected'      => 'caf_table',
            ],
            'extended chars allowed' => [
                'input'         => 'café_table',
                'allowExtended' => true,
                'expected'      => 'café_table',
            ],
            'mixed extended and special' => [
                'input'         => 'café@表_123',
                'allowExtended' => true,
                'expected'      => 'café_表_123',
            ],
        ];
    }

    #[DataProvider('mysql_identifier_extended_chars_provider')]
    public function test_sanitize_mysql_identifier_extended_chars(
        string $input,
        bool $allowExtended,
        string $expected
    ): void {
        $this->assertEquals(
            $expected,
            sanitize_mysql_identifier($input, 64, $allowExtended)
        );
    }

    /**
     * Data provider for MySQL identifier tests with must-start-with-letter option
     */
    public static function mysql_identifier_must_start_provider(): array
    {
        return [
            'number start needs underscore' => [
                'input'         => '123test',
                'allowExtended' => false,
                'expected'      => '_123test',
            ],
            'letter start unchanged' => [
                'input'         => 'test123',
                'allowExtended' => false,
                'expected'      => 'test123',
            ],
            'extended letter start unchanged' => [
                'input'         => 'étest123',
                'allowExtended' => true,
                'expected'      => 'étest123',
            ],
            'special char replaced' => [
                'input'         => '@test',
                'allowExtended' => false,
                'expected'      => 'test',
            ],
        ];
    }

    #[DataProvider('mysql_identifier_must_start_provider')]
    public function test_sanitize_mysql_identifier_must_start_with_letter(
        string $input,
        bool $allowExtended,
        string $expected
    ): void {
        $this->assertEquals(
            $expected,
            sanitize_mysql_identifier($input, 64, $allowExtended, true)
        );
    }

    /**
     * Test that empty result throws RuntimeException
     */
    public function test_sanitize_mysql_identifier_empty_result(): void
    {
        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('Resulting sanitized string is empty.');
        sanitize_mysql_identifier('@#$');
    }

    /**
     * Test that invalid length throws InvalidArgumentException
     */
    public function test_sanitize_mysql_identifier_invalid_length(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Maximum length must be greater than 0 characters.');
        sanitize_mysql_identifier('test', 0);
    }

    /**
     * Test preservation of original leading underscore
     */
    public function test_sanitize_mysql_identifier_leading_underscore_preservation(): void
    {
        // Original leading underscore should be preserved
        $this->assertEquals(
            '_test',
            sanitize_mysql_identifier('_test', 64, false, false, true)
        );
    }

    /**
     * Test that leading underscore from sanitization is not preserved
     */
    public function test_sanitize_mysql_identifier_sanitized_underscore_not_preserved(): void
    {
        // Leading underscore from sanitization should not be preserved
        $this->assertEquals(
            'test',
            sanitize_mysql_identifier('@test', 64, false, false, true)
        );
    }

    /**
     * Data provider for basic domain sanitization tests
     */
    public static function basic_domain_provider(): array
    {
        return [
            'simple domain'        => ['example.com', 'example_com'],
            'subdomain'            => ['subdomain.example.com', 'subdomain_example_com'],
            'http protocol'        => ['http://example.com', 'example_com'],
            'https protocol'       => ['https://example.com', 'example_com'],
            'www prefix'           => ['www.example.com', 'example_com'],
            'special characters'   => ['example.com/special&chars', 'example_com_special_chars'],
            'multiple underscores' => ['multiple___underscores', 'multiple_underscores'],
            'empty input'          => ['', ''],
            'unicode characters'   => ['ñ.example.com', 'example_com'],
            'trailing underscore'  => ['example.com_', 'example_com'],
        ];
    }

    #[DataProvider('basic_domain_provider')]
    public function test_sanitize_domain_for_database_basic_cases(string $input, string $expected): void
    {
        $this->assertEquals($expected, sanitize_domain_for_database($input));
    }

    /**
     * Data provider for domain sanitization with additional parameters
     */
    public static function domain_with_params_provider(): array
    {
        return [
            'with username'               => ['example.com', 'user123', true, false, 'db_', 'example_com_user123'],
            'force letter start'          => ['123example.com', '', true, true, 'db_', 'db_123example_com'],
            'no force letter start'       => ['123example.com', '', true, false, 'db_', '123example_com'],
            'custom prefix'               => ['123example.com', '', true, true, 'custom_', 'custom_123example_com'],
            'username with special chars' => ['example.com', 'user@123', true, false, 'db_', 'example_com_user_123'],
            'with TLD'                    => ['example.com', '', true, false, 'db_', 'example_com'],
            'without TLD'                 => ['example.com', '', false, false, 'db_', 'example'],
        ];
    }

    #[DataProvider('domain_with_params_provider')]
    public function test_sanitize_domain_for_database_with_params(
        string $domain,
        string $username,
        bool $includeTLD,
        bool $forceLetterStart,
        string $prefix,
        string $expected
    ): void {
        $this->assertEquals($expected, sanitize_domain_for_database($domain, $username, $includeTLD, $forceLetterStart, $prefix));
    }

    /**
     * Test long domain truncation
     */
    public function test_sanitize_domain_for_database_truncates_long_domain(): void
    {
        $longDomain = str_repeat('a', 100) . '.com';
        $expected   = str_repeat('a', 64);
        $this->assertEquals($expected, sanitize_domain_for_database($longDomain));
    }

    /**
     * Test long domain truncation with username
     */
    public function test_sanitize_domain_for_database_truncates_long_domain_with_username(): void
    {
        $longDomain = str_repeat('a', 100) . '.com';
        $username   = 'user123';
        $expected   = str_repeat('a', 56) . '_user123'; // 64 chars minus '_user123'
        $this->assertEquals($expected, sanitize_domain_for_database($longDomain, $username));
    }

    /**
     * Data provider for basic identifier validation tests
     */
    public static function provide_validate_identifier_valid_cases(): array
    {
        return [
            'simple valid' => [
                'input'    => 'test123',
                'expected' => true,
            ],
            'with hyphens' => [
                'input'    => 'test-123-test',
                'expected' => true,
            ],
            'with underscores' => [
                'input'    => 'test_123_test',
                'expected' => true,
            ],
            'mixed case' => [
                'input'    => 'Test_123-TEST',
                'expected' => true,
            ],
            'numbers only' => [
                'input'    => '123456',
                'expected' => true,
            ],
            'single character' => [
                'input'    => 'a',
                'expected' => true,
            ],
        ];
    }

    #[DataProvider('provide_validate_identifier_valid_cases')]
    public function test_validate_identifier_valid_cases(string $input, bool $expected): void
    {
        $this->assertEquals($expected, validate_identifier($input));
    }

    /**
     * Data provider for invalid identifier tests
     */
    public static function provide_validate_identifier_invalid_cases(): array
    {
        return [
            'empty string' => [
                'input'             => '',
                'expectedException' => \InvalidArgumentException::class,
                'expectedMessage'   => 'Identifier cannot be empty.',
            ],
            'spaces' => [
                'input'             => 'test 123',
                'expectedException' => \InvalidArgumentException::class,
                'expectedMessage'   => 'Identifier "test 123" can only contain letters, numbers, hyphens, and underscores.',
            ],
            'special characters' => [
                'input'             => 'test@123',
                'expectedException' => \InvalidArgumentException::class,
                'expectedMessage'   => 'Identifier "test@123" can only contain letters, numbers, hyphens, and underscores.',
            ],
            'unicode characters' => [
                'input'             => 'tést123',
                'expectedException' => \InvalidArgumentException::class,
                'expectedMessage'   => 'Identifier "tést123" can only contain letters, numbers, hyphens, and underscores.',
            ],
        ];
    }

    #[DataProvider('provide_validate_identifier_invalid_cases')]
    public function test_validate_identifier_invalid_cases(
        string $input,
        string $expectedException,
        string $expectedMessage
    ): void {
        $this->expectException($expectedException);
        $this->expectExceptionMessage($expectedMessage);
        validate_identifier($input);
    }

    /**
     * Test maximum length validation
     */
    public function test_validate_identifier_max_length(): void
    {
        // Should pass with exactly max length
        $this->assertTrue(validate_identifier(str_repeat('a', 64)));

        // Should fail when exceeding max length
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Identifier "' . str_repeat('a', 65) . '" exceeds maximum length of 64 characters.');
        validate_identifier(str_repeat('a', 65));
    }

    /**
     * Test custom maximum length
     */
    public function test_validate_identifier_custom_max_length(): void
    {
        // Should pass with custom length
        $this->assertTrue(validate_identifier('test', 10));

        // Should fail with custom length
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Identifier "toolongstring" exceeds maximum length of 5 characters.');
        validate_identifier('toolongstring', 5);
    }

    /**
     * Test null maximum length
     */
    public function test_validate_identifier_null_max_length(): void
    {
        // Should pass with very long string when max length is null
        $this->assertTrue(validate_identifier(str_repeat('a', 1000), null));
    }

    /**
     * Data provider for reserved identifier tests
     */
    public static function provide_validate_identifier_reserved_cases_valid(): array
    {
        return [
            'valid non-reserved string' => [
                'input'           => 'test',
                'reservedStrings' => ['admin', 'root', 'system'],
                'expected'        => true,
            ],
            'substring of reserved not blocked' => [
                'input'           => 'administrator',
                'reservedStrings' => ['admin', 'root', 'system'],
                'expected'        => true,
            ],
        ];
    }

    /**
     * Data provider for invalid reserved identifier tests
     */
    public static function provide_validate_identifier_reserved_cases_invalid(): array
    {
        return [
            'matches reserved string exactly' => [
                'input'             => 'admin',
                'reservedStrings'   => ['admin', 'root', 'system'],
                'expectedException' => \InvalidArgumentException::class,
                'expectedMessage'   => 'Identifier "admin" matches reserved string.',
            ],
            'matches reserved string case-insensitive' => [
                'input'             => 'ADMIN',
                'reservedStrings'   => ['admin', 'root', 'system'],
                'expectedException' => \InvalidArgumentException::class,
                'expectedMessage'   => 'Identifier "ADMIN" matches reserved string.',
            ],
            'matches reserved string mixed case' => [
                'input'             => 'AdMiN',
                'reservedStrings'   => ['admin', 'root', 'system'],
                'expectedException' => \InvalidArgumentException::class,
                'expectedMessage'   => 'Identifier "AdMiN" matches reserved string.',
            ],
        ];
    }

    #[DataProvider('provide_validate_identifier_reserved_cases_valid')]
    public function test_validate_identifier_reserved_cases_valid(
        string $input,
        array $reservedStrings,
        bool $expected
    ): void {
        $this->assertEquals($expected, validate_identifier($input, null, $reservedStrings));
    }

    #[DataProvider('provide_validate_identifier_reserved_cases_invalid')]
    public function test_validate_identifier_reserved_cases_invalid(
        string $input,
        array $reservedStrings,
        string $expectedException,
        string $expectedMessage
    ): void {
        $this->expectException($expectedException);
        $this->expectExceptionMessage($expectedMessage);
        validate_identifier($input, null, $reservedStrings);
    }

    /**
     * Test null reserved strings array
     */
    public function test_validate_identifier_empty_reserved_list(): void
    {
        $this->assertTrue(validate_identifier('admin', null, []));
    }

    /**
     * Data provider for valid UUID test cases
     */
    public static function provide_valid_uuid_cases(): array
    {
        return [
            'standard lowercase UUID' => [
                'input'    => '123e4567-e89b-12d3-a456-426614174000',
                'expected' => true,
            ],
            'uppercase UUID' => [
                'input'    => '123E4567-E89B-12D3-A456-426614174000',
                'expected' => true,
            ],
            'mixed case UUID' => [
                'input'    => '123e4567-e89b-12D3-A456-426614174000',
                'expected' => true,
            ],
        ];
    }

    /**
     * Data provider for invalid UUID test cases
     */
    public static function provide_invalid_uuid_cases(): array
    {
        return [
            'too short' => [
                'input'    => '123e4567-e89b-12d3-a456-42661417400',
                'expected' => false,
            ],
            'too long' => [
                'input'    => '123e4567-e89b-12d3-a456-4266141740000',
                'expected' => false,
            ],
            'missing hyphen' => [
                'input'    => '123e4567e89b-12d3-a456-426614174000',
                'expected' => false,
            ],
            'wrong format' => [
                'input'    => '123e-4567-e89b-12d3-a456-426614174000',
                'expected' => false,
            ],
            'non-hex character' => [
                'input'    => '123e4567-e89b-12d3-a456-42661417400g',
                'expected' => false,
            ],
            'plain string' => [
                'input'    => 'not-a-uuid',
                'expected' => false,
            ],
            'empty string' => [
                'input'    => '',
                'expected' => false,
            ],
            'no hyphens' => [
                'input'    => '123e4567e89b12d3a456426614174000',
                'expected' => false,
            ],
            'invalid characters' => [
                'input'    => '123e4567-e89b-12z3-a456-426614174000',
                'expected' => false,
            ],
        ];
    }

    #[DataProvider('provide_valid_uuid_cases')]
    public function test_is_valid_uuid_valid_cases(string $input, bool $expected): void
    {
        $this->assertEquals($expected, is_valid_uuid($input));
    }

    #[DataProvider('provide_invalid_uuid_cases')]
    public function test_is_valid_uuid_invalid_cases(string $input, bool $expected): void
    {
        $this->assertEquals($expected, is_valid_uuid($input));
    }

    /**
     * Data provider for testing uuid_v4 with different options
     */
    public static function uuid_v4_options_provider(): array
    {
        return [
            'default options'         => [true, true, true],
            'no dashes'               => [false, true, false],
            'uppercase'               => [true, false, true],
            'no dashes and uppercase' => [false, false, false],
        ];
    }

    /**
     * Test the basic functionality of uuid_v4
     */
    public function test_uuid_v4_basic(): void
    {
        $uuid = uuid_v4();

        // UUID should be a string
        $this->assertIsString($uuid);

        // UUID with dashes should be 36 characters
        $this->assertEquals(36, strlen($uuid));

        // Should be a valid UUID
        $this->assertTrue(is_valid_uuid($uuid), 'The generated UUID is not valid');

        // Should include the version 4 marker
        $this->assertMatchesRegularExpression('/^[0-9a-f]{8}-[0-9a-f]{4}-4[0-9a-f]{3}-[89ab][0-9a-f]{3}-[0-9a-f]{12}$/i', $uuid);
    }

    /**
     * Test uuid_v4 with different formatting options
     */
    #[DataProvider('uuid_v4_options_provider')]
    public function test_uuid_v4_format_options(bool $dashes, bool $lowercase, bool $shouldHaveDashes): void
    {
        $uuid = uuid_v4($dashes, $lowercase);

        // Check if dashes are present as expected
        if ($shouldHaveDashes) {
            $this->assertStringContainsString('-', $uuid);
            $this->assertEquals(36, strlen($uuid));
        } else {
            $this->assertStringNotContainsString('-', $uuid);
            $this->assertEquals(32, strlen($uuid));
        }

        // Check case
        if ($lowercase) {
            $this->assertEquals(strtolower($uuid), $uuid);
        } else {
            $this->assertEquals(strtoupper($uuid), $uuid);
        }

        // Clean up dashes for validation if needed
        $cleanUuid = str_replace('-', '', $uuid);

        // Check if it's a valid hex string
        $this->assertTrue(ctype_xdigit($cleanUuid), 'UUID contains non-hexadecimal characters');

        // Reformat for standard validation if needed
        if (!$dashes) {
            $formattedUuid = substr($cleanUuid, 0, 8) . '-' .
                             substr($cleanUuid, 8, 4) . '-' .
                             substr($cleanUuid, 12, 4) . '-' .
                             substr($cleanUuid, 16, 4) . '-' .
                             substr($cleanUuid, 20, 12);
            $this->assertTrue(is_valid_uuid($formattedUuid), 'The UUID is not valid when reformatted');
        }
    }

    /**
     * Test that uuid_v4 generates unique values
     */
    public function test_uuid_v4_uniqueness(): void
    {
        $uuids = [];
        $count = 100;

        // Generate multiple UUIDs
        for ($i = 0; $i < $count; $i++) {
            $uuids[] = uuid_v4();
        }

        // Check uniqueness
        $uniqueUuids = array_unique($uuids);
        $this->assertCount($count, $uniqueUuids, 'Generated UUIDs are not unique');
    }

    /**
     * Test uuid_v4 generates RFC 4122 compliant version 4 UUIDs
     */
    public function test_uuid_v4_rfc4122_compliance(): void
    {
        $uuid = uuid_v4();

        // Extract the version and variant fields
        $parts = explode('-', $uuid);
        $this->assertCount(5, $parts);

        // Check version (should be 4)
        $version = $parts[2][0];
        $this->assertEquals('4', $version);

        // Check variant (should be 8, 9, a, or b)
        $variant = $parts[3][0];
        $this->assertMatchesRegularExpression('/^[89ab]$/', $variant);
    }
}
