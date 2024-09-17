<?php

/**
 * Output Buffer Capture Functions
 *
 * This file contains functions for capturing output buffers in PHP.
 * It includes two main functions:
 * - capture_buffer(): Captures the output of a function call
 * - capture_eval(): Captures the output from evaluating a string of PHP code
 *
 * These functions are useful for capturing output that would normally be
 * sent directly to the browser, allowing it to be stored in a variable
 * for further processing or delayed output.
 * 
 * Key features:
 * - Capturing output from function calls
 * - Capturing output from evaluated PHP code
 *
 * @package FOfX\Helper
 */

namespace FOfX\Helper;

/**
 * Captures the output of a function into a string and optionally appends the return value if it is a string.
 * 
 * This function can either capture just the output of the function or both the output and its return value.
 * 
 * Example:
 *      $output = Helper\capture_buffer('print_r', false, [1, 2, 3], true);
 * 
 * The output will be blank, since the return value is not captured.
 * 
 * Example:
 *      $output_retval = Helper\capture_buffer('print_r', true, [1, 2, 3], true);
 * 
 * The output will be print_r([1, 2, 3], true), since the return value is captured.
 * 
 * @param  string  $functionName     The name of the function to capture output from.
 * @param  bool    $captureRetVal    Whether to capture and append the return value (default: false).
 * @param  mixed   ...$args          The arguments to pass to the function.
 * @return string                    The captured output and optionally the return value if it's a string.
 * @throws \InvalidArgumentException
 * @throws \Exception
 */
function capture_buffer(string $functionName, bool $captureRetVal = false, ...$args): string
{
    if (!function_exists($functionName)) {
        throw new \InvalidArgumentException("Invalid function name: $functionName");
    }

    ob_start();
    try {
        // Validate the function name
        if (!function_exists($functionName)) {
            throw new \InvalidArgumentException("Invalid function name.");
        }

        // Call the function and capture the return value if needed
        $retval = $functionName(...$args);
        $clean = ob_get_clean();

        // If capturing return value and it's a string, append it to the output
        if ($captureRetVal && is_string($retval)) {
            return $retval . PHP_EOL . $clean;
        }

        return $clean;
    } catch (\Exception $e) {
        // Clean the buffer in case of an exception
        ob_end_clean();
        throw $e;
    }
}

/**
 * Captures the output buffer from eval().
 * 
 * Should be combined with get_defined_vars() since eval() can't access variables outside of its scope.
 * 
 * Example:
 *      $array = ['Apples' => 10, 'Oranges' => 20, 'Pears' => 30];
 *      $code = "FOfX\Helper\print_h2(\$array);";
 *      echo Helper\capture_eval($code, get_defined_vars());
 * 
 * @param  string  $code           The string to be evaluated.
 * @param  array   $variables      The variables to be passed to the eval() function via extract().
 * @param  bool    $captureRetVal  If true, the return value will also be captured.
 * @return string                  The captured output and optionally the return value.
 * @throws \Exception
 */
function capture_eval(string $code, array $variables = [], bool $captureRetVal = false): string
{
    // Extract variables into the local scope of eval()
    // If no variables are provided, use the global scope
    if (!empty($variables)) {
        extract($variables);
    } else {
        extract($GLOBALS);
    }

    // Capture the initial output buffer level
    $initialLevel = ob_get_level();

    // Start a new output buffer
    ob_start();

    try {
        // Evaluate the provided code and capture the return value
        $returnValue = eval($code);
        $outputBuffer = ob_get_clean();

        // If capturing the return value and it's a string, append it to the output
        if ($captureRetVal && is_string($returnValue)) {
            return $returnValue . PHP_EOL . $outputBuffer;
        }

        return $outputBuffer;
    } catch (\Exception $e) {
        // Ensure all buffers opened by this function are closed in case of an exception
        while (ob_get_level() > $initialLevel) {
            ob_end_clean();
        }
        throw $e;
    }
}
