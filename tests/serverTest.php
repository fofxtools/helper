<?php

namespace FOfX\Helper;

use PHPUnit\Framework\TestCase;

class ServerTest extends TestCase
{
    /**
     * These tests modify the $_SERVER and $_ENV superglobals, so store the original values
     * to restore them after each test
     *
     * @var array
     */
    private $originalServer;

    /**
     * @var array
     */
    private $originalEnv;

    /**
     * Class property for the expected quote style based on the operating system.
     *
     * @var string
     */
    private $expected_quote;

    /**
     * Set up the test environment
     */
    protected function setUp(): void
    {
        parent::setUp();

        // Store original values
        $this->originalServer = $_SERVER;
        $this->originalEnv    = $_ENV;

        // Determine the expected quote style based on the operating system
        $this->expected_quote = (PHP_OS_FAMILY === 'Windows') ? '"' : "'";
    }

    /**
     * Tear down the test environment
     */
    protected function tearDown(): void
    {
        // Restore original values
        $_SERVER = $this->originalServer;
        $_ENV    = $this->originalEnv;

        parent::tearDown();
    }

    /**
     * Test that a valid IP from HTTP_CLIENT_IP is returned correctly.
     *
     * @return void
     */
    public function test_get_remote_addr_from_http_client_ip()
    {
        // Set the HTTP_CLIENT_IP server variable
        $_SERVER['HTTP_CLIENT_IP'] = '192.168.1.100';

        // Clear other potential IP headers
        unset($_SERVER['HTTP_X_FORWARDED_FOR']);
        unset($_SERVER['REMOTE_ADDR']);

        // Call the function
        $result = get_remote_addr();

        // Assert that the correct IP is returned
        $this->assertEquals('192.168.1.100', $result);
    }

    /**
     * Test that a valid IP from HTTP_X_FORWARDED_FOR is returned correctly.
     *
     * @return void
     */
    public function test_get_remote_addr_from_http_x_forwarded_for()
    {
        // Set the HTTP_X_FORWARDED_FOR server variable
        $_SERVER['HTTP_X_FORWARDED_FOR'] = '10.0.0.1';

        // Clear other potential IP headers
        unset($_SERVER['HTTP_CLIENT_IP']);
        unset($_SERVER['REMOTE_ADDR']);

        // Call the function
        $result = get_remote_addr();

        // Assert that the correct IP is returned
        $this->assertEquals('10.0.0.1', $result);
    }

    /**
     * Test that the first IP from a comma-separated list in HTTP_X_FORWARDED_FOR is returned.
     *
     * @return void
     */
    public function test_get_remote_addr_from_multiple_ips_in_http_x_forwarded_for()
    {
        // Set the HTTP_X_FORWARDED_FOR server variable with multiple IPs
        $_SERVER['HTTP_X_FORWARDED_FOR'] = '192.168.1.100, 10.0.0.1';

        // Clear other potential IP headers
        unset($_SERVER['HTTP_CLIENT_IP']);
        unset($_SERVER['REMOTE_ADDR']);

        // Call the function
        $result = get_remote_addr();

        // Assert that the first IP is returned
        $this->assertEquals('192.168.1.100', $result);
    }

    /**
     * Test that a valid IP from REMOTE_ADDR is returned correctly.
     *
     * @return void
     */
    public function test_get_remote_addr_from_remote_addr()
    {
        // Set the REMOTE_ADDR server variable
        $_SERVER['REMOTE_ADDR'] = '127.0.0.1';

        // Clear other potential IP headers
        unset($_SERVER['HTTP_CLIENT_IP']);
        unset($_SERVER['HTTP_X_FORWARDED_FOR']);

        // Call the function
        $result = get_remote_addr();

        // Assert that the correct IP is returned
        $this->assertEquals('127.0.0.1', $result);
    }

    /**
     * Test that the default IP '127.0.0.1' is returned when no valid IP is found.
     *
     * @return void
     */
    public function test_get_remote_addr_defaults_to_localhost()
    {
        // Clear all potential IP headers
        unset($_SERVER['HTTP_CLIENT_IP']);
        unset($_SERVER['HTTP_X_FORWARDED_FOR']);
        unset($_SERVER['REMOTE_ADDR']);

        // Call the function
        $result = get_remote_addr();

        // Assert that '127.0.0.1' is returned as default
        $this->assertEquals('127.0.0.1', $result);
    }

    /**
     * Test that an exception is thrown when an invalid IP is provided.
     *
     * @return void
     */
    public function test_get_remote_addr_throws_exception_on_invalid_ip_format()
    {
        // Set an invalid IP in HTTP_CLIENT_IP
        $_SERVER['HTTP_CLIENT_IP'] = 'invalid_ip';

        // Clear other potential IP headers
        unset($_SERVER['HTTP_X_FORWARDED_FOR']);
        unset($_SERVER['REMOTE_ADDR']);

        // Expect an exception to be thrown
        $this->expectException(\UnexpectedValueException::class);

        // Call the function
        get_remote_addr();
    }

    /**
     * Test that an exception is thrown when the IP is invalid in HTTP_X_FORWARDED_FOR.
     *
     * @return void
     */
    public function test_get_remote_addr_throws_exception_on_invalid_ip_in_http_x_forwarded_for()
    {
        // Set an invalid IP in HTTP_X_FORWARDED_FOR
        $_SERVER['HTTP_X_FORWARDED_FOR'] = 'invalid_ip';

        // Clear other potential IP headers
        unset($_SERVER['HTTP_CLIENT_IP']);
        unset($_SERVER['REMOTE_ADDR']);

        // Expect an exception to be thrown
        $this->expectException(\UnexpectedValueException::class);

        // Call the function
        get_remote_addr();
    }

    /**
     * Test that no other headers are used when HTTP_CLIENT_IP is valid.
     *
     * @return void
     */
    public function test_get_remote_addr_prioritizes_http_client_ip_over_others()
    {
        // Set multiple IP headers
        $_SERVER['HTTP_CLIENT_IP']       = '203.0.113.1';
        $_SERVER['HTTP_X_FORWARDED_FOR'] = '192.168.0.1';
        $_SERVER['REMOTE_ADDR']          = '10.0.0.1';

        // Call the function
        $result = get_remote_addr();

        // Assert that HTTP_CLIENT_IP is prioritized
        $this->assertEquals('203.0.113.1', $result);
    }

