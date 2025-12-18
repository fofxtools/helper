<?php

declare(strict_types=1);

namespace FOfX\Helper;

use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\Attributes\DataProvider;

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
                'Receive'  => '12',
                'Transmit' => '34',
            ];

            $this->assertSame($expected, $result);
        } else {
            // Simulate valid output from /proc/net/dev for Linux
            $procNetDevOutput = "Inter-|   Receive    Transmit\neth0: 12345 0 0 0 0 0 0 0 54321\n";

            // Create a mock callable to simulate shell_exec on Linux (with ip route support)
            $mockExecutor = function ($command) use ($procNetDevOutput) {
                if (str_contains($command, 'ip route')) {
                    return '1.1.1.1 via 192.168.1.1 dev eth0 src 192.168.1.10';
                }

                return $procNetDevOutput;
            };

            // Call get_network_stats and assert the expected result for Linux
            $result   = get_network_stats(false, $mockExecutor);
            $expected = [
                'Receive'  => '12345',
                'Transmit' => '54321',
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
            'Receive'  => '12',
            'Transmit' => '34',
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
        $procNetDevOutput = "Inter-|   Receive    Transmit\neth0: 12345 0 0 0 0 0 0 0 54321\n";

        // Create a mock callable to simulate shell_exec on Linux (with ip route support)
        $mockExecutor = function ($command) use ($procNetDevOutput) {
            if (str_contains($command, 'ip route')) {
                return '1.1.1.1 via 192.168.1.1 dev eth0 src 192.168.1.10';
            }

            return $procNetDevOutput;
        };

        // Call get_network_stats and assert the expected result for Linux
        $result   = get_network_stats(false, $mockExecutor);
        $expected = [
            'Receive'  => '12345',
            'Transmit' => '54321',
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

        $this->expectException(\RuntimeException::class);

        $result = get_network_stats(false, $mockExecutor);
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

            // Create a mock callable to simulate shell_exec on Linux (with ip route support)
            $mockExecutor = function ($command) use ($procNetDevOutput) {
                if (str_contains($command, 'ip route')) {
                    return '1.1.1.1 via 192.168.1.1 dev eth0 src 192.168.1.10';
                }

                return $procNetDevOutput;
            };

            // Call get_network_stats with a PID and assert the expected result for Linux
            $result   = get_network_stats(123, $mockExecutor);
            $expected = [
                'Receive'  => '67890',
                'Transmit' => '98765',
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
            $this->expectException(\InvalidArgumentException::class);

            // Call get_network_stats with an invalid PID
            get_network_stats(-1);
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
            'Receive'  => '12',
            'Transmit' => '34',
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

        $this->expectException(\RuntimeException::class);

        $result = get_windows_network_stats($mockExecutor);
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
        $this->expectException(\InvalidArgumentException::class);

        // Call get_linux_network_stats with an invalid PID
        get_linux_network_stats(-1);
    }

    /**
     * Test get_linux_primary_interface_stats with ip route (Strategy 1).
     */
    public function test_get_linux_primary_interface_stats_with_ip_route()
    {
        if (PHP_OS_FAMILY === 'Windows') {
            $this->markTestSkipped('This test is only applicable to Linux.');
        }

        // Mock executor that simulates both ip route and /proc/net/dev
        $mockExecutor = function ($command) {
            if (str_contains($command, 'ip route')) {
                return "1.1.1.1 via 192.168.1.1 dev enp7s0 src 192.168.1.10 uid 1000\n    cache";
            }

            return "Inter-|   Receive    Transmit\nenp7s0: 12345 0 0 0 0 0 0 0 54321\nlo: 100 0 0 0 0 0 0 0 200\n";
        };

        $result   = get_linux_primary_interface_stats(false, $mockExecutor);
        $expected = [
            'Receive'  => '12345',
            'Transmit' => '54321',
        ];

        $this->assertSame($expected, $result);
    }

    /**
     * Test get_linux_primary_interface_stats with eth0 fallback (Strategy 2).
     */
    public function test_get_linux_primary_interface_stats_with_eth0_fallback()
    {
        if (PHP_OS_FAMILY === 'Windows') {
            $this->markTestSkipped('This test is only applicable to Linux.');
        }

        // Mock executor that simulates no ip route but has eth0
        $mockExecutor = function ($command) {
            if (str_contains($command, 'ip route')) {
                return false; // ip command not available
            }

            return "Inter-|   Receive    Transmit\neth0: 67890 0 0 0 0 0 0 0 98765\nlo: 100 0 0 0 0 0 0 0 200\n";
        };

        $result   = get_linux_primary_interface_stats(false, $mockExecutor);
        $expected = [
            'Receive'  => '67890',
            'Transmit' => '98765',
        ];

        $this->assertSame($expected, $result);
    }

    /**
     * Test get_linux_primary_interface_stats with enp* fallback (Strategy 3).
     */
    public function test_get_linux_primary_interface_stats_with_enp_fallback()
    {
        if (PHP_OS_FAMILY === 'Windows') {
            $this->markTestSkipped('This test is only applicable to Linux.');
        }

        // Mock executor with no ip route, no eth0, but has enp7s0
        $mockExecutor = function ($command) {
            if (str_contains($command, 'ip route')) {
                return false;
            }

            return "Inter-|   Receive    Transmit\nenp7s0: 11111 0 0 0 0 0 0 0 22222\nlo: 100 0 0 0 0 0 0 0 200\n";
        };

        $result   = get_linux_primary_interface_stats(false, $mockExecutor);
        $expected = [
            'Receive'  => '11111',
            'Transmit' => '22222',
        ];

        $this->assertSame($expected, $result);
    }

    /**
     * Test get_linux_primary_interface_stats with wlp* fallback (Strategy 4).
     */
    public function test_get_linux_primary_interface_stats_with_wlp_fallback()
    {
        if (PHP_OS_FAMILY === 'Windows') {
            $this->markTestSkipped('This test is only applicable to Linux.');
        }

        // Mock executor with no ip route, no eth0, no enp*, but has wlo1
        $mockExecutor = function ($command) {
            if (str_contains($command, 'ip route')) {
                return false;
            }

            return "Inter-|   Receive    Transmit\nwlo1: 33333 0 0 0 0 0 0 0 44444\nlo: 100 0 0 0 0 0 0 0 200\n";
        };

        $result   = get_linux_primary_interface_stats(false, $mockExecutor);
        $expected = [
            'Receive'  => '33333',
            'Transmit' => '44444',
        ];

        $this->assertSame($expected, $result);
    }

    /**
     * Test get_linux_primary_interface_stats with first non-loopback fallback (Strategy 5).
     */
    public function test_get_linux_primary_interface_stats_with_first_interface_fallback()
    {
        if (PHP_OS_FAMILY === 'Windows') {
            $this->markTestSkipped('This test is only applicable to Linux.');
        }

        // Mock executor with unusual interface name
        $mockExecutor = function ($command) {
            if (str_contains($command, 'ip route')) {
                return false;
            }

            return "Inter-|   Receive    Transmit\ndocker0: 55555 0 0 0 0 0 0 0 66666\nlo: 100 0 0 0 0 0 0 0 200\n";
        };

        $result   = get_linux_primary_interface_stats(false, $mockExecutor);
        $expected = [
            'Receive'  => '55555',
            'Transmit' => '66666',
        ];

        $this->assertSame($expected, $result);
    }

    /**
     * Test get_linux_primary_interface_stats throws exception when no interface found.
     */
    public function test_get_linux_primary_interface_stats_no_interface_found()
    {
        if (PHP_OS_FAMILY === 'Windows') {
            $this->markTestSkipped('This test is only applicable to Linux.');
        }

        // Mock executor with only loopback interface
        $mockExecutor = function ($command) {
            if (str_contains($command, 'ip route')) {
                return false;
            }

            return "Inter-|   Receive    Transmit\nlo: 100 0 0 0 0 0 0 0 200\n";
        };

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('No suitable network interface found');

        get_linux_primary_interface_stats(false, $mockExecutor);
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
    public function test_escape_windows_cmd_argument_basic_string()
    {
        // Basic input
        $arg = 'simple_argument';

        // Expected output
        $expected = '"simple_argument"';

        // Call the function
        $result = escape_windows_cmd_argument($arg);

        // Assert that the result matches the expected output
        $this->assertEquals($expected, $result);
    }

    /**
     * Test escaping of a string containing spaces.
     *
     * @return void
     */
    public function test_escape_windows_cmd_argument_string_with_spaces()
    {
        // Input containing spaces
        $arg = 'argument with spaces';

        // Expected output
        $expected = '"argument with spaces"';

        // Call the function
        $result = escape_windows_cmd_argument($arg);

        // Assert that the result matches the expected output
        $this->assertEquals($expected, $result);
    }

    /**
     * Test escaping of a string containing double quotes.
     *
     * @return void
     */
    public function test_escape_windows_cmd_argument_string_with_double_quotes()
    {
        // Input containing double quotes
        $arg = 'argument "with quotes"';

        // Expected output
        $expected = '"argument \\"with quotes\\""';

        // Call the function
        $result = escape_windows_cmd_argument($arg);

        // Assert that the result matches the expected output
        $this->assertEquals($expected, $result);
    }

    /**
     * Test escaping of a string containing backslashes.
     *
     * @return void
     */
    public function test_escape_windows_cmd_argument_string_with_backslashes()
    {
        $arg = 'argument\\with\\backslashes';

        // Expected output with escaped backslashes (4 backslashes)
        $expected = '"argument\\with\\backslashes"';

        $result = escape_windows_cmd_argument($arg);

        // Assert that the result matches the expected output
        $this->assertEquals($expected, $result);
    }

    /**
     * Test escaping of a string with mixed backslashes and double quotes.
     *
     * @return void
     */
    public function test_escape_windows_cmd_argument_string_with_backslashes_and_quotes()
    {
        $arg = 'argument\\with\\"both"';

        // Expected output with escaped backslashes and double quotes
        $expected = '"argument\\with\\\\\\"both\\""';

        $result = escape_windows_cmd_argument($arg);

        // Assert that the result matches the expected output
        $this->assertEquals($expected, $result);
    }

    /**
     * Test escaping of an empty string.
     *
     * @return void
     */
    public function test_escape_windows_cmd_argument_empty_string()
    {
        // Empty input
        $arg = '';

        // Expected output
        $expected = '""';

        // Call the function
        $result = escape_windows_cmd_argument($arg);

        // Assert that the result matches the expected output
        $this->assertEquals($expected, $result);
    }

    /**
     * Test escaping of a string containing only backslashes.
     *
     * @return void
     */
    public function test_escape_windows_cmd_argument_only_backslashes()
    {
        $arg = '\\\\\\\\';

        // Expected output with 8 backslashes
        $expected = '"\\\\\\\\\\\\\\\\"';

        $result = escape_windows_cmd_argument($arg);

        // Assert that the result matches the expected output
        $this->assertEquals($expected, $result);
    }

    /**
     * Test escaping of a string containing special characters.
     *
     * @return void
     */
    public function test_escape_windows_cmd_argument_special_characters()
    {
        // Input containing special characters
        $arg = 'arg$#@&^%!*';

        // Expected output
        $expected = '"arg$#@&^%!*"';

        // Call the function
        $result = escape_windows_cmd_argument($arg);

        // Assert that the result matches the expected output
        $this->assertEquals($expected, $result);
    }

    /**
     * Test escaping of a very long string.
     *
     * @return void
     */
    public function test_escape_windows_cmd_argument_very_long_string()
    {
        // Input containing a long string (over 256 characters)
        $arg = str_repeat('a', 300);

        // Expected output
        $expected = '"' . str_repeat('a', 300) . '"';

        // Call the function
        $result = escape_windows_cmd_argument($arg);

        // Assert that the result matches the expected output
        $this->assertEquals($expected, $result);
    }

    /**
     * Test escaping of a string containing newlines.
     *
     * @return void
     */
    public function test_escape_windows_cmd_argument_string_with_newlines()
    {
        $arg = "argument\nwith\nnewlines";

        // Expected output with newlines escaped
        $expected = "\"argument\nwith\nnewlines\"";

        $result = escape_windows_cmd_argument($arg);

        // Assert that the result matches the expected output
        $this->assertEquals($expected, $result);
    }

    /**
     * Test escaping of a string containing tabs.
     *
     * @return void
     */
    public function test_escape_windows_cmd_argument_string_with_tabs()
    {
        $arg = "argument\twith\ttabs";

        // Expected output with tab characters
        $expected = "\"argument\twith\ttabs\"";

        $result = escape_windows_cmd_argument($arg);

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

    #[DataProvider('provider_is_absolute_path_for_absolute_paths')]
    public function test_is_absolute_path_for_absolute_paths(string $path)
    {
        $this->assertTrue(is_absolute_path($path));
    }

    #[DataProvider('provider_is_absolute_path_for_relative_paths')]
    public function test_is_absolute_path_for_relative_paths(string $path)
    {
        $this->assertFalse(is_absolute_path($path));
    }

    /**
     * Provides basic and multibyte character test data for Windows.
     *
     * @return array
     */
    public static function escapeshellcmd_windows_basic_data_provider(): array
    {
        return [
            // Basic ASCII Characters
            'tab'             => [0x09, "\t"],
            'newline'         => [0x0A, "^\n"],
            'carriage_return' => [0x0D, "\r"],
            'space'           => [0x20, ' '],
            'exclamation'     => [0x21, '^!'],
            'double_quote'    => [0x22, '^"'],
            'single_quote'    => [0x27, "^'"],
            'percent'         => [0x25, '^%'],
            'ampersand'       => [0x26, '^&'],
            'asterisk'        => [0x2A, '^*'],
            'semicolon'       => [0x3B, '^;'],
            'less_than'       => [0x3C, '^<'],
            'greater_than'    => [0x3E, '^>'],
            'caret'           => [0x5E, '^^'],
            'dollar'          => [0x24, '^$'],
            'backslash'       => [0x5C, '^\\'],

            // Extended ASCII Characters
            'latin_small_a_grave' => [0xE0, "\xC3\xA0"],
            'latin_small_o_tilde' => [0xF5, "\xC3\xB5"],
            'non_breaking_space'  => [0xA0, "\xC2\xA0"],

            // Multibyte Characters
            'emoji_smile'        => [0x1F600, "\xF0\x9F\x98\x80"],
            'cjk_character'      => [0x4E2D, "\xE4\xB8\xAD"],
            'hiragana_character' => [0x3042, "\xE3\x81\x82"],
            'euro_sign'          => [0x20AC, "\xE2\x82\xAC"],

            // Edge Cases
            'high_surrogate' => [0xD800, "\xED\xA0\x80"],
            'low_surrogate'  => [0xDFFF, "\xED\xBF\xBF"],
            'max_unicode'    => [0x10FFFF, "\xF4\x8F\xBF\xBF"],
            'non_character'  => [0xFFFF, "\xEF\xBF\xBF"],
        ];
    }

    public static function escapeshellcmd_windows_complex_data_provider(): array
    {
        return [
            'string_with_quotes'                => ['echo "Hello \'world\'"', 'echo ^"Hello ^\'world^\'^"'],
            'string_with_semicolon'             => ['ls; rm -rf /', 'ls^; rm -rf /'],
            'string_with_asterisk'              => ['find . -name "*.php"', 'find . -name ^"^*.php^"'],
            'string_with_backticks'             => ['echo `uname -a`', 'echo ^`uname -a^`'],
            'string_with_pipe'                  => ['ps aux | grep apache', 'ps aux ^| grep apache'],
            'string_with_environment'           => ['echo $HOME', 'echo ^$HOME'],
            'string_with_parentheses'           => ['echo (test)', 'echo ^(test^)'],
            'string_with_multiple_escape_chars' => ['echo \\"foo\\"', 'echo ^\^"foo^\^"'],
            'string_with_dollar_and_specials'   => ['echo $VAR & ls', 'echo ^$VAR ^& ls'],
            'string_with_mixed_utf8'            => ['Grüß Gott! 😀', 'Grüß Gott^! 😀'],
            'string_with_utf8_symbols'          => ['⌘ ✈ ☎', '⌘ ✈ ☎'],
            'string_with_newline'               => ["echo -e 'Hello\nWorld'", "echo -e ^'Hello^\nWorld^'"],
            'string_with_backslash'             => ['C:\\Windows\\System32', 'C:^\Windows^\System32'],
            'string_complex_mixed'              => ['echo "O\'Reilly; rm -rf *; echo $PWD"', 'echo ^"O^\'Reilly^; rm -rf ^*^; echo ^$PWD^"'],
        ];
    }

    /**
     * Provides basic and multibyte character test data for Linux.
     *
     * @return array
     */
    public static function escapeshellcmd_linux_basic_data_provider(): array
    {
        return [
            // Basic ASCII Characters
            'tab'             => [0x09, "\t"],
            'newline'         => [0x0A, "\\\n"],
            'carriage_return' => [0x0D, "\r"],
            'space'           => [0x20, ' '],
            'exclamation'     => [0x21, '!'],
            'double_quote'    => [0x22, '\"'],
            'single_quote'    => [0x27, "\\'"],
            'percent'         => [0x25, '%'],
            'ampersand'       => [0x26, '\&'],
            'asterisk'        => [0x2A, '\*'],
            'semicolon'       => [0x3B, '\;'],
            'less_than'       => [0x3C, '\<'],
            'greater_than'    => [0x3E, '\>'],
            'caret'           => [0x5E, '\\^'],
            'dollar'          => [0x24, '\$'],
            'backslash'       => [0x5C, '\\\\'],

            // Extended ASCII Characters
            'latin_small_a_grave' => [0xE0, "\xC3\xA0"],
            'latin_small_o_tilde' => [0xF5, "\xC3\xB5"],
            'non_breaking_space'  => [0xA0, "\xC2\xA0"],

            // Multibyte Characters
            'emoji_smile'        => [0x1F600, "\xF0\x9F\x98\x80"],
            'cjk_character'      => [0x4E2D, "\xE4\xB8\xAD"],
            'hiragana_character' => [0x3042, "\xE3\x81\x82"],
            'euro_sign'          => [0x20AC, "\xE2\x82\xAC"],

            // Edge Cases
            'high_surrogate' => [0xD800, ''],
            'low_surrogate'  => [0xDFFF, ''],
            'max_unicode'    => [0x10FFFF, "\xF4\x8F\xBF\xBF"],
            'non_character'  => [0xFFFF, "\xEF\xBF\xBF"],
        ];
    }

    public static function escapeshellcmd_linux_complex_data_provider(): array
    {
        return [
            'string_with_quotes'                => ['echo "Hello \'world\'"', 'echo "Hello \\\'world\\\'"'],
            'string_with_semicolon'             => ['ls; rm -rf /', 'ls\; rm -rf /'],
            'string_with_asterisk'              => ['find . -name "*.php"', 'find . -name "\*.php"'],
            'string_with_backticks'             => ['echo `uname -a`', 'echo \`uname -a\`'],
            'string_with_pipe'                  => ['ps aux | grep apache', 'ps aux \| grep apache'],
            'string_with_environment'           => ['echo $HOME', 'echo \$HOME'],
            'string_with_parentheses'           => ['echo (test)', 'echo \(test\)'],
            'string_with_multiple_escape_chars' => ['echo \\"foo\\"', 'echo \\\\"foo\\\\"'],
            'string_with_dollar_and_specials'   => ['echo $VAR & ls', 'echo \$VAR \& ls'],
            'string_with_mixed_utf8'            => ['Grüß Gott! 😀', 'Grüß Gott! 😀'],
            'string_with_utf8_symbols'          => ['⌘ ✈ ☎', '⌘ ✈ ☎'],
            'string_with_newline'               => ["echo -e 'Hello\nWorld'", "echo -e 'Hello\\\nWorld'"],
            'string_with_backslash'             => ['C:\\Windows\\System32', 'C:\\\\Windows\\\\System32'],
            'string_complex_mixed'              => ['echo "O\'Reilly; rm -rf *; echo $PWD"', 'echo "O\\\'Reilly\\; rm -rf \\*\\; echo \\$PWD"'],
        ];
    }

    #[DataProvider('escapeshellcmd_windows_basic_data_provider')]
    public function test_escapeshellcmd_os_windows_basic(int $codePoint, string $utf8Char): void
    {
        $char   = mb_convert_encoding('&#' . $codePoint . ';', 'UTF-8', 'HTML-ENTITIES');
        $result = escapeshellcmd_os($char, true);
        $this->assertEquals($utf8Char, $result, "Failed on Windows with input: $utf8Char");
    }

    #[DataProvider('escapeshellcmd_linux_basic_data_provider')]
    public function test_escapeshellcmd_os_linux_basic(int $codePoint, string $utf8Char): void
    {
        $char   = mb_convert_encoding('&#' . $codePoint . ';', 'UTF-8', 'HTML-ENTITIES');
        $result = escapeshellcmd_os($char, false);
        $this->assertEquals($utf8Char, $result, "Failed on Linux with input: $utf8Char");
    }

    /**
     * Test that a ValueError is thrown when a null byte is present.
     */
    public function test_escapeshellcmd_os_null_byte(): void
    {
        $this->expectException(\ValueError::class);
        $this->expectExceptionMessage('Argument #1 ($command) must not contain any null bytes');
        escapeshellcmd_os("\0", true); // For Windows
        escapeshellcmd_os("\0", false); // For Linux
    }

    /**
     * Test escapeshellcmd_os() on Windows with complex strings.
     */
    #[DataProvider('escapeshellcmd_windows_complex_data_provider')]
    public function test_escapeshellcmd_os_windows_complex(string $input, string $expected): void
    {
        $result = escapeshellcmd_os($input, true);
        $this->assertEquals($expected, $result, "Failed on Windows with input: $input");
    }

    /**
     * Test escapeshellcmd_os() on Linux with complex strings.
     */
    #[DataProvider('escapeshellcmd_linux_complex_data_provider')]
    public function test_escapeshellcmd_os_linux_complex(string $input, string $expected): void
    {
        $result = escapeshellcmd_os($input, false);
        $this->assertEquals($expected, $result, "Failed on Linux with input: $input");
    }

    /**
     * Test escapeshellcmd_linux() with basic characters.
     */
    #[DataProvider('escapeshellcmd_linux_basic_data_provider')]
    public function test_escapeshellcmd_linux_basic(int $codePoint, string $utf8Char): void
    {
        $char   = mb_convert_encoding('&#' . $codePoint . ';', 'UTF-8', 'HTML-ENTITIES');
        $result = escapeshellcmd_linux($char);
        $this->assertEquals($utf8Char, $result, "Failed with input: $utf8Char");
    }

    /**
     * Test escapeshellcmd_linux() with complex strings.
     */
    #[DataProvider('escapeshellcmd_linux_complex_data_provider')]
    public function test_escapeshellcmd_linux_complex(string $input, string $expected): void
    {
        $result = escapeshellcmd_linux($input);
        $this->assertEquals($expected, $result, "Failed with input: $input");
    }

    /**
     * Test escapeshellcmd_windows() with basic characters.
     */
    #[DataProvider('escapeshellcmd_windows_basic_data_provider')]
    public function test_escapeshellcmd_windows_basic(int $codePoint, string $utf8Char): void
    {
        $char   = mb_convert_encoding('&#' . $codePoint . ';', 'UTF-8', 'HTML-ENTITIES');
        $result = escapeshellcmd_windows($char);
        $this->assertEquals($utf8Char, $result, "Failed with input: $utf8Char");
    }

    /**
     * Test escapeshellcmd_windows() with complex strings.
     */
    #[DataProvider('escapeshellcmd_windows_complex_data_provider')]
    public function test_escapeshellcmd_windows_complex(string $input, string $expected): void
    {
        $result = escapeshellcmd_windows($input);
        $this->assertEquals($expected, $result, "Failed with input: $input");
    }

    /**
     * Test that escapeshellcmd_linux() throws ValueError for null bytes.
     */
    public function test_escapeshellcmd_linux_null_byte(): void
    {
        $this->expectException(\ValueError::class);
        $this->expectExceptionMessage('Argument #1 ($command) must not contain any null bytes');
        escapeshellcmd_linux("\0");
    }

    /**
     * Test that escapeshellcmd_windows() throws ValueError for null bytes.
     */
    public function test_escapeshellcmd_windows_null_byte(): void
    {
        $this->expectException(\ValueError::class);
        $this->expectExceptionMessage('Argument #1 ($command) must not contain any null bytes');
        escapeshellcmd_windows("\0");
    }

    /**
     * Provides basic test data for Windows escapeshellarg_os.
     */
    public static function escapeshellarg_windows_basic_data_provider(): array
    {
        return [
            // Basic ASCII Characters
            'tab'             => ["\t", "\"\t\""],
            'newline'         => ["\n", "\"\n\""],
            'carriage_return' => ["\r", "\"\r\""],
            'space'           => [' ', '" "'],
            'exclamation'     => ['!', '" "'],  // Replaced with space on Windows
            'double_quote'    => ['"', '" "'],  // Replaced with space on Windows
            'single_quote'    => ["'", "\"'\""],
            'percent'         => ['%', '" "'],  // Replaced with space on Windows
            'ampersand'       => ['&', '"&"'],
            'asterisk'        => ['*', '"*"'],
            'semicolon'       => [';', '";"'],
            'less_than'       => ['<', '"<"'],
            'greater_than'    => ['>', '">"'],
            'caret'           => ['^', '"^"'],
            'dollar'          => ['$', '"$"'],
            'backslash'       => ['\\', '"\\\\"'],

            // Extended ASCII Characters
            'latin_small_a_grave' => ['à', '"à"'],
            'latin_small_o_tilde' => ['õ', '"õ"'],
            'non_breaking_space'  => ["\xC2\xA0", "\"\xC2\xA0\""],

            // Multibyte Characters
            'emoji_smile'        => ['😀', '"😀"'],
            'cjk_character'      => ['中', '"中"'],
            'hiragana_character' => ['あ', '"あ"'],
            'euro_sign'          => ['€', '"€"'],
        ];
    }

    /**
     * Provides complex test data for Windows escapeshellarg_os.
     */
    public static function escapeshellarg_windows_complex_data_provider(): array
    {
        return [
            'string_with_quotes'                => ['echo "Hello \'world\'"', '"echo  Hello \'world\' "'],
            'string_with_semicolon'             => ['ls; rm -rf /', '"ls; rm -rf /"'],
            'string_with_asterisk'              => ['find . -name "*.php"', '"find . -name  *.php "'],
            'string_with_backticks'             => ['echo `uname -a`', '"echo `uname -a`"'],
            'string_with_pipe'                  => ['ps aux | grep apache', '"ps aux | grep apache"'],
            'string_with_environment'           => ['echo $HOME', '"echo $HOME"'],
            'string_with_parentheses'           => ['echo (test)', '"echo (test)"'],
            'string_with_multiple_escape_chars' => ['echo \\"foo\\"', '"echo \\ foo\\ "'],
            'string_with_dollar_and_specials'   => ['echo $VAR & ls', '"echo $VAR & ls"'],
            'string_with_mixed_utf8'            => ['Grüß Gott! 😀', '"Grüß Gott  😀"'],
            'string_with_utf8_symbols'          => ['⌘ ✈ ☎', '"⌘ ✈ ☎"'],
            'string_with_newline'               => ["echo -e 'Hello\nWorld'", "\"echo -e 'Hello\nWorld'\""],
            'string_with_backslash'             => ['C:\\Windows\\System32', '"C:\\Windows\\System32"'],
            'string_complex_mixed'              => ['echo "O\'Reilly; rm -rf *; echo $PWD"', '"echo  O\'Reilly; rm -rf *; echo $PWD "'],
        ];
    }

    /**
     * Provides basic test data for Linux escapeshellarg_os.
     */
    public static function escapeshellarg_linux_basic_data_provider(): array
    {
        return [
            // Basic ASCII Characters
            'tab'             => ["\t", "'\t'"],
            'newline'         => ["\n", "'\n'"],
            'carriage_return' => ["\r", "'\r'"],
            'space'           => [' ', "' '"],
            'exclamation'     => ['!', "'!'"],
            'double_quote'    => ['"', "'\"'"],
            'single_quote'    => ["'", "''\\'''"],
            'percent'         => ['%', "'%'"],
            'ampersand'       => ['&', "'&'"],
            'asterisk'        => ['*', "'*'"],
            'semicolon'       => [';', "';'"],
            'less_than'       => ['<', "'<'"],
            'greater_than'    => ['>', "'>'"],
            'caret'           => ['^', "'^'"],
            'dollar'          => ['$', "'$'"],
            'backslash'       => ['\\', "'\\'"],

            // Extended ASCII Characters
            'latin_small_a_grave' => ['à', "'à'"],
            'latin_small_o_tilde' => ['õ', "'õ'"],
            'non_breaking_space'  => ["\xC2\xA0", "'\xC2\xA0'"],

            // Multibyte Characters
            'emoji_smile'        => ['😀', "'😀'"],
            'cjk_character'      => ['中', "'中'"],
            'hiragana_character' => ['あ', "'あ'"],
            'euro_sign'          => ['€', "'€'"],
        ];
    }

    /**
     * Provides complex test data for Linux escapeshellarg_os.
     */
    public static function escapeshellarg_linux_complex_data_provider(): array
    {
        return [
            'string_with_quotes'                => ['echo "Hello \'world\'"', "'echo \"Hello '\\''world'\\''\"'"],
            'string_with_semicolon'             => ['ls; rm -rf /', "'ls; rm -rf /'"],
            'string_with_asterisk'              => ['find . -name "*.php"', "'find . -name \"*.php\"'"],
            'string_with_backticks'             => ['echo `uname -a`', "'echo `uname -a`'"],
            'string_with_pipe'                  => ['ps aux | grep apache', "'ps aux | grep apache'"],
            'string_with_environment'           => ['echo $HOME', "'echo \$HOME'"],
            'string_with_parentheses'           => ['echo (test)', "'echo (test)'"],
            'string_with_multiple_escape_chars' => ['echo \\"foo\\"', "'echo \\\"foo\\\"'"],
            'string_with_dollar_and_specials'   => ['echo $VAR & ls', "'echo \$VAR & ls'"],
            'string_with_mixed_utf8'            => ['Grüß Gott! 😀', "'Grüß Gott! 😀'"],
            'string_with_utf8_symbols'          => ['⌘ ✈ ☎', "'⌘ ✈ ☎'"],
            'string_with_newline'               => ["echo -e 'Hello\nWorld'", "'echo -e '\\''Hello\nWorld'\\'''"],
            'string_with_backslash'             => ['C:\\Windows\\System32', "'C:\\Windows\\System32'"],
            'string_complex_mixed'              => ['echo "O\'Reilly; rm -rf *; echo $PWD"', "'echo \"O'\\''Reilly; rm -rf *; echo \$PWD\"'"],
        ];
    }

    /**
     * Test escapeshellarg_os() with basic characters for Windows.
     */
    #[DataProvider('escapeshellarg_windows_basic_data_provider')]
    public function test_escapeshellarg_os_windows_basic(string $input, string $expected): void
    {
        $result = escapeshellarg_os($input, true);
        $this->assertEquals($expected, $result, "Failed with input: $input");
    }

    /**
     * Test escapeshellarg_os() with complex strings for Windows.
     */
    #[DataProvider('escapeshellarg_windows_complex_data_provider')]
    public function test_escapeshellarg_os_windows_complex(string $input, string $expected): void
    {
        $result = escapeshellarg_os($input, true);
        $this->assertEquals($expected, $result, "Failed with input: $input");
    }

    /**
     * Test escapeshellarg_os() with basic characters for Linux.
     */
    #[DataProvider('escapeshellarg_linux_basic_data_provider')]
    public function test_escapeshellarg_os_linux_basic(string $input, string $expected): void
    {
        $result = escapeshellarg_os($input, false);
        $this->assertEquals($expected, $result, "Failed with input: $input");
    }

    /**
     * Test escapeshellarg_os() with complex strings for Linux.
     */
    #[DataProvider('escapeshellarg_linux_complex_data_provider')]
    public function test_escapeshellarg_os_linux_complex(string $input, string $expected): void
    {
        $result = escapeshellarg_os($input, false);
        $this->assertEquals($expected, $result, "Failed with input: $input");
    }

    /**
     * Test that escapeshellarg_os() throws ValueError for null bytes.
     */
    public function test_escapeshellarg_os_null_byte(): void
    {
        $this->expectException(\ValueError::class);
        $this->expectExceptionMessage('escapeshellarg_os(): Argument #1 ($arg) must not contain any null bytes');
        escapeshellarg_os("\0", true);  // Test with Windows
        escapeshellarg_os("\0", false); // Test with Linux
    }

    /**
     * Test escapeshellarg_linux() with basic characters.
     */
    #[DataProvider('escapeshellarg_linux_basic_data_provider')]
    public function test_escapeshellarg_linux_basic(string $input, string $expected): void
    {
        $result = escapeshellarg_linux($input);
        $this->assertEquals($expected, $result, "Failed with input: $input");
    }

    /**
     * Test escapeshellarg_linux() with complex strings.
     */
    #[DataProvider('escapeshellarg_linux_complex_data_provider')]
    public function test_escapeshellarg_linux_complex(string $input, string $expected): void
    {
        $result = escapeshellarg_linux($input);
        $this->assertEquals($expected, $result, "Failed with input: $input");
    }

    /**
     * Test escapeshellarg_windows() with basic characters.
     */
    #[DataProvider('escapeshellarg_windows_basic_data_provider')]
    public function test_escapeshellarg_windows_basic(string $input, string $expected): void
    {
        $result = escapeshellarg_windows($input);
        $this->assertEquals($expected, $result, "Failed with input: $input");
    }

    /**
     * Test escapeshellarg_windows() with complex strings.
     */
    #[DataProvider('escapeshellarg_windows_complex_data_provider')]
    public function test_escapeshellarg_windows_complex(string $input, string $expected): void
    {
        $result = escapeshellarg_windows($input);
        $this->assertEquals($expected, $result, "Failed with input: $input");
    }

    /**
     * Test that escapeshellarg_linux() throws ValueError for null bytes.
     */
    public function test_escapeshellarg_linux_null_byte(): void
    {
        $this->expectException(\ValueError::class);
        $this->expectExceptionMessage('escapeshellarg_os(): Argument #1 ($arg) must not contain any null bytes');
        escapeshellarg_linux("\0");
    }

    /**
     * Test that escapeshellarg_windows() throws ValueError for null bytes.
     */
    public function test_escapeshellarg_windows_null_byte(): void
    {
        $this->expectException(\ValueError::class);
        $this->expectExceptionMessage('escapeshellarg_os(): Argument #1 ($arg) must not contain any null bytes');
        escapeshellarg_windows("\0");
    }

    /**
     * Test resolv_conf_nameserver_ip() returns valid IP or null
     */
    public function test_resolv_conf_nameserver_ip_returns_valid_ip_or_null(): void
    {
        // Skip if not on Linux
        if (PHP_OS_FAMILY !== 'Linux') {
            $this->markTestSkipped('This test is only applicable to Linux.');
        }

        $result = resolv_conf_nameserver_ip();

        // Result should be either a valid IP or null
        $this->assertTrue(
            $result === null || filter_var($result, FILTER_VALIDATE_IP) !== false,
            'Expected valid IP address or null'
        );
    }

    /**
     * Test resolv_conf_nameserver_ip() does not return localhost
     */
    public function test_resolv_conf_nameserver_ip_not_localhost(): void
    {
        // Skip if not on Linux
        if (PHP_OS_FAMILY !== 'Linux') {
            $this->markTestSkipped('This test is only applicable to Linux.');
        }

        $result = resolv_conf_nameserver_ip();

        if ($result !== null) {
            $this->assertNotEquals(
                '127.0.0.1',
                $result,
                'Should filter out localhost and return Windows host IP'
            );
        } else {
            // If null, test passes (no localhost to check)
            $this->assertNull($result);
        }
    }

    /**
     * Test resolv_conf_nameserver_ip() returns single IP without newlines
     */
    public function test_resolv_conf_nameserver_ip_no_newlines(): void
    {
        // Skip if not on Linux
        if (PHP_OS_FAMILY !== 'Linux') {
            $this->markTestSkipped('This test is only applicable to Linux.');
        }

        $result = resolv_conf_nameserver_ip();

        if ($result !== null) {
            $this->assertStringNotContainsString(
                "\n",
                $result,
                'Should return a single IP address without newlines'
            );
        } else {
            // If null, test passes (no newlines to check)
            $this->assertNull($result);
        }
    }

    /**
     * Test wsl_default_route_ip() returns valid IP or null
     */
    public function test_wsl_default_route_ip_returns_valid_ip_or_null(): void
    {
        // Skip if not on Linux
        if (PHP_OS_FAMILY !== 'Linux') {
            $this->markTestSkipped('This test is only applicable to Linux.');
        }

        $result = wsl_default_route_ip();

        // Result should be either a valid IP or null
        $this->assertTrue(
            $result === null || filter_var($result, FILTER_VALIDATE_IP) !== false,
            'Expected valid IP address or null from default route'
        );
    }

    /**
     * Test wsl_default_route_ip() does not return localhost
     */
    public function test_wsl_default_route_ip_not_localhost(): void
    {
        // Skip if not on Linux
        if (PHP_OS_FAMILY !== 'Linux') {
            $this->markTestSkipped('This test is only applicable to Linux.');
        }

        $result = wsl_default_route_ip();

        if ($result !== null) {
            $this->assertNotEquals(
                '127.0.0.1',
                $result,
                'Default route should not be localhost'
            );
        } else {
            // If null, test passes (no localhost to check)
            $this->assertNull($result);
        }
    }

    /**
     * Test wsl_default_route_ip() returns single IP without newlines
     */
    public function test_wsl_default_route_ip_no_newlines(): void
    {
        // Skip if not on Linux
        if (PHP_OS_FAMILY !== 'Linux') {
            $this->markTestSkipped('This test is only applicable to Linux.');
        }

        $result = wsl_default_route_ip();

        if ($result !== null) {
            $this->assertStringNotContainsString(
                "\n",
                $result,
                'Should return a single IP address without newlines'
            );
        } else {
            // If null, test passes (no newlines to check)
            $this->assertNull($result);
        }
    }

    /**
     * Test wsl_default_route_ip() returns private IP in WSL environment
     */
    public function test_wsl_default_route_ip_returns_private_ip_in_wsl(): void
    {
        // Skip if not on Linux
        if (PHP_OS_FAMILY !== 'Linux') {
            $this->markTestSkipped('This test is only applicable to Linux.');
        }

        // Only test this in actual WSL environment
        if (!getenv('WSL_DISTRO_NAME')) {
            $this->markTestSkipped('This test is only applicable in WSL environment.');
        }

        $result = wsl_default_route_ip();

        if ($result !== null) {
            $this->assertTrue(
                str_starts_with($result, '192.168.') ||
                str_starts_with($result, '172.') ||
                str_starts_with($result, '10.'),
                'WSL default route should typically be a private IP address'
            );
        } else {
            // If null, mark as skipped since we can't test
            $this->markTestSkipped('Could not get default route IP to test.');
        }
    }

    /**
     * Data provider for wsl_url() tests
     *
     * @return array<string,array{string}>
     */
    public static function wsl_url_provider(): array
    {
        return [
            'localhost_only'                  => ['http://localhost'],
            'localhost_basic'                 => ['http://localhost/api'],
            'localhost_with_port'             => ['http://localhost:8000/api'],
            'localhost_with_port_and_version' => ['http://localhost:10001/api/v1'],
            'external_api'                    => ['http://api.example.com/v1'],
            'ip_basic'                        => ['http://127.0.0.1/api'],
            'ip_with_port'                    => ['http://172.20.128.1:8000/api'],
            'ip_with_port_and_version'        => ['http://172.20.128.1:10001/api/v1'],
        ];
    }

    /**
     * Test wsl_url() function
     */
    #[DataProvider('wsl_url_provider')]
    public function test_wsl_url(string $url): void
    {
        // Mock environment check
        $isWsl = PHP_OS_FAMILY === 'Linux' && getenv('WSL_DISTRO_NAME');

        if ($isWsl) {
            // In WSL environment
            $result = wsl_url($url);

            if (str_contains($url, 'localhost')) {
                // localhost URLs should be modified
                $this->assertStringNotContainsString(
                    'localhost',
                    $result,
                    'WSL should convert localhost to IP address'
                );

                // Should preserve port if present
                if (preg_match('/:\d+/', $url, $matches)) {
                    $this->assertStringContainsString(
                        $matches[0],
                        $result,
                        'WSL should preserve port number'
                    );
                }
            } else {
                // Non-localhost URLs should remain unchanged
                $this->assertSame($url, $result, 'Non-localhost URL should not be modified');
            }
        } else {
            // Not in WSL environment - URLs should remain unchanged
            $result = wsl_url($url);
            $this->assertSame($url, $result, 'URL should not be modified outside WSL');
        }
    }
}
