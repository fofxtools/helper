<?php

declare(strict_types=1);

namespace FOfX\Helper;

use PHPUnit\Framework\TestCase;

/**
 * Test case for the Capture class
 */
class CaptureTest extends TestCase
{
    /**
     * @var Capture
     */
    private $capture;

    /**
     * Set up the test environment
     */
    protected function setUp(): void
    {
        $this->capture = new Capture();
    }

    /**
     * Test the captureBuffer method
     */
    public function testCaptureBuffer(): void
    {
        $this->capture->captureBuffer('print_r', true, [1, 2, 3], true);

        $expected = "Array\n(\n    [0] => 1\n    [1] => 2\n    [2] => 3\n)\n";
        $actual   = $this->capture->getOutputs()['print_r'];

        $this->assertStringContainsString($expected, $actual);
    }

    /**
     * Test the captureEval method
     */
    public function testCaptureEval(): void
    {
        $code = 'echo "Hello, World!";';
        $this->capture->captureEval($code);

        $expected = 'Hello, World!';
        $actual   = $this->capture->getOutputs()[$code];

        $this->assertEquals($expected, $actual);
    }

    /**
     * Test the captureEval method with variables
     */
    public function testCaptureEvalWithVariables(): void
    {
        $code      = 'echo $message;';
        $variables = ['message' => 'Hello from variable!'];
        $this->capture->captureEval($code, $variables);

        $expected = 'Hello from variable!';
        $actual   = $this->capture->getOutputs()[$code];

        $this->assertEquals($expected, $actual);
    }

    /**
     * Test the print method
     */
    public function testPrint(): void
    {
        $this->capture->captureEval('echo "Test Output";');

        $expected = "Array\n(\n    [echo \"Test Output\";] => Test Output\n)\n";

        $this->expectOutputString($expected);
        $this->capture->print();
    }

    /**
     * Test the printH2 method
     */
    public function testPrintH2(): void
    {
        $this->capture->captureEval('echo "Test H2 Output";');

        $expected = '<h2>echo "Test H2 Output";</h2>' . PHP_EOL . 'Test H2 Output';

        $this->expectOutputString($expected);
        $this->capture->printH2();
    }

    /**
     * Test the getOutputs method
     */
    public function testGetOutputs(): void
    {
        $this->capture->captureEval('echo "Output 1";');
        $this->capture->captureEval('echo "Output 2";');

        $expected = [
            'echo "Output 1";' => 'Output 1',
            'echo "Output 2";' => 'Output 2',
        ];

        $this->assertEquals($expected, $this->capture->getOutputs());
    }

    /**
     * Test capturing multiple outputs
     */
    public function testMultipleCaptures(): void
    {
        $this->capture->captureBuffer('print_r', true, [1, 2, 3], true);
        $this->capture->captureEval('echo "Eval Output";');

        $outputs = $this->capture->getOutputs();

        $this->assertCount(2, $outputs);
        $this->assertArrayHasKey('print_r', $outputs);
        $this->assertArrayHasKey('echo "Eval Output";', $outputs);
    }

    /**
     * Test error handling in captureEval
     */
    public function testCaptureEvalError(): void
    {
        $code = 'echo $undefinedVariable;';

        $warningOccurred = false;

        set_error_handler(function ($errno, $errstr) use (&$warningOccurred) {
            $warningOccurred = true;

            return true;
        }, E_WARNING);

        try {
            $this->capture->captureEval($code);

            $outputs = $this->capture->getOutputs();

            $this->assertArrayHasKey($code, $outputs);
            $this->assertTrue($warningOccurred, 'A warning should have occurred');
            $this->assertEmpty($outputs[$code], 'The output should be empty');
        } finally {
            restore_error_handler();
        }
    }
}