    /**
     * Test that the function returns the HTTP_X_FORWARDED_HOST
     * when $use_forwarded_host is true and it is present.
     *
     * @return void
     */
    public function test_get_http_host_with_http_x_forwarded_host()
    {
        // Set the HTTP_X_FORWARDED_HOST server variable
        $_SERVER['HTTP_X_FORWARDED_HOST'] = 'forwarded.example.com';

        // Clear other potential IP headers
        unset($_SERVER['HTTP_HOST']);
        unset($_SERVER['SERVER_NAME']);

        // Call the function
        $result = get_http_host(true);

        // Assert that the correct host is returned
        $this->assertEquals('forwarded.example.com', $result);
    }

    /**
     * Test that the function returns the HTTP_HOST
     * when it is present and $use_forwarded_host is false.
     *
     * @return void
     */
    public function test_get_http_host_with_http_host()
    {
        // Set the HTTP_HOST server variable
        $_SERVER['HTTP_HOST'] = 'example.com';

        // Clear other potential IP headers
        unset($_SERVER['HTTP_X_FORWARDED_HOST']);
        unset($_SERVER['SERVER_NAME']);

        // Call the function
        $result = get_http_host(false);

        // Assert that the correct host is returned
        $this->assertEquals('example.com', $result);
    }

    /**
     * Test that the function returns SERVER_NAME
     * when HTTP_HOST is not set.
     *
     * @return void
     */
    public function test_get_http_host_with_server_name()
    {
        // Set the SERVER_NAME server variable
        $_SERVER['SERVER_NAME'] = 'server.example.com';

        // Clear other potential IP headers
        unset($_SERVER['HTTP_HOST']);
        unset($_SERVER['HTTP_X_FORWARDED_HOST']);

        // Call the function
        $result = get_http_host(false);

        // Assert that the correct host is returned
        $this->assertEquals('server.example.com', $result);
    }

    /**
     * Test that the function defaults to 'localhost'
     * when no valid host information is provided.
     *
     * @return void
     */
    public function test_get_http_host_defaults_to_localhost()
    {
        // Clear all potential IP headers
        unset($_SERVER['HTTP_HOST']);
        unset($_SERVER['HTTP_X_FORWARDED_HOST']);
        unset($_SERVER['SERVER_NAME']);

        // Call the function
        $result = get_http_host(false);

        // Assert that 'localhost' is returned as the default
        $this->assertEquals('localhost', $result);
    }

    /**
     * Test that the function throws an exception
     * when an invalid host is provided.
     *
     * @return void
     */
    public function test_get_http_host_throws_exception_on_invalid_host()
    {
        // Set an invalid HTTP_HOST server variable
        $_SERVER['HTTP_HOST'] = 'invalid_host@';

        // Expect an exception to be thrown
        $this->expectException(\UnexpectedValueException::class);

        // Call the function
        get_http_host(false);
    }

    /**
     * Test that the function throws an exception
     * when an invalid forwarded host is provided.
     *
     * @return void
     */
    public function test_get_http_host_throws_exception_on_invalid_forwarded_host()
    {
        // Set an invalid HTTP_X_FORWARDED_HOST server variable
        $_SERVER['HTTP_X_FORWARDED_HOST'] = 'invalid_host@';

        // Clear other potential IP headers
        unset($_SERVER['HTTP_HOST']);
        unset($_SERVER['SERVER_NAME']);

        // Expect an exception to be thrown
        $this->expectException(\UnexpectedValueException::class);

        // Call the function with $use_forwarded_host set to true
        get_http_host(true);
    }

    /**
     * Test that multiple hosts in HTTP_X_FORWARDED_HOST
     * returns the first one.
     *
     * @return void
     */
    public function test_get_http_host_with_multiple_forwarded_hosts()
    {
        // Set multiple hosts in HTTP_X_FORWARDED_HOST
        $_SERVER['HTTP_X_FORWARDED_HOST'] = 'forwarded.example.com, secondary.example.com';

        // Clear other potential IP headers
        unset($_SERVER['HTTP_HOST']);
        unset($_SERVER['SERVER_NAME']);

        // Call the function
        $result = get_http_host(true);

        // Assert that the first host is returned
        $this->assertEquals('forwarded.example.com', $result);
    }

    /**
     * Test that a host with a port is correctly returned
     * and validated.
     *
     * @return void
     */
    public function test_get_http_host_with_port()
    {
        // Set HTTP_HOST with a port
        $_SERVER['HTTP_HOST'] = 'example.com:8080';

        // Call the function
        $result = get_http_host(false);

        // Assert that the host with the port is returned
        $this->assertEquals('example.com:8080', $result);
    }

    /**
     * Test get_user_home_directory on Windows
     */
    public function test_get_user_home_directory_windows(): void
    {
        $_SERVER['HOMEDRIVE'] = 'C:';
        $_SERVER['HOMEPATH']  = '\Users\TestUser';

        $this->assertEquals('C:\Users\TestUser', get_user_home_directory('Windows'));
    }

    /**
     * Test get_user_home_directory on Unix
     */
    public function test_get_user_home_directory_unix(): void
    {
        if (function_exists('posix_getpwuid') && function_exists('posix_getuid')) {
            $expectedHome = posix_getpwuid(posix_getuid())['dir'];
        } else {
            $_SERVER['HOME'] = '/home/testuser';
            $expectedHome    = '/home/testuser';
        }

        $this->assertEquals($expectedHome, get_user_home_directory('Linux'));
    }

    /**
     * Test get_windows_home_directory with HOMEDRIVE and HOMEPATH set
     */
    public function test_get_windows_home_directory_with_homedrive_and_homepath(): void
    {
        $_SERVER['HOMEDRIVE'] = 'D:';
        $_SERVER['HOMEPATH']  = '\Users\JohnDoe';

        $this->assertEquals('D:\Users\JohnDoe', get_windows_home_directory('Windows'));
    }

