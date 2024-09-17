<?php

namespace FOfX\Helper;

use PHPUnit\Framework\TestCase;

class BufferTest extends TestCase
{
    /**
     * Test capture_buffer function without capturing return value
     */
    public function test_capture_buffer_without_return_value()
    {
        // Arrange
        $expected = "Array\n(\n    [0] => 1\n    [1] => 2\n    [2] => 3\n)\n";

        // Act
        $result = capture_buffer('print_r', false, [1, 2, 3], false);

        // Assert
        $this->assertEquals($expected, $result);
    }

    /**
     * Test capture_buffer function with capturing return value
     */
    public function test_capture_buffer_with_return_value()
    {
        // Arrange
        $expected = "Array\n(\n    [0] => 1\n    [1] => 2\n    [2] => 3\n)";

        // Act
        $result = capture_buffer('print_r', true, [1, 2, 3], true);

        // Assert
        // Remove any trailing whitespace (including newlines) from both strings
        $this->assertEquals(
            rtrim($expected),
            rtrim($result),
            "The main content of the captured buffer should match the expected output."
        );

        // Check that the result starts with the expected content
        $this->assertStringStartsWith(
            $expected,
            $result,
            "The captured buffer should start with the expected output."
        );

        // Check that the result only contains additional whitespace after the expected content
        $this->assertMatchesRegularExpression(
            '/^' . preg_quote($expected, '/') . '\s*$/s',
            $result,
            "The captured buffer should only contain additional whitespace after the expected content."
        );
    }

    /**
     * Test capture_buffer function with invalid function name
     */
    public function test_capture_buffer_invalid_function()
    {
        // Assert
        $this->expectException(\InvalidArgumentException::class);

        // Act
        capture_buffer('non_existent_function');
    }

    /**
     * Test capture_eval function without capturing return value
     */
    public function test_capture_eval_without_return_value()
    {
        // Arrange
        $code = 'echo "Hello, World!";';
        $expected = "Hello, World!";

        // Act
        $result = capture_eval($code, get_defined_vars());

        // Assert
        $this->assertEquals($expected, $result);
    }


    /**
     * Test capture_eval function with capturing return value
     */
    public function test_capture_eval_with_return_value()
    {
        // Arrange
        $code = 'echo "Hello"; return "World";';

        // Act
        $result = capture_eval($code, get_defined_vars(), true);

        // Assert
        // Split the result into lines, trimming each line
        $resultLines = array_map('trim', explode("\n", str_replace("\r\n", "\n", $result)));

        $this->assertCount(2, $resultLines, "The result should contain two lines");
        $this->assertEquals("World", $resultLines[0], "The first line should be the return value 'World'");
        $this->assertEquals("Hello", $resultLines[1], "The second line should be the echoed 'Hello'");
    }

    /**
     * Test capture_eval function with syntax error
     */
    public function test_capture_eval_syntax_error()
    {
        $initialLevel = ob_get_level();

        // Arrange
        $code = 'echo "Incomplete string;';

        // Act & Assert
        $this->expectException(\ParseError::class);
        $this->expectExceptionMessage('syntax error, unexpected end of file');

        try {
            capture_eval($code, get_defined_vars());
        } finally {
            // Ensure the output buffer level is restored
            while (ob_get_level() > $initialLevel) {
                ob_end_clean();
            }
        }
    }

    /**
     * Test that capture_eval properly manages output buffers
     */
    public function test_capture_eval_output_buffer_management()
    {
        $initialLevel = ob_get_level();

        $code = 'echo "Hello, World!";';
        capture_eval($code, get_defined_vars());

        $this->assertEquals($initialLevel, ob_get_level(), 'Output buffer level should remain unchanged after capture_eval');
    }
}
