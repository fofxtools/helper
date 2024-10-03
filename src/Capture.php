<?php

/**
 * Capture Class for Output Management
 *
 * This file contains the Capture class, which provides functionality
 * for capturing and managing output from various PHP operations.
 *
 * The Capture class allows for:
 * - Capturing output from function calls
 * - Capturing output from evaluated PHP code
 * - Storing multiple captured outputs
 * - Printing captured outputs in different formats
 *
 * This class is particularly useful for debugging, testing, and
 * scenarios where output needs to be captured and processed
 * rather than directly displayed.
 *
 * The class allows captured outputs to be retrieved, printed,
 * or formatted with HTML tags for display.
 */

namespace FOfX\Helper;

/**
 * Class to help capture the outputs of commands.
 *
 * @example
 * $ini = include __DIR__ . '/../config/config.php';
 * $c = new Helper\Capture();
 * $c->captureEval('FOfX\Helper\get_diagnostics();');
 * $c->captureEval('echo FOfX\Helper\get_remote_addr();');
 * $c->captureEval('print_r($ini);', get_defined_vars());
 * $c->print();
 */
class Capture
{
    // Stores the outputs of the captures.
    private $outputArray = [];

    /**
     * Captures the output of a function into a string and saves it in $this->outputArray.
     *
     * @param string $functionName  The function to capture.
     * @param bool   $captureRetVal Whether to capture and append the return value (default: false).
     * @param mixed  ...$args       The arguments to pass to the function.
     *
     * @return void
     *
     * @see     capture_buffer
     */
    public function captureBuffer(string $functionName, bool $captureRetVal = false, ...$args): void
    {
        $output                           = capture_buffer($functionName, $captureRetVal, ...$args);
        $this->outputArray[$functionName] = $output;
    }

    /**
     * Captures the output of an eval'd string and saves it in $this->outputArray.
     *
     * @param string $code      The code to be evaluated.
     * @param array  $variables The variables to be passed to the eval() function via extract().
     *
     * @return void
     *
     * @see     capture_eval
     */
    public function captureEval(string $code, array $variables = []): void
    {
        $output                   = capture_eval($code, $variables);
        $this->outputArray[$code] = $output;
    }

    /**
     * Prints the captured output.
     *
     * @return void
     */
    public function print(): void
    {
        print_r($this->outputArray);
    }

    /**
     * Prints the captured output with <h2> tags.
     *
     * @return void
     *
     * @see     print_h2
     */
    public function printH2(): void
    {
        print_h2($this->outputArray);
    }

    /**
     * Get the captured outputs.
     *
     * @return array
     */
    public function getOutputs(): array
    {
        return $this->outputArray;
    }
}