    /**
     * Test get_windows_home_directory with USERPROFILE set
     */
    public function test_get_windows_home_directory_with_userprofile(): void
    {
        unset($_SERVER['HOMEDRIVE']);
        unset($_SERVER['HOMEPATH']);
        $_SERVER['USERPROFILE'] = 'C:\Users\JaneDoe';

        $this->assertEquals('C:\Users\JaneDoe', get_windows_home_directory('Windows'));
    }

    /**
     * Test get_windows_home_directory on non-Windows system
     */
    public function test_get_windows_home_directory_on_non_windows(): void
    {
        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('get_windows_home_directory() can only be called on Windows systems.');

        get_windows_home_directory('Linux');
    }

    /**
     * Test get_unix_home_directory with HOME set
     */
    public function test_get_unix_home_directory_with_home(): void
    {
        if (function_exists('posix_getpwuid') && function_exists('posix_getuid')) {
            $expectedHome = posix_getpwuid(posix_getuid())['dir'];
        } else {
            $_SERVER['HOME'] = '/home/janedoe';
            $expectedHome    = '/home/janedoe';
        }

        $this->assertEquals($expectedHome, get_unix_home_directory('Linux'));
    }

    /**
     * Test get_unix_home_directory on Windows system
     */
    public function test_get_unix_home_directory_on_windows(): void
    {
        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('get_unix_home_directory() can only be called on Unix-like systems.');

        get_unix_home_directory('Windows');
    }

    /**
     * Test url_filename_ with a standard valid URL.
     */
    public function test_url_filename_with_valid_url()
    {
        $url      = 'https://www.example.com/subfolder1/subfolder2/index.php?string=abc&num=123';
        $expected = 'index.php';

        $this->assertSame($expected, url_filename($url));
    }

    /**
     * Test url_filename_ with a URL that has no query parameters.
     */
    public function test_url_filename_with_url_without_query_parameters()
    {
        $url      = 'https://www.example.com/subfolder1/subfolder2/index.php';
        $expected = 'index.php';

        $this->assertSame($expected, url_filename($url));
    }

    /**
     * Test url_filename_ with a URL that ends in a directory (no filename).
     */
    public function test_url_filename_with_url_that_ends_in_directory()
    {
        $url      = 'https://www.example.com/subfolder1/subfolder2/';
        $expected = '';

        $this->assertSame($expected, url_filename($url));
    }

    /**
     * Test url_filename_ with a "dirty" URL containing extra slashes and spaces.
     */
    public function test_url_filename_with_dirty_url()
    {
        $url      = '  https://www.example.com////subfolder///file.txt    ';
        $expected = 'file.txt';

        $this->assertSame($expected, url_filename(trim($url)));
    }

    /**
     * Test url_filename_ with a URL containing special characters in the filename.
     */
    public function test_url_filename_with_special_characters_in_filename()
    {
        $url      = 'https://www.example.com/path/to/file-with_special-characters!.php';
        $expected = 'file-with_special-characters!.php';

        $this->assertSame($expected, url_filename($url));
    }

    /**
     * Test url_filename_ with an invalid URL (missing scheme).
     */
    public function test_url_filename_with_invalid_url_missing_scheme()
    {
        $url      = 'www.example.com/index.php';
        $expected = null;

        $this->assertNull(url_filename($url));
    }

    /**
     * Test url_filename_ with an invalid URL (invalid structure).
     */
    public function test_url_filename_with_invalid_url_structure()
    {
        $url      = 'https:///invalid-url';
        $expected = null;

        $this->assertNull(url_filename($url));
    }

    /**
     * Test url_filename_ with a URL with no path (just a domain).
     */
    public function test_url_filename_with_url_with_no_path()
    {
        $url      = 'https://www.example.com';
        $expected = '';

        $this->assertSame($expected, url_filename($url));
    }

    /**
     * Test url_filename_ with an empty string as the URL.
     */
    public function test_url_filename_with_empty_url()
    {
        $url      = '';
        $expected = null;

        $this->assertNull(url_filename($url));
    }

    /**
     * Test url_filename_ with a very long URL.
     */
    public function test_url_filename_with_very_long_url()
    {
        $url      = 'https://www.example.com/' . str_repeat('folder/', 100) . 'file.php';
        $expected = 'file.php';

        $this->assertSame($expected, url_filename($url));
    }

    /**
     * Test url_filename_ with a URL that ends in a file with no extension.
     */
    public function test_url_filename_with_url_that_ends_in_file_without_extension()
    {
        $url      = 'https://www.example.com/path/to/file';
        $expected = 'file';

        $this->assertSame($expected, url_filename($url));
    }

    /**
     * Test url_file_extension_ with a valid URL containing a file extension.
     */
    public function test_url_file_extension_with_valid_url()
    {
        $url      = 'https://www.example.com/subfolder1/subfolder2/index.php?string=abc&num=123';
        $expected = 'php';

        $this->assertSame($expected, url_file_extension($url));
    }

    /**
     * Test url_file_extension_ with a URL that contains no query parameters.
     */
    public function test_url_file_extension_with_url_without_query_parameters()
    {
        $url      = 'https://www.example.com/subfolder1/subfolder2/file.txt';
        $expected = 'txt';

        $this->assertSame($expected, url_file_extension($url));
    }

    /**
     * Test url_file_extension_ with a URL that ends in a directory (no filename).
     */
    public function test_url_file_extension_with_url_ending_in_directory()
    {
        $url      = 'https://www.example.com/subfolder1/subfolder2/';
        $expected = null;

        $this->assertNull(url_file_extension($url));
    }

    /**
     * Test url_file_extension_ with a "dirty" URL containing extra spaces and slashes.
     */
    public function test_url_file_extension_with_dirty_url()
    {
        $url      = '  https://www.example.com/subfolder1/subfolder2////file.txt  ';
        $expected = 'txt';

        $this->assertSame($expected, url_file_extension(trim($url)));
    }

    /**
     * Test url_file_extension_ with a URL that has no extension.
     */
    public function test_url_file_extension_with_url_without_extension()
    {
        $url      = 'https://www.example.com/subfolder1/subfolder2/file';
        $expected = null;

        $this->assertNull(url_file_extension($url));
    }

    /**
     * Test url_file_extension_ with a URL containing special characters in the filename.
     */
    public function test_url_file_extension_with_special_characters_in_filename()
    {
        $url      = 'https://www.example.com/subfolder1/subfolder2/file-with_special-characters!.jpeg';
        $expected = 'jpeg';

        $this->assertSame($expected, url_file_extension($url));
    }

