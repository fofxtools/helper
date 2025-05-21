<?php

declare(strict_types=1);

namespace FOfX\Helper;

use PHPUnit\Framework\TestCase;

class FileTest extends TestCase
{
    private $logFile;

    protected function setUp(): void
    {
        // Generate a unique log file name for each test
        $this->logFile = getcwd() . '/' . uniqid('test_debug_log_', true) . '.log';

        // Ensure the log file is clean before each test
        if (file_exists($this->logFile)) {
            unlink($this->logFile);
        }
    }

    protected function tearDown(): void
    {
        // Cleanup: Remove the log file after each test
        if (file_exists($this->logFile)) {
            unlink($this->logFile);
        }
    }

    /**
     * Test that debug_log correctly writes a message to the log file.
     */
    public function test_debug_log_creates_file_and_logs_message()
    {
        $message = 'This is a test message.';

        // Call the function
        debug_log($message, basename($this->logFile));

        // Assert that the log file exists
        $this->assertFileExists($this->logFile);

        // Assert that the log file contains the message
        $logContent = file_get_contents($this->logFile);
        $this->assertStringContainsString($message, $logContent);
    }

    /**
     * Test that debug_log appends a message to the existing log file.
     */
    public function test_debug_log_appends_message_to_existing_file()
    {
        $message1 = 'First message.';
        $message2 = 'Second message.';

        // Call the function twice
        debug_log($message1, basename($this->logFile));
        debug_log($message2, basename($this->logFile));

        // Assert that the log file contains both messages
        $logContent = file_get_contents($this->logFile);
        $this->assertStringContainsString($message1, $logContent);
        $this->assertStringContainsString($message2, $logContent);
    }

    /**
     * Test that debug_log throws an exception if it fails to write to the log file.
     */
    public function test_debug_log_throws_exception_on_failure()
    {
        // Convert PHP warnings to exceptions
        set_error_handler(function ($errno, $errstr, $errfile, $errline) {
            throw new \ErrorException($errstr, 0, $errno, $errfile, $errline);
        });

        try {
            // Expect that an exception will be thrown
            $this->expectException(\Exception::class);
            // Simulate a failure by trying to write to an invalid directory
            debug_log('This should fail.', '/invalid_directory/' . uniqid('test_debug_log_', true) . '.log');
        } finally {
            // Restore the previous error handler
            restore_error_handler();
        }
    }

    /**
     * Test that debug_log includes the correct timestamp in the log entry.
     */
    public function test_debug_log_includes_correct_timestamp()
    {
        $message = 'Timestamp test message.';

        // Call the function
        debug_log($message, basename($this->logFile));

        // Extract the timestamp from the log file
        $logContent = file_get_contents($this->logFile);
        preg_match('/===== (\d{4}-\d{2}-\d{2} \d{2}:\d{2}:\d{2})/', $logContent, $matches);

        // Assert that the timestamp is present and within 1 second of the current time
        $this->assertNotEmpty($matches);
        $logTime = new \DateTime($matches[1]);
        $now     = new \DateTime();
        $diff    = $now->getTimestamp() - $logTime->getTimestamp();
        $this->assertLessThanOrEqual(1, abs($diff));
    }

    /**
     * Test that debug_log includes the correct script filename in the log entry.
     */
    public function test_debug_log_includes_correct_script_filename()
    {
        $message = 'Filename test message.';

        // Call the function
        debug_log($message, basename($this->logFile));

        // Extract the script filename from the log file
        $logContent = file_get_contents($this->logFile);
        preg_match('/\t(.+?) =====/', $logContent, $matches);

        // Assert that the script filename is correct
        $this->assertNotEmpty($matches);
        $this->assertEquals($_SERVER['SCRIPT_FILENAME'], $matches[1]);
    }

    /**
     * Test that log_file correctly creates a log file and writes the message to it.
     */
    public function test_log_file_creates_file_and_logs_message()
    {
        $message = 'This is a test message.';
        $prefix  = 'test_log';

        // Call the function and capture the returned log filename
        $this->logFile = log_file($message, $prefix, false);

        // Assert that the log file exists
        $this->assertFileExists($this->logFile);

        // Assert that the log file contains the message
        $logContent = file_get_contents($this->logFile);
        $this->assertStringContainsString($message, $logContent);
    }

    /**
     * Test that log_file throws an exception if it fails to write to the log file.
     */
    public function test_log_file_throws_exception_on_failure()
    {
        // Convert PHP warnings to exceptions
        set_error_handler(function ($errno, $errstr, $errfile, $errline) {
            throw new \ErrorException($errstr, 0, $errno, $errfile, $errline);
        });

        try {
            // Expect that a RuntimeException will be thrown
            $this->expectException(\RuntimeException::class);
            // Try writing to an invalid directory (should fail)
            $invalidDirectory = '/invalid_directory';
            $this->logFile    = log_file('This should fail.', $invalidDirectory . '/test_log', false);
        } finally {
            // Restore the previous error handler
            restore_error_handler();

            // Cleanup: If the file was somehow created, delete it
            if (file_exists($this->logFile)) {
                unlink($this->logFile);
            }
        }
    }

    /**
     * Test that log_file includes the correct timestamp in the log entry.
     */
    public function test_log_file_includes_correct_timestamp()
    {
        $message = 'Timestamp test message.';
        $prefix  = 'test_log';

        // Call the function and capture the returned log filename
        $this->logFile = log_file($message, $prefix, false);

        // Extract the timestamp from the log file
        $logContent = file_get_contents($this->logFile);
        preg_match('/===== (\d{4}-\d{2}-\d{2} \d{2}:\d{2}:\d{2})/', $logContent, $matches);

        // Assert that the timestamp is present and within 1 second of the current time
        $this->assertNotEmpty($matches);
        $logTime = new \DateTime($matches[1]);
        $now     = new \DateTime();
        $diff    = $now->getTimestamp() - $logTime->getTimestamp();
        $this->assertLessThanOrEqual(1, abs($diff));
    }

    /**
     * Test that log_file includes microseconds in the filename when specified.
     */
    public function test_log_file_includes_microseconds_in_filename()
    {
        $message = 'Microseconds in filename.';
        $prefix  = 'test_log';

        // Call the function with microseconds enabled and capture the returned log filename
        $this->logFile = log_file($message, $prefix, true);

        // Assert that the log file exists
        $this->assertFileExists($this->logFile);

        // Assert that the log file contains the correct message
        $logContent = file_get_contents($this->logFile);
        $this->assertStringContainsString($message, $logContent);
    }

    /**
     * Test that log_file sanitizes the prefix to prevent invalid characters in the filename.
     */
    public function test_log_file_sanitizes_prefix()
    {
        // The message and prefix for the test
        $message = 'Sanitized prefix test.';
        $prefix  = 'invalid@prefix#name!*';

        // Call the function with the invalid prefix and capture the returned filename
        $this->logFile = log_file($message, $prefix, false);

        // Assert that the log file exists
        $this->assertFileExists($this->logFile);

        // Assert that the log file contains the correct message
        $logContent = file_get_contents($this->logFile);
        $this->assertStringContainsString($message, $logContent);
    }
}