    /**
     * Test url_file_extension_ with an invalid URL (missing scheme).
     */
    public function test_url_file_extension_with_invalid_url_missing_scheme()
    {
        $url      = 'www.example.com/subfolder1/file.php';
        $expected = null;

        $this->assertNull(url_file_extension($url));
    }

    /**
     * Test url_file_extension_ with an invalid URL structure.
     */
    public function test_url_file_extension_with_invalid_url_structure()
    {
        $url      = 'https:///invalid-url';
        $expected = null;

        $this->assertNull(url_file_extension($url));
    }

    /**
     * Test url_file_extension_ with a URL with no path (just a domain).
     */
    public function test_url_file_extension_with_url_with_no_path()
    {
        $url      = 'https://www.example.com';
        $expected = null;

        $this->assertNull(url_file_extension($url));
    }

    /**
     * Test url_file_extension_ with an empty string as the URL.
     */
    public function test_url_file_extension_with_empty_url()
    {
        $url      = '';
        $expected = null;

        $this->assertNull(url_file_extension($url));
    }

    /**
     * Test url_file_extension_ with a very long URL.
     */
    public function test_url_file_extension_with_very_long_url()
    {
        $url      = 'https://www.example.com/' . str_repeat('folder/', 100) . 'file.html';
        $expected = 'html';

        $this->assertSame($expected, url_file_extension($url));
    }

    /**
     * Test url_file_extension_ with a URL containing a query string but no extension.
     */
    public function test_url_file_extension_with_url_and_query_string_but_no_extension()
    {
        $url      = 'https://www.example.com/path/file?param=value';
        $expected = null;

        $this->assertNull(url_file_extension($url));
    }

    /**
     * Test url_file_extension_ with a URL that ends in a file with no extension.
     */
    public function test_url_file_extension_with_url_that_ends_in_file_without_extension()
    {
        $url      = 'https://www.example.com/path/to/file';
        $expected = null;

        $this->assertNull(url_file_extension($url));
    }

    /**
     * Test url_file_extension_ with a file that has multiple dots in the name.
     */
    public function test_url_file_extension_with_file_with_multiple_dots_in_name()
    {
        $url      = 'https://www.example.com/path/file.name.with.dots.tar.gz';
        $expected = 'gz';

        $this->assertSame($expected, url_file_extension($url));
    }

    /**
     * Test get_network_stats_ for Windows and Linux.
     */
    public function test_get_network_stats()
    {
        if (PHP_OS_FAMILY === 'Windows') {
            // Simulate valid output from netstat for Windows
            $netstatOutput = "Interface Statistics\nBytes 12 34\n";

            // Create a mock callable to simulate shell_exec on Windows
            $mockExecutor = function ($command) use ($netstatOutput) {
                return $netstatOutput;
            };

            // Call get_network_stats and assert the expected result for Windows
            $result   = get_network_stats(false, $mockExecutor);
            $expected = [
                'Bytes' => [
                    'Receive'  => '12',
                    'Transmit' => '34',
                ],
            ];

            $this->assertSame($expected, $result);
        } else {
            // Simulate valid output from /proc/net/dev for Linux
            $procNetDevOutput = "Inter-|   Receive    Transmit\nlo: 12345 0 0 0 0 0 0 0 54321\n";

            // Create a mock callable to simulate shell_exec on Linux
            $mockExecutor = function ($command) use ($procNetDevOutput) {
                return $procNetDevOutput;
            };

            // Call get_network_stats and assert the expected result for Linux
            $result   = get_network_stats(false, $mockExecutor);
            $expected = [
                'lo' => [
                    'Receive'  => '12345',
                    'Transmit' => '54321',
                ],
            ];

            $this->assertSame($expected, $result);
        }
    }

    /**
     * Test get_network_stats_ for Windows OS with valid output from netstat.
     */
    public function test_get_network_stats_for_windows()
    {
        if (PHP_OS_FAMILY !== 'Windows') {
            $this->markTestSkipped('This test is only applicable to Windows.');
        }

        // Simulate valid output from netstat for Windows
        $netstatOutput = "Interface Statistics\nBytes 12 34\n";

        // Create a mock callable to simulate shell_exec on Windows
        $mockExecutor = function ($command) use ($netstatOutput) {
            return $netstatOutput;
        };

        // Call get_network_stats and assert the expected result for Windows
        $result   = get_network_stats(false, $mockExecutor);
        $expected = [
            'Bytes' => [
                'Receive'  => '12',
                'Transmit' => '34',
            ],
        ];

        $this->assertSame($expected, $result);
    }

    /**
     * Test get_network_stats_ for Linux OS with valid output from /proc/net/dev.
     */
    public function test_get_network_stats_for_linux()
    {
        if (PHP_OS_FAMILY === 'Windows') {
            $this->markTestSkipped('This test is only applicable to Linux.');
        }

        // Simulate valid output from /proc/net/dev for Linux
        $procNetDevOutput = "Inter-|   Receive    Transmit\nlo: 12345 0 0 0 0 0 0 0 54321\n";

        // Create a mock callable to simulate shell_exec on Linux
        $mockExecutor = function ($command) use ($procNetDevOutput) {
            return $procNetDevOutput;
        };

        // Call get_network_stats and assert the expected result for Linux
        $result   = get_network_stats(false, $mockExecutor);
        $expected = [
            'lo' => [
                'Receive'  => '12345',
                'Transmit' => '54321',
            ],
        ];

        $this->assertSame($expected, $result);
    }

    /**
     * Test get_network_stats_ with empty output from execute_shell_command.
     */
    public function test_get_network_stats_with_empty_output()
    {
        // Simulate empty output from shell_exec
        $mockExecutor = function ($command) {
            return '';
        };

        // Call get_network_stats and assert an empty array is returned
        $result = get_network_stats(false, $mockExecutor);
        $this->assertSame([], $result);
    }

    /**
     * Test get_network_stats_ when execute_shell_command fails and returns false.
     */
    public function test_get_network_stats_with_failed_command()
    {
        // Simulate execute_shell_command failing by returning false
        $mockExecutor = function ($command) {
            return false;
        };

        // Expect a RuntimeException when calling get_network_stats
        $this->expectException(\RuntimeException::class);

        // Call get_network_stats with the mock executor
        get_network_stats(false, $mockExecutor);
    }

    /**
     * Test get_network_stats_ for Linux with a specific PID.
     * This test will only run on Linux.
     */
    public function test_get_network_stats_for_linux_with_pid()
    {
        if (PHP_OS_FAMILY !== 'Windows') {
            // Simulate valid output from /proc/123/net/dev for Linux
            $procNetDevOutput = "Inter-|   Receive    Transmit\neth0: 67890 0 0 0 0 0 0 0 98765\n";

            // Create a mock callable to simulate shell_exec on Linux
            $mockExecutor = function ($command) use ($procNetDevOutput) {
                return $procNetDevOutput;
            };

            // Call get_network_stats with a PID and assert the expected result for Linux
            $result   = get_network_stats(123, $mockExecutor);
            $expected = [
                'eth0' => [
                    'Receive'  => '67890',
                    'Transmit' => '98765',
                ],
            ];

            $this->assertSame($expected, $result);
        } else {
            $this->markTestSkipped('This test is only applicable to Linux.');
        }
    }

    /**
     * Test get_network_stats_ for Linux with an invalid PID (non-numeric).
     * This test will only run on Linux.
     */
    public function test_get_network_stats_with_invalid_pid()
    {
        if (PHP_OS_FAMILY !== 'Windows') {
            // Expect a TypeError for a non-numeric PID
            $this->expectException(\TypeError::class);

            // Call get_network_stats with an invalid PID
            // @phpstan-ignore-next-line
            get_network_stats('invalid_pid');
        } else {
            $this->markTestSkipped('This test is only applicable to Linux.');
        }
    }

    /**
     * Test get_windows_network_stats_ for Windows.
     */
    public function test_get_windows_network_stats()
    {
        if (PHP_OS_FAMILY !== 'Windows') {
            $this->markTestSkipped('This test is only applicable to Windows.');
        }

        // Simulate valid output from netstat on Windows
        $netstatOutput = "Interface Statistics\nBytes 12 34\n";

        // Create a mock callable to simulate shell_exec on Windows
        $mockExecutor = function ($command) use ($netstatOutput) {
            return $netstatOutput;
        };

        // Call get_windows_network_stats and assert the expected result
        $result   = get_windows_network_stats($mockExecutor);
        $expected = [
            'Bytes' => [
                'Receive'  => '12',
                'Transmit' => '34',
            ],
        ];

        $this->assertSame($expected, $result);
    }

    /**
     * Test get_windows_network_stats_ with empty output.
     */
    public function test_get_windows_network_stats_with_empty_output()
    {
        if (PHP_OS_FAMILY !== 'Windows') {
            $this->markTestSkipped('This test is only applicable to Windows.');
        }

        // Simulate empty output from shell_exec
        $mockExecutor = function ($command) {
            return '';
        };

        // Call get_windows_network_stats and assert an empty array is returned
        $result = get_windows_network_stats($mockExecutor);
        $this->assertSame([], $result);
    }

    /**
     * Test get_windows_network_stats_ with command failure.
     */
    public function test_get_windows_network_stats_with_command_failure()
    {
        if (PHP_OS_FAMILY !== 'Windows') {
            $this->markTestSkipped('This test is only applicable to Windows.');
        }

        // Simulate shell_exec failing by returning false
        $mockExecutor = function ($command) {
            return false;
        };

        // Expect a RuntimeException when calling get_windows_network_stats
        $this->expectException(\RuntimeException::class);

        // Call get_windows_network_stats with the mock executor
        get_windows_network_stats($mockExecutor);
    }

    /**
     * Test get_linux_network_stats_ for Linux.
     */
    public function test_get_linux_network_stats()
    {
        if (PHP_OS_FAMILY === 'Windows') {
            $this->markTestSkipped('This test is only applicable to Linux.');
        }

        // Simulate valid output from /proc/net/dev for Linux
        $procNetDevOutput = "Inter-|   Receive    Transmit\nlo: 12345 0 0 0 0 0 0 0 54321\n";

        // Create a mock callable to simulate shell_exec on Linux
        $mockExecutor = function ($command) use ($procNetDevOutput) {
            return $procNetDevOutput;
        };

        // Call get_linux_network_stats and assert the expected result
        $result   = get_linux_network_stats(false, $mockExecutor);
        $expected = [
            'lo' => [
                'Receive'  => '12345',
                'Transmit' => '54321',
            ],
        ];

        $this->assertSame($expected, $result);
    }

    /**
     * Test get_linux_network_stats_ with empty output.
     */
    public function test_get_linux_network_stats_with_empty_output()
    {
        if (PHP_OS_FAMILY === 'Windows') {
            $this->markTestSkipped('This test is only applicable to Linux.');
        }

        // Simulate empty output from shell_exec
        $mockExecutor = function ($command) {
            return '';
        };

        // Call get_linux_network_stats and assert an empty array is returned
        $result = get_linux_network_stats(false, $mockExecutor);
        $this->assertSame([], $result);
    }

    /**
     * Test get_linux_network_stats_ with command failure.
     */
    public function test_get_linux_network_stats_with_command_failure()
    {
        if (PHP_OS_FAMILY === 'Windows') {
            $this->markTestSkipped('This test is only applicable to Linux.');
        }

        // Simulate shell_exec failing by returning false
        $mockExecutor = function ($command) {
            return false;
        };

        // Expect a RuntimeException when calling get_linux_network_stats
        $this->expectException(\RuntimeException::class);

        // Call get_linux_network_stats with the mock executor
        get_linux_network_stats(false, $mockExecutor);
    }

    /**
     * Test get_linux_network_stats_ for Linux with a specific PID.
     */
    public function test_get_linux_network_stats_with_pid()
    {
        if (PHP_OS_FAMILY === 'Windows') {
            $this->markTestSkipped('This test is only applicable to Linux.');
        }

        // Simulate valid output from /proc/123/net/dev for Linux
        $procNetDevOutput = "Inter-|   Receive    Transmit\neth0: 67890 0 0 0 0 0 0 0 98765\n";

        // Create a mock callable to simulate shell_exec on Linux
        $mockExecutor = function ($command) use ($procNetDevOutput) {
            return $procNetDevOutput;
        };

        // Call get_linux_network_stats with a PID and assert the expected result
        $result   = get_linux_network_stats(123, $mockExecutor);
        $expected = [
            'eth0' => [
                'Receive'  => '67890',
                'Transmit' => '98765',
            ],
        ];

        $this->assertSame($expected, $result);
    }

    /**
     * Test get_linux_network_stats_ with an invalid PID (non-numeric).
     */
    public function test_get_linux_network_stats_with_invalid_pid()
    {
        if (PHP_OS_FAMILY === 'Windows') {
            $this->markTestSkipped('This test is only applicable to Linux.');
        }

        // Expect a TypeError for a non-numeric PID
        $this->expectException(\TypeError::class);

        // Call get_linux_network_stats with an invalid PID
        // @phpstan-ignore-next-line
        get_linux_network_stats('invalid_pid');
    }

    /**
     * Test get_diagnostics function.
     *
     * This test captures the output of the get_diagnostics function and ensures
     * that it includes key diagnostic information.
     *
     * @return void
     */
    public function test_get_diagnostics_output()
    {
        // Capture output
        ob_start();
        get_diagnostics();
        $output = ob_get_clean();

        // Assert output contains key diagnostics
        $this->assertStringContainsString('__LINE__', $output);
        $this->assertStringContainsString('__FILE__', $output);
        $this->assertStringContainsString('__DIR__', $output);
        $this->assertStringContainsString('__FUNCTION__', $output);
        $this->assertStringContainsString('__METHOD__', $output);
        $this->assertStringContainsString('__NAMESPACE__', $output);
        $this->assertStringContainsString('dirname', $output);
        $this->assertStringContainsString('getcwd()', $output);
        $this->assertStringContainsString('php://input', $output);
    }

    /**
     * Test get_diagnostics function with missing $_SESSION.
     *
     * This test verifies that get_diagnostics correctly handles
     * the case where $_SESSION is not set.
     *
     * @return void
     */
    public function test_get_diagnostics_no_session()
    {
        // Unset the $_SESSION variable to simulate a missing session
        unset($_SESSION);

        // Capture output
        ob_start();
        get_diagnostics();
        $output = ob_get_clean();

        // Assert that $_SESSION is handled correctly
        $this->assertStringContainsString('count($_SESSION) : 0', $output);
    }

    /**
     * Test get_diagnostics function with an active session.
     *
     * This test verifies that get_diagnostics correctly handles
     * an active session.
     *
     * @return void
     */
    public function test_get_diagnostics_with_session()
    {
        // Simulate an active session
        $_SESSION = ['user' => 'admin', 'role' => 'administrator'];

        // Capture output
        ob_start();
        get_diagnostics();
        $output = ob_get_clean();

        // Assert that $_SESSION count and length are correctly printed
        $this->assertStringContainsString('count($_SESSION) : 2', $output);
    }

    /**
     * Test print_php_constants function.
     *
     * This test captures the output of the print_php_constants function
     * and checks that all expected constants are printed.
     *
     * @return void
     */
    public function test_print_php_constants_output()
    {
        // Capture output
        ob_start();
        print_php_constants();
        $output = ob_get_clean();

        // Assert the expected PHP magic constants are present in the output
        $this->assertStringContainsString('__LINE__', $output);
        $this->assertStringContainsString('__FILE__', $output);
        $this->assertStringContainsString('__DIR__', $output);
        $this->assertStringContainsString('__FUNCTION__', $output);
        $this->assertStringContainsString('__CLASS__', $output);
        $this->assertStringContainsString('__TRAIT__', $output);
        $this->assertStringContainsString('__METHOD__', $output);
        $this->assertStringContainsString('__NAMESPACE__', $output);
    }

    /**
     * Test print_path_info function.
     *
     * This test verifies that the output of the print_path_info function
     * contains valid directory and path information.
     *
     * @return void
     */
    public function test_print_path_info_output()
    {
        // Capture output
        ob_start();
        print_path_info();
        $output = ob_get_clean();

        // Assert directory and path information is correctly printed
        $this->assertStringContainsString('dirname(\'.\')', $output);
        $this->assertStringContainsString('dirname(__FILE__)', $output);
        $this->assertStringContainsString('getcwd()', $output);
    }

    /**
     * Test print_server_variables function.
     *
     * This test captures the output of print_server_variables and checks
     * that server-related information is printed.
     *
     * @return void
     */
    public function test_print_server_variables_output()
    {
        // Simulate server environment
        $_SERVER['SCRIPT_FILENAME'] = '/path/to/script.php';
        $_SERVER['PHP_SELF']        = '/index.php';
        $_SERVER['DOCUMENT_ROOT']   = '/path/to';

        // Capture output
        ob_start();
        print_server_variables();
        $output = ob_get_clean();

        // Assert key server variables are in the output
        $this->assertStringContainsString('get_remote_addr()', $output);
        $this->assertStringContainsString('get_http_host()', $output);
        $this->assertStringContainsString('$_SERVER[\'SCRIPT_FILENAME\']', $output);
        $this->assertStringContainsString('$_SERVER[\'PHP_SELF\']', $output);
        $this->assertStringContainsString('$_SERVER[\'DOCUMENT_ROOT\']', $output);
    }

    /**
     * Test print_array_info function for a standard array.
     *
     * This test captures the output of the print_array_info function
     * and checks that count and length information is correct.
     *
     * @return void
     */
    public function test_print_array_info_standard_array()
    {
        // Simulate array data
        $array = ['foo', 'bar', 'baz'];

        // Capture output
        ob_start();
        print_array_info('testArray', $array);
        $output = ob_get_clean();

        // Assert array count and length info is in the output
        $this->assertStringContainsString('count($testArray) : 3', $output);
        $this->assertStringContainsString('strlen(recursive_implode($testArray))', $output);
    }

    /**
     * Test print_array_info function for an empty array.
     *
     * This test ensures that the print_array_info function correctly
     * handles an empty array.
     *
     * @return void
     */
    public function test_print_array_info_empty_array()
    {
        // Simulate an empty array
        $array = [];

        // Capture output
        ob_start();
        print_array_info('emptyArray', $array);
        $output = ob_get_clean();

        // Assert output correctly reflects the empty array
        $this->assertStringContainsString('count($emptyArray) : 0', $output);
        $this->assertStringContainsString('strlen(recursive_implode($emptyArray))', $output);
    }

    /**
     * Test basic string escaping for a simple argument.
     *
     * @return void
     */
    public function test_escapeshellarg_windows_basic_string()
    {
        // Basic input
        $arg = 'simple_argument';

        // Expected output
        $expected = '"simple_argument"';

        // Call the function
        $result = escapeshellarg_windows($arg);

        // Assert that the result matches the expected output
        $this->assertEquals($expected, $result);
    }

    /**
     * Test escaping of a string containing spaces.
     *
     * @return void
     */
    public function test_escapeshellarg_windows_string_with_spaces()
    {
        // Input containing spaces
        $arg = 'argument with spaces';

        // Expected output
        $expected = '"argument with spaces"';

        // Call the function
        $result = escapeshellarg_windows($arg);

        // Assert that the result matches the expected output
        $this->assertEquals($expected, $result);
    }

    /**
     * Test escaping of a string containing double quotes.
     *
     * @return void
     */
    public function test_escapeshellarg_windows_string_with_double_quotes()
    {
        // Input containing double quotes
        $arg = 'argument "with quotes"';

        // Expected output
        $expected = '"argument \\"with quotes\\""';

        // Call the function
        $result = escapeshellarg_windows($arg);

        // Assert that the result matches the expected output
        $this->assertEquals($expected, $result);
    }

    /**
     * Test escaping of a string containing backslashes.
     *
     * @return void
     */
    public function test_escapeshellarg_windows_string_with_backslashes()
    {
        $arg = 'argument\\with\\backslashes';

        // Expected output with escaped backslashes (4 backslashes)
        $expected = '"argument\\with\\backslashes"';

        $result = escapeshellarg_windows($arg);

        // Assert that the result matches the expected output
        $this->assertEquals($expected, $result);
    }

    /**
     * Test escaping of a string with mixed backslashes and double quotes.
     *
     * @return void
     */
    public function test_escapeshellarg_windows_string_with_backslashes_and_quotes()
    {
        $arg = 'argument\\with\\"both"';

        // Expected output with escaped backslashes and double quotes
        $expected = '"argument\\with\\\\\\"both\\""';

        $result = escapeshellarg_windows($arg);

        // Assert that the result matches the expected output
        $this->assertEquals($expected, $result);
    }

    /**
     * Test escaping of an empty string.
     *
     * @return void
     */
    public function test_escapeshellarg_windows_empty_string()
    {
        // Empty input
        $arg = '';

        // Expected output
        $expected = '""';

        // Call the function
        $result = escapeshellarg_windows($arg);

        // Assert that the result matches the expected output
        $this->assertEquals($expected, $result);
    }

    /**
     * Test escaping of a string containing only backslashes.
     *
     * @return void
     */
    public function test_escapeshellarg_windows_only_backslashes()
    {
        $arg = '\\\\\\\\';

        // Expected output with 8 backslashes
        $expected = '"\\\\\\\\\\\\\\\\"';

        $result = escapeshellarg_windows($arg);

        // Assert that the result matches the expected output
        $this->assertEquals($expected, $result);
    }

    /**
     * Test escaping of a string containing special characters.
     *
     * @return void
     */
    public function test_escapeshellarg_windows_special_characters()
    {
        // Input containing special characters
        $arg = 'arg$#@&^%!*';

        // Expected output
        $expected = '"arg$#@&^%!*"';

        // Call the function
        $result = escapeshellarg_windows($arg);

        // Assert that the result matches the expected output
        $this->assertEquals($expected, $result);
    }

    /**
     * Test escaping of a very long string.
     *
     * @return void
     */
    public function test_escapeshellarg_windows_very_long_string()
    {
        // Input containing a long string (over 256 characters)
        $arg = str_repeat('a', 300);

        // Expected output
        $expected = '"' . str_repeat('a', 300) . '"';

        // Call the function
        $result = escapeshellarg_windows($arg);

        // Assert that the result matches the expected output
        $this->assertEquals($expected, $result);
    }

    /**
     * Test escaping of a string containing newlines.
     *
     * @return void
     */
    public function test_escapeshellarg_windows_string_with_newlines()
    {
        $arg = "argument\nwith\nnewlines";

        // Expected output with newlines escaped
        $expected = "\"argument\nwith\nnewlines\"";

        $result = escapeshellarg_windows($arg);

        // Assert that the result matches the expected output
        $this->assertEquals($expected, $result);
    }

    /**
     * Test escaping of a string containing tabs.
     *
     * @return void
     */
    public function test_escapeshellarg_windows_string_with_tabs()
    {
        $arg = "argument\twith\ttabs";

        // Expected output with tab characters
        $expected = "\"argument\twith\ttabs\"";

        $result = escapeshellarg_windows($arg);

        // Assert that the result matches the expected output
        $this->assertEquals($expected, $result);
    }

    /**
     * Test basic string escaping for a simple argument.
     *
     * @return void
     */
    public function test_escapeshellarg_crossplatform_basic_string()
    {
        $arg = 'simple_argument';

        // Expected output is based on the platform
        $expected = $this->expected_quote . 'simple_argument' . $this->expected_quote;

        $result = escapeshellarg_crossplatform($arg);

        // Assert that the result matches the expected output
        $this->assertEquals($expected, $result);
    }

    /**
     * Test escaping of a string containing spaces.
     *
     * @return void
     */
    public function test_escapeshellarg_crossplatform_string_with_spaces()
    {
        $arg = 'argument with spaces';

        // Expected output with spaces preserved and properly quoted
        $expected = $this->expected_quote . 'argument with spaces' . $this->expected_quote;

        $result = escapeshellarg_crossplatform($arg);

        // Assert that the result matches the expected output
        $this->assertEquals($expected, $result);
    }

    /**
     * Test escaping of a string containing backslashes.
     *
     * @return void
     */
    public function test_escapeshellarg_crossplatform_string_with_backslashes()
    {
        $arg = 'argument\\with\\backslashes';

        // Expected output with backslashes preserved and properly escaped
        if (stripos(PHP_OS, 'WIN') !== false) {
            $expected = '"argument\\with\\backslashes"';
        } else {
            $expected = "'argument\\with\\backslashes'";
        }

        $result = escapeshellarg_crossplatform($arg);

        // Assert that the result matches the expected output
        $this->assertEquals($expected, $result);
    }

    /**
     * Test escaping of a string containing double quotes.
     *
     * @return void
     */
    public function test_escapeshellarg_crossplatform_string_with_double_quotes()
    {
        $arg = 'argument "with quotes"';

        // Expected output depends on platform, Windows needs more escaping
        if (stripos(PHP_OS, 'WIN') !== false) {
            // Windows-style escaping
            $expected = '"argument \\"with quotes\\""';
        } else {
            // Unix-style escaping
            $expected = "'argument \"with quotes\"'";
        }

        $result = escapeshellarg_crossplatform($arg);

        // Assert that the result matches the expected output
        $this->assertEquals($expected, $result);
    }

    /**
     * Test escaping of a string with mixed backslashes and double quotes.
     *
     * @return void
     */
    public function test_escapeshellarg_crossplatform_string_with_backslashes_and_quotes()
    {
        $arg = 'argument\\with\\"both"';

        // Expected output depends on the platform's escaping rules
        if (stripos(PHP_OS, 'WIN') !== false) {
            $expected = '"argument\\with\\\\\\"both\\""';
        } else {
            $expected = "'argument\\with\\\"both\"'";
        }

        $result = escapeshellarg_crossplatform($arg);

        // Assert that the result matches the expected output
        $this->assertEquals($expected, $result);
    }

    /**
     * Test escaping of a string containing newlines.
     *
     * @return void
     */
    public function test_escapeshellarg_crossplatform_string_with_newlines()
    {
        $arg = "argument\nwith\nnewlines";

        // Expected output keeps newlines preserved inside quotes
        $expected = $this->expected_quote . "argument\nwith\nnewlines" . $this->expected_quote;

        $result = escapeshellarg_crossplatform($arg);

        // Assert that the result matches the expected output
        $this->assertEquals($expected, $result);
    }

    /**
     * Test escaping of a string containing tabs.
     *
     * @return void
     */
    public function test_escapeshellarg_crossplatform_string_with_tabs()
    {
        $arg = "argument\twith\ttabs";

        // Expected output with tab characters preserved inside quotes
        $expected = $this->expected_quote . "argument\twith\ttabs" . $this->expected_quote;

        $result = escapeshellarg_crossplatform($arg);

        // Assert that the result matches the expected output
        $this->assertEquals($expected, $result);
    }

    /**
     * Test escaping of an empty string.
     *
     * @return void
     */
    public function test_escapeshellarg_crossplatform_empty_string()
    {
        $arg = '';

        // An empty string should be escaped as two quotes ("" or '')
        $expected = $this->expected_quote . $this->expected_quote;

        $result = escapeshellarg_crossplatform($arg);

        // Assert that the result matches the expected output
        $this->assertEquals($expected, $result);
    }

    /**
     * Test escaping of a string containing special characters.
     *
     * @return void
     */
    public function test_escapeshellarg_crossplatform_special_characters()
    {
        $arg = 'arg$#@&^%!*';

        // Special characters should be preserved inside quotes
        $expected = $this->expected_quote . 'arg$#@&^%!*' . $this->expected_quote;

        $result = escapeshellarg_crossplatform($arg);

        // Assert that the result matches the expected output
        $this->assertEquals($expected, $result);
    }

    /**
     * Test escaping of a string containing only backslashes.
     *
     * @return void
     */
    public function test_escapeshellarg_crossplatform_only_backslashes()
    {
        $arg = '\\\\\\\\';

        // Adjust the expected output based on the platform
        if (stripos(PHP_OS, 'WIN') !== false) {
            // On Windows, each backslash is escaped, leading to 8 backslashes
            $expected = '"\\\\\\\\\\\\\\\\"';
        } else {
            // On Unix-like systems, backslashes aren't fully escaped, so 4 backslashes remain
            $expected = "'\\\\\\\\'";
        }

        $result = escapeshellarg_crossplatform($arg);

        // Assert that the result matches the expected output
        $this->assertEquals($expected, $result);
    }

    /**
     * Test escaping of a very long string.
     *
     * @return void
     */
    public function test_escapeshellarg_crossplatform_very_long_string()
    {
        $arg = str_repeat('a', 300);

        // Expected output should preserve the long string inside quotes
        $expected = $this->expected_quote . str_repeat('a', 300) . $this->expected_quote;

        $result = escapeshellarg_crossplatform($arg);

        // Assert that the result matches the expected output
        $this->assertEquals($expected, $result);
    }

    public static function provider_is_absolute_path_for_absolute_paths(): array
    {
        if (DIRECTORY_SEPARATOR === '\\') {
            // Windows-specific absolute paths
            return [
                ['C:\\Windows\\System32'],
                ['C:/Program Files'],
                ['\\\\server\\share\\folder'],
                ['C:\\'],
                ['C:/'],
                ['\\\\server'],
            ];
        }

        // Unix-like absolute paths
        return [
            ['/usr/local/bin'],
            ['/var/www/html'],
            ['/'],
        ];
    }

    public static function provider_is_absolute_path_for_relative_paths(): array
    {
        return [
            // Common relative paths
            ['documents/report.txt'],
            ['..\\data\\file.txt'],
            ['./file'],
            ['../file'],
            ['folder\\subfolder\\file'],

            // Edge cases for relative paths
            [''],   // Empty path
            ['.'],  // Current directory
            ['..'], // Parent directory
        ];
    }

    /**
     * @dataProvider provider_is_absolute_path_for_absolute_paths
     */
    public function test_is_absolute_path_for_absolute_paths(string $path)
    {
        $this->assertTrue(is_absolute_path($path));
    }

    /**
     * @dataProvider provider_is_absolute_path_for_relative_paths
     */
    public function test_is_absolute_path_for_relative_paths(string $path)
    {
        $this->assertFalse(is_absolute_path($path));
    }
}
