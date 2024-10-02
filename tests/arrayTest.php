<?php

namespace FOfX\Helper;

use PHPUnit\Framework\TestCase;

class ArrayTest extends TestCase
{
    /**
     * Test super_unique with a simple array.
     */
    public function test_super_unique_with_simple_array()
    {
        $array = [1, 2, 2, 3, 4, 4, 5];
        $expected = [1, 2, 3, 4, 5];
        $this->assertEquals($expected, array_values(super_unique($array)));
    }

    /**
     * Test super_unique with a multidimensional array.
     */
    public function test_super_unique_with_multidimensional_array()
    {
        $array = [
            [1, 2, 3],
            [1, 2, 3],
            [4, 5, 6],
            [7, 8, 9],
            [4, 5, 6]
        ];
        $expected = [
            [1, 2, 3],
            [4, 5, 6],
            [7, 8, 9]
        ];
        $this->assertEquals($expected, array_values(super_unique($array)));
    }

    /**
     * Test super_unique with an empty array.
     */
    public function test_super_unique_with_empty_array()
    {
        $array = [];
        $expected = [];
        $this->assertEquals($expected, array_values(super_unique($array)));
    }

    /**
     * Test super_unique with an array containing mixed types.
     */
    public function test_super_unique_with_mixed_types_array()
    {
        $array = [1, '1', 2, '2', 3, '3'];
        $expected = [1, '1', 2, '2', 3, '3'];
        $this->assertEquals($expected, array_values(super_unique($array)));
    }

    /**
     * Test super_unique with a deeply nested array.
     */
    public function test_super_unique_with_nested_array()
    {
        $array = [
            [1, 2, [3, 4]],
            [1, 2, [3, 4]],
            [5, 6, [7, 8]],
            [5, 6, [7, 8]]
        ];
        $expected = [
            [1, 2, [3, 4]],
            [5, 6, [7, 8]]
        ];
        $this->assertEquals($expected, array_values(super_unique($array)));
    }

    /**
     * Test case: Simple one-dimensional array.
     * This test checks if the function correctly implodes a simple array
     * with numeric elements into a comma-separated string.
     */
    public function test_recursive_implode_with_simple_array()
    {
        $array = [1, 2, 3];
        $expected = '1,2,3';
        $this->assertEquals($expected, recursive_implode($array));
    }

    /**
     * Test case: Multi-dimensional array.
     * This test checks if the function correctly implodes a multi-dimensional array
     * into a flat comma-separated string, ensuring that all nested elements are included.
     */
    public function test_recursive_implode_with_multidimensional_array()
    {
        $array = [1, [2, 3], 4];
        $expected = '1,2,3,4';
        $this->assertEquals($expected, recursive_implode($array));
    }

    /**
     * Test case: Include keys in the output.
     * This test checks if the function correctly includes keys in the imploded string
     * when the $include_keys parameter is set to true.
     */
    public function test_recursive_implode_include_keys()
    {
        $array = ['a' => 1, 'b' => 2, 'c' => 3];
        $expected = 'a,1,b,2,c,3';
        $this->assertEquals($expected, recursive_implode($array, ',', true));
    }

    /**
     * Test case: Trim all whitespace from the output.
     * This test checks if the function correctly trims all whitespace from the elements
     * in the array before creating the final imploded string when the $trim_all parameter is set to true.
     */
    public function test_recursive_implode_trim_all_whitespace()
    {
        $array = [' a ', ' b ', ' c '];
        $expected = 'a,b,c';
        $this->assertEquals($expected, recursive_implode($array, ',', false, true));
    }

    /**
     * Test case: Handle objects within the array.
     * This test checks if the function correctly serializes objects in the array
     * and includes them in the imploded string, avoiding fatal errors related to object-to-string conversion.
     */
    public function test_recursive_implode_with_objects()
    {
        $object = new \stdClass();
        $object->property = 'value';
        $array = [1, $object, 3];
        $expected = '1;' . serialize($object) . ';3';
        $this->assertEquals($expected, recursive_implode($array, ';'));
    }

    /**
     * Test case: Empty array.
     * This test checks if the function correctly handles an empty array,
     * returning an empty string as the result.
     */
    public function test_recursive_implode_with_empty_array()
    {
        $array = [];
        $expected = '';
        $this->assertEquals($expected, recursive_implode($array));
    }

    /**
     * Test that pre_r returns the formatted array as a string when $returnAsString is TRUE.
     */
    public function test_pre_r_returns_formatted_string()
    {
        $array = ['a' => 1, 'b' => 2];
        $expected = "<pre>\nArray\n(\n    [a] => 1\n    [b] => 2\n)\n</pre>\n";

        $result = pre_r($array, true);

        $this->assertEquals($expected, $result);
    }

    /**
     * Test that pre_r outputs the formatted array when $returnAsString is FALSE.
     */
    public function test_pre_r_prints_formatted_output()
    {
        $array = ['x' => 'foo', 'y' => 'bar'];
        $expectedOutput = "<pre>\nArray\n(\n    [x] => foo\n    [y] => bar\n)\n</pre>\n";

        $this->expectOutputString($expectedOutput);
        pre_r($array, false);
    }

    /**
     * Test that is_int_index returns true for a positive integer.
     */
    public function test_is_int_index_returns_true_for_positive_integer()
    {
        $this->assertTrue(is_int_index(5));
    }

    /**
     * Test that is_int_index returns true for zero.
     */
    public function test_is_int_index_returns_true_for_zero()
    {
        $this->assertTrue(is_int_index(0));
    }

    /**
     * Test that is_int_index returns false for a negative integer.
     */
    public function test_is_int_index_returns_false_for_negative_integer()
    {
        $this->assertFalse(is_int_index(-1));
    }

    /**
     * Test that is_int_index returns false for a non-integer value.
     */
    public function test_is_int_index_returns_false_for_non_integer()
    {
        $this->assertFalse(is_int_index('string'));
        $this->assertFalse(is_int_index(4.5));
        $this->assertFalse(is_int_index(null));
        $this->assertFalse(is_int_index([]));
    }

    /**
     * Test case: All elements in the array are numeric.
     * This includes integers, floats, and numeric strings.
     * The expected result is TRUE, since all elements are numeric.
     */
    public function test_array_is_numeric_with_all_numeric_elements()
    {
        $array = [1, 2, 3, 4.5, '6'];
        $result = array_is_numeric($array);
        $this->assertTrue($result);
    }

    /**
     * Test case: The array contains both numeric and non-numeric elements.
     * The expected result is FALSE, since not all elements are numeric.
     */
    public function test_array_is_numeric_with_non_numeric_elements()
    {
        $array = [1, 2, 'abc', 4.5];
        $result = array_is_numeric($array);
        $this->assertFalse($result);
    }

    /**
     * Test case: The array is empty, and $check_empty is set to TRUE.
     * The expected result is FALSE, since an empty array should be considered non-numeric
     * when $check_empty is TRUE.
     */
    public function test_array_is_numeric_with_empty_array_and_check_empty_true()
    {
        $array = [];
        $result = array_is_numeric($array, TRUE);
        $this->assertFalse($result);
    }

    /**
     * Test case: The array is empty, and $check_empty is set to FALSE.
     * The expected result is TRUE, since the array doesn't contain any non-numeric items,
     * and an empty array is valid when $check_empty is FALSE.
     */
    public function test_array_is_numeric_with_empty_array_and_check_empty_false()
    {
        $array = [];
        $result = array_is_numeric($array, FALSE);
        $this->assertTrue($result);
    }

    /**
     * Test case: The array contains various forms of zero (0, '0', 0.0) and a small float.
     * The expected result is TRUE, since all elements are numeric.
     */
    public function test_array_is_numeric_with_mixed_elements_including_zero()
    {
        $array = [0, '0', 0.0, 0.5];
        $result = array_is_numeric($array);
        $this->assertTrue($result);
    }

    /**
     * Test case: The array contains only non-numeric elements.
     * This includes strings, NULL, arrays, and objects.
     * The expected result is FALSE, since none of the elements are numeric.
     */
    public function test_array_is_numeric_with_only_non_numeric_elements()
    {
        $array = ['abc', null, [], new \stdClass()];
        $result = array_is_numeric($array);
        $this->assertFalse($result);
    }

    /**
     * Test case: All elements in the array are positive numeric values.
     * This test checks if the function correctly returns TRUE for an array of positive numbers.
     */
    public function test_array_is_positive_numeric_with_all_positive_numbers()
    {
        $array = [1, 2, 3, 4.5, 6];
        $result = array_is_positive_numeric($array);
        $this->assertTrue($result);
    }

    /**
     * Test case: The array contains a negative number.
     * This test checks if the function correctly returns FALSE when the array contains at least one negative number.
     */
    public function test_array_is_positive_numeric_with_negative_number()
    {
        $array = [1, -2, 3];
        $result = array_is_positive_numeric($array);
        $this->assertFalse($result);
    }

    /**
     * Test case: The array contains a non-numeric value.
     * This test checks if the function correctly returns FALSE when the array contains a non-numeric value.
     */
    public function test_array_is_positive_numeric_with_non_numeric_value()
    {
        $array = [1, 2, 'abc'];
        $result = array_is_positive_numeric($array);
        $this->assertFalse($result);
    }

    /**
     * Test case: The array is empty and $check_empty is TRUE.
     * This test checks if the function correctly returns FALSE when the array is empty and $check_empty is TRUE.
     */
    public function test_array_is_positive_numeric_with_empty_array_and_check_empty_true()
    {
        $array = [];
        $result = array_is_positive_numeric($array, TRUE);
        $this->assertFalse($result);
    }

    /**
     * Test case: The array is empty and $check_empty is FALSE.
     * This test checks if the function correctly returns TRUE when the array is empty and $check_empty is FALSE.
     */
    public function test_array_is_positive_numeric_with_empty_array_and_check_empty_false()
    {
        $array = [];
        $result = array_is_positive_numeric($array, FALSE);
        $this->assertTrue($result);
    }

    /**
     * Test case: The array contains zero (0).
     * This test checks if the function correctly returns TRUE when the array contains zero, as zero is non-negative.
     */
    public function test_array_is_positive_numeric_with_zero()
    {
        $array = [0, 1, 2];
        $result = array_is_positive_numeric($array);
        $this->assertTrue($result);
    }

    /**
     * Test case: The array contains only negative numbers.
     * This test checks if the function correctly returns FALSE when all elements in the array are negative.
     */
    public function test_array_is_positive_numeric_with_all_negative_numbers()
    {
        $array = [-1, -2, -3];
        $result = array_is_positive_numeric($array);
        $this->assertFalse($result);
    }

    /**
     * Test case: All elements in the array are valid integer indexes (0 or higher).
     * This test checks if the function correctly returns TRUE for an array of valid integer indexes.
     */
    public function test_array_is_int_indexes_with_valid_integer_indexes()
    {
        $array = [0, 1, 2, 3];
        $result = array_is_int_indexes($array);
        $this->assertTrue($result);
    }

    /**
     * Test case: The array contains a non-integer value.
     * This test checks if the function correctly returns FALSE when the array contains at least one non-integer value.
     */
    public function test_array_is_int_indexes_with_non_integer_value()
    {
        $array = [0, 1, '2', 3];
        $result = array_is_int_indexes($array);
        $this->assertFalse($result);
    }

    /**
     * Test case: The array contains a negative integer.
     * This test checks if the function correctly returns FALSE when the array contains a negative integer.
     */
    public function test_array_is_int_indexes_with_negative_integer()
    {
        $array = [0, -1, 2, 3];
        $result = array_is_int_indexes($array);
        $this->assertFalse($result);
    }

    /**
     * Test case: The array is empty and $check_empty is TRUE.
     * This test checks if the function correctly returns FALSE when the array is empty and $check_empty is TRUE.
     */
    public function test_array_is_int_indexes_with_empty_array_and_check_empty_true()
    {
        $array = [];
        $result = array_is_int_indexes($array, TRUE);
        $this->assertFalse($result);
    }

    /**
     * Test case: The array is empty and $check_empty is FALSE.
     * This test checks if the function correctly returns TRUE when the array is empty and $check_empty is FALSE.
     */
    public function test_array_is_int_indexes_with_empty_array_and_check_empty_false()
    {
        $array = [];
        $result = array_is_int_indexes($array, FALSE);
        $this->assertTrue($result);
    }

    /**
     * Test case: The array contains non-integer values (e.g., strings, floats).
     * This test checks if the function correctly returns FALSE when the array contains non-integer values.
     */
    public function test_array_is_int_indexes_with_mixed_non_integer_values()
    {
        $array = [0, 'string', 2.5, 3];
        $result = array_is_int_indexes($array);
        $this->assertFalse($result);
    }

    /**
     * Test case: The array has only numeric keys.
     * This test checks if the function correctly returns FALSE when the array contains only numeric keys.
     */
    public function test_has_string_keys_with_numeric_keys()
    {
        $array = [1, 2, 3];
        $result = has_string_keys($array);
        $this->assertFalse($result);
    }

    /**
     * Test case: The array has string keys.
     * This test checks if the function correctly returns TRUE when the array contains at least one string key.
     */
    public function test_has_string_keys_with_string_keys()
    {
        $array = ['a' => 1, 'b' => 2, 'c' => 3];
        $result = has_string_keys($array);
        $this->assertTrue($result);
    }

    /**
     * Test case: The array has mixed numeric and string keys.
     * This test checks if the function correctly returns TRUE when the array contains both numeric and string keys.
     */
    public function test_has_string_keys_with_mixed_keys()
    {
        $array = [1, 'b' => 2, 3];
        $result = has_string_keys($array);
        $this->assertTrue($result);
    }

    /**
     * Test case: The array is empty.
     * This test checks if the function correctly returns FALSE when the array is empty.
     */
    public function test_has_string_keys_with_empty_array()
    {
        $array = [];
        $result = has_string_keys($array);
        $this->assertFalse($result);
    }

    /**
     * Test case: The array has no arrays as values.
     * This test checks if the function correctly returns FALSE when the array contains only non-array values.
     */
    public function test_has_array_values_with_no_array_values()
    {
        $array = [1, 2, 3, 'string', null];
        $result = has_array_values($array);
        $this->assertFalse($result);
    }

    /**
     * Test case: The array has an array as one of its values.
     * This test checks if the function correctly returns TRUE when the array contains at least one array value.
     */
    public function test_has_array_values_with_array_value()
    {
        $array = [1, 2, [3, 4], 'string'];
        $result = has_array_values($array);
        $this->assertTrue($result);
    }

    /**
     * Test case: The array is empty.
     * This test checks if the function correctly returns FALSE when the array is empty.
     */
    public function test_has_array_values_with_empty_array()
    {
        $array = [];
        $result = has_array_values($array);
        $this->assertFalse($result);
    }

    /**
     * Test case: The array has only arrays as values.
     * This test checks if the function correctly returns TRUE when the array contains only arrays as its values.
     */
    public function test_has_array_values_with_only_array_values()
    {
        $array = [[1, 2], ['a', 'b'], [3.14, true]];
        $result = has_array_values($array);
        $this->assertTrue($result);
    }

    /**
     * Test case: The array has mixed types, including arrays.
     * This test checks if the function correctly returns TRUE when the array contains mixed types, including arrays.
     */
    public function test_has_array_values_with_mixed_values()
    {
        $array = [1, [2, 3], 'string', 4.5, null, [5, 6]];
        $result = has_array_values($array);
        $this->assertTrue($result);
    }

    /**
     * Test case: The array has only arrays as values.
     * This test checks if the function correctly returns TRUE when all elements in the array are arrays.
     */
    public function test_has_only_array_values_with_only_array_values()
    {
        $array = [[1, 2], ['a', 'b'], [3.14, true]];
        $result = has_only_array_values($array);
        $this->assertTrue($result);
    }

    /**
     * Test case: The array has mixed values, including arrays and non-arrays.
     * This test checks if the function correctly returns FALSE when the array contains at least one non-array value.
     */
    public function test_has_only_array_values_with_mixed_values()
    {
        $array = [[1, 2], 'string', [3.14, true]];
        $result = has_only_array_values($array);
        $this->assertFalse($result);
    }

    /**
     * Test case: The array has no array values, only non-array values.
     * This test checks if the function correctly returns FALSE when none of the elements in the array are arrays.
     */
    public function test_has_only_array_values_with_no_array_values()
    {
        $array = [1, 2, 'string', null];
        $result = has_only_array_values($array);
        $this->assertFalse($result);
    }

    /**
     * Test case: The array is empty.
     * This test checks if the function correctly returns TRUE when the array is empty, as there are no non-array values.
     */
    public function test_has_only_array_values_with_empty_array()
    {
        $array = [];
        $result = has_only_array_values($array);
        $this->assertTrue($result);
    }

    /**
     * Test case: The array has nested arrays only.
     * This test checks if the function correctly returns TRUE when all elements are arrays, including deeply nested arrays.
     */
    public function test_has_only_array_values_with_nested_arrays()
    {
        $array = [[[1, 2]], [['a', 'b']], [[3.14, true]]];
        $result = has_only_array_values($array);
        $this->assertTrue($result);
    }

    /**
     * Test case: The array has only arrays of the same size.
     * This test checks if the function correctly returns TRUE when all arrays within the array have the same size.
     */
    public function test_has_only_similar_array_values_with_same_size_arrays()
    {
        $array = [[1, 2], ['a', 'b'], [true, false]];
        $result = has_only_similar_array_values($array);
        $this->assertTrue($result);
    }

    /**
     * Test case: The array has arrays of different sizes.
     * This test checks if the function correctly returns FALSE when the arrays within the array have different sizes.
     */
    public function test_has_only_similar_array_values_with_different_size_arrays()
    {
        $array = [[1, 2], ['a', 'b', 'c'], [true, false]];
        $result = has_only_similar_array_values($array);
        $this->assertFalse($result);
    }

    /**
     * Test case: The array has a mix of arrays and non-array values.
     * This test checks if the function correctly returns FALSE when the array contains non-array values.
     */
    public function test_has_only_similar_array_values_with_mixed_values()
    {
        $array = [[1, 2], 'string', [true, false]];
        $result = has_only_similar_array_values($array);
        $this->assertFalse($result);
    }

    /**
     * Test case: The array is empty.
     * This test checks if the function correctly returns TRUE when the array is empty.
     */
    public function test_has_only_similar_array_values_with_empty_array()
    {
        $array = [];
        $result = has_only_similar_array_values($array);
        $this->assertTrue($result);
    }

    /**
     * Test case: The array has arrays of equal size, but one of the arrays is empty.
     * This test checks if the function correctly returns TRUE when all arrays are of the same size, even if they are empty.
     */
    public function test_has_only_similar_array_values_with_empty_and_non_empty_arrays()
    {
        $array = [[], [], []];
        $result = has_only_similar_array_values($array);
        $this->assertTrue($result);
    }

    /**
     * Test case: The array has only one array value.
     * This test checks if the function correctly returns TRUE when the array contains only one array value.
     */
    public function test_has_only_similar_array_values_with_single_array_value()
    {
        $array = [[1, 2, 3]];
        $result = has_only_similar_array_values($array);
        $this->assertTrue($result);
    }

    /**
     * Test case: All arrays have the same keys.
     * This test checks if the function correctly returns TRUE when all input arrays have the same set of keys.
     */
    public function test_array_same_keys_with_same_keys()
    {
        $arrays = [
            ['a' => 1, 'b' => 2, 'c' => 3],
            ['a' => 'A', 'b' => 'B', 'c' => 'C'],
            ['a' => 'X', 'b' => 'Y', 'c' => 'Z']
        ];
        $result = array_same_keys(...$arrays);
        $this->assertTrue($result);
    }

    /**
     * Test case: Arrays have different keys.
     * This test checks if the function correctly returns FALSE when the input arrays have different sets of keys.
     */
    public function test_array_same_keys_with_different_keys()
    {
        $arrays = [
            [1, 2, 3],
            ['a' => 'A', 'b' => 'B', 'c' => 'C'],
            [0 => 'X', 1 => 'Y', 2 => 'Z']
        ];
        $result = array_same_keys(...$arrays);
        $this->assertFalse($result);
    }

    /**
     * Test case: The arrays have the same keys but in different orders.
     * This test checks if the function correctly returns FALSE when the arrays have the same keys but in different orders.
     */
    public function test_array_same_keys_with_keys_in_different_order()
    {
        $arrays = [
            ['a' => 'A', 'b' => 'B', 'c' => 'C'],
            ['c' => 'C', 'b' => 'B', 'a' => 'A']
        ];
        $result = array_same_keys(...$arrays);
        $this->assertFalse($result);
    }

    /**
     * Test case: One of the arrays is empty.
     * This test checks if the function correctly returns FALSE when one of the input arrays is empty.
     */
    public function test_array_same_keys_with_one_empty_array()
    {
        $arrays = [
            [1, 2, 3],
            []
        ];
        $result = array_same_keys(...$arrays);
        $this->assertFalse($result);
    }

    /**
     * Test case: All arrays are empty.
     * This test checks if the function correctly returns TRUE when all input arrays are empty.
     */
    public function test_array_same_keys_with_all_empty_arrays()
    {
        $arrays = [
            [],
            []
        ];
        $result = array_same_keys(...$arrays);
        $this->assertTrue($result);
    }

    /**
     * Test case: Only one array is passed.
     * This test checks if the function correctly returns TRUE when only one array is passed as input.
     */
    public function test_array_same_keys_with_single_array()
    {
        $arrays = [
            [1, 2, 3]
        ];
        $result = array_same_keys(...$arrays);
        $this->assertTrue($result);
    }

    /**
     * Test case: All arrays have the same count.
     * This test checks if the function correctly returns TRUE when all arrays have the same number of elements.
     */
    public function test_array_same_counts_with_same_counts()
    {
        $arrays = [
            [1, 2, 3],
            ['a' => 'A', 'b' => 'B', 'c' => 'C'],
            [100, 200, 300]
        ];
        $result = array_same_counts(...$arrays);
        $this->assertTrue($result);
    }

    /**
     * Test case: Arrays have different counts.
     * This test checks if the function correctly returns FALSE when the arrays have different numbers of elements.
     */
    public function test_array_same_counts_with_different_counts()
    {
        $arrays = [
            [1, 2, 3],
            ['a' => 'A', 'b' => 'B'],
            [100, 200, 300]
        ];
        $result = array_same_counts(...$arrays);
        $this->assertFalse($result);
    }

    /**
     * Test case: Mixed types with non-array values.
     * This test checks if the function correctly returns FALSE when non-array values are passed.
     */
    public function test_array_same_counts_with_non_array_values()
    {
        $arrays = [
            [1, 2, 3],
            'not an array',
            [100, 200, 300]
        ];
        $result = array_same_counts(...$arrays);
        $this->assertFalse($result);
    }

    /**
     * Test case: Single array input.
     * This test checks if the function correctly returns TRUE when only one array is passed.
     */
    public function test_array_same_counts_with_single_array()
    {
        $arrays = [
            [1, 2, 3]
        ];
        $result = array_same_counts(...$arrays);
        $this->assertTrue($result);
    }

    /**
     * Test case: All arrays are empty.
     * This test checks if the function correctly returns TRUE when all arrays are empty.
     */
    public function test_array_same_counts_with_all_empty_arrays()
    {
        $arrays = [
            [],
            [],
            []
        ];
        $result = array_same_counts(...$arrays);
        $this->assertTrue($result);
    }

    /**
     * Test case: One array is empty, and others are not.
     * This test checks if the function correctly returns FALSE when one array is empty and others are not.
     */
    public function test_array_same_counts_with_one_empty_array()
    {
        $arrays = [
            [1, 2, 3],
            [],
            [100, 200, 300]
        ];
        $result = array_same_counts(...$arrays);
        $this->assertFalse($result);
    }

    /**
     * Test case: Array with multiple integer keys.
     * This test checks if the function correctly returns the highest numeric key when the array has multiple integer keys.
     */
    public function test_max_int_key_with_multiple_integer_keys()
    {
        $array = [10 => 'a', 20 => 'b', 5 => 'c'];
        $result = max_int_key($array);
        $this->assertEquals(20, $result);
    }

    /**
     * Test case: Array with mixed integer and string keys.
     * This test checks if the function correctly returns the highest numeric key when the array has both integer and string keys.
     */
    public function test_max_int_key_with_mixed_keys()
    {
        $array = [1 => 'a', 'b' => 'b', 3 => 'c', 'd' => 'd'];
        $result = max_int_key($array);
        $this->assertEquals(3, $result);
    }

    /**
     * Test case: Array with only string keys.
     * This test checks if the function correctly returns FALSE when the array has no numeric keys.
     */
    public function test_max_int_key_with_string_keys_only()
    {
        $array = ['a' => 'a', 'b' => 'b', 'c' => 'c'];
        $result = max_int_key($array);
        $this->assertFalse($result);
    }

    /**
     * Test case: Empty array.
     * This test checks if the function correctly returns FALSE when the array is empty.
     */
    public function test_max_int_key_with_empty_array()
    {
        $array = [];
        $result = max_int_key($array);
        $this->assertFalse($result);
    }

    /**
     * Test case: Array with only one integer key.
     * This test checks if the function correctly returns the integer key when the array has only one numeric key.
     */
    public function test_max_int_key_with_one_integer_key()
    {
        $array = [5 => 'a'];
        $result = max_int_key($array);
        $this->assertEquals(5, $result);
    }

    /**
     * Test case: Array with negative integer keys.
     * This test checks if the function correctly returns the highest numeric key when the array has negative integer keys.
     */
    public function test_max_int_key_with_negative_integer_keys()
    {
        $array = [-10 => 'a', -20 => 'b', -5 => 'c'];
        $result = max_int_key($array);
        $this->assertEquals(-5, $result);
    }

    /**
     * Test case: Array with a mix of positive and negative integer keys.
     * This test checks if the function correctly returns the highest numeric key when the array has both positive and negative integer keys.
     */
    public function test_max_int_key_with_positive_and_negative_integer_keys()
    {
        $array = [-10 => 'a', 0 => 'b', 15 => 'c'];
        $result = max_int_key($array);
        $this->assertEquals(15, $result);
    }

    /**
     * Test case: Empty array.
     * This test checks if the function correctly returns 0 when the array is empty.
     */
    public function test_next_int_key_with_empty_array()
    {
        $array = [];
        $result = next_int_key($array);
        $this->assertEquals(0, $result);
    }

    /**
     * Test case: Array with only integer keys.
     * This test checks if the function correctly returns the next integer key (highest key + 1) when the array has only integer keys.
     */
    public function test_next_int_key_with_integer_keys()
    {
        $array = [10 => 'a', 20 => 'b', 5 => 'c'];
        $result = next_int_key($array);
        $this->assertEquals(21, $result);
    }

    /**
     * Test case: Array with mixed integer and string keys.
     * This test checks if the function correctly returns the next integer key when the array has both integer and string keys.
     */
    public function test_next_int_key_with_mixed_keys()
    {
        $array = [1 => 'a', 'b' => 'b', 3 => 'c', 'd' => 'd'];
        $result = next_int_key($array);
        $this->assertEquals(4, $result);
    }

    /**
     * Test case: Array with only string keys.
     * This test checks if the function correctly returns 0 when the array has no integer keys.
     */
    public function test_next_int_key_with_string_keys_only()
    {
        $array = ['a' => 'a', 'b' => 'b', 'c' => 'c'];
        $result = next_int_key($array);
        $this->assertEquals(0, $result);
    }

    /**
     * Test case: Array with negative integer keys.
     * This test checks if the function correctly returns the next integer key when the array has negative integer keys.
     */
    public function test_next_int_key_with_negative_integer_keys()
    {
        $array = [-10 => 'a', -20 => 'b', -5 => 'c'];
        $result = next_int_key($array);
        $this->assertEquals(-4, $result);
    }

    /**
     * Test case: Array with a mix of positive and negative integer keys.
     * This test checks if the function correctly returns the next integer key when the array has both positive and negative integer keys.
     */
    public function test_next_int_key_with_positive_and_negative_integer_keys()
    {
        $array = [-10 => 'a', 0 => 'b', 15 => 'c'];
        $result = next_int_key($array);
        $this->assertEquals(16, $result);
    }

    /**
     * Test case: Slice a numeric array with default parameters.
     * This test checks if the function correctly slices the first 50 elements of a numeric array.
     */
    public function test_slice_with_default_parameters_on_numeric_array()
    {
        $array = range(1, 100);
        $result = slice($array);
        $expected = range(1, 50);
        $this->assertEquals($expected, $result);
    }

    /**
     * Test case: Slice a numeric array with a specific offset.
     * This test checks if the function correctly slices a numeric array starting from a given offset.
     */
    public function test_slice_with_offset_on_numeric_array()
    {
        $array = range(1, 100);
        $result = slice($array, 10);
        $expected = range(11, 60);
        $this->assertEquals($expected, $result);
    }

    /**
     * Test case: Slice a numeric array with a specific length.
     * This test checks if the function correctly slices a numeric array with a specified length.
     */
    public function test_slice_with_length_on_numeric_array()
    {
        $array = range(1, 100);
        $result = slice($array, 0, 10);
        $expected = range(1, 10);
        $this->assertEquals($expected, $result);
    }

    /**
     * Test case: Slice a numeric array with both offset and length.
     * This test checks if the function correctly slices a numeric array with both a specified offset and length.
     */
    public function test_slice_with_offset_and_length_on_numeric_array()
    {
        $array = range(1, 100);
        $result = slice($array, 10, 20);
        $expected = range(11, 30);
        $this->assertEquals($expected, $result);
    }

    /**
     * Test case: Slice a string-keyed associative array.
     * This test checks if the function correctly preserves string keys when slicing an associative array.
     */
    public function test_slice_with_string_keyed_associative_array()
    {
        $array = ['a' => 1, 'b' => 2, 'c' => 3, 'd' => 4, 'e' => 5];
        $result = slice($array, 1, 2);
        $expected = ['b' => 2, 'c' => 3];
        $this->assertEquals($expected, $result);
    }

    /**
     * Test case: Slice an integer-keyed associative array.
     * This test checks if the function correctly reorders integer keys when slicing an associative array with integer keys.
     */
    public function test_slice_with_integer_keyed_associative_array()
    {
        $array = [10 => 'a', 20 => 'b', 30 => 'c', 40 => 'd', 50 => 'e'];
        $result = slice($array, 1, 3);
        $expected = [0 => 'b', 1 => 'c', 2 => 'd'];
        $this->assertEquals($expected, $result);
    }

    /**
     * Test case: Slice a numeric array with a negative offset.
     * This test checks if the function correctly slices a numeric array starting from a negative offset.
     */
    public function test_slice_with_negative_offset_on_numeric_array()
    {
        $array = range(1, 100);
        $result = slice($array, -10);
        $expected = range(91, 100);
        $this->assertEquals($expected, $result);
    }

    /**
     * Test case: Slice a numeric array with a negative length.
     * This test checks if the function correctly slices a numeric array with a negative length.
     */
    public function test_slice_with_negative_length_on_numeric_array()
    {
        $array = range(1, 100);
        $result = slice($array, 10, -10);
        $expected = range(11, 90);
        $this->assertEquals($expected, $result);
    }

    /**
     * Test case: Printing a sliced numeric array without returning it as a string.
     * This test checks if the function correctly prints the sliced array without returning a string.
     */
    public function test_rslice_without_returning_string()
    {
        $array = range(1, 100);

        // Use output buffering to capture the output
        ob_start();
        rSlice($array);
        // Get the output and clear the buffer
        $output = ob_get_clean();

        // Generate the expected output string
        $expectedOutput = print_r(slice($array, 0, 50), true);

        // Assert that the captured output matches the expected output
        $this->assertEquals($expectedOutput, $output);
    }

    /**
     * Test case: Returning a sliced numeric array as a string.
     * This test checks if the function correctly returns the sliced array as a string.
     */
    public function test_rslice_with_returning_string()
    {
        $array = range(1, 100);
        $result = rSlice($array, 10, 20, true);
        $expected = print_r(slice($array, 10, 20), true);
        $this->assertEquals($expected, $result);
    }

    /**
     * Test case: Printing a sliced associative array with string keys without returning it as a string.
     * This test checks if the function correctly prints the sliced associative array without returning a string.
     */
    public function test_rslice_with_associative_array_without_returning_string()
    {
        $array = ['a' => 1, 'b' => 2, 'c' => 3, 'd' => 4, 'e' => 5];
        // Use output buffering to capture the output
        ob_start();
        rSlice($array, 1, 2);
        // Get the output and clear the buffer
        $output = ob_get_clean();
        ob_start();
        print_r(slice($array, 1, 2), false);
        $expectedOutput = ob_get_clean();
        $this->assertEquals($expectedOutput, $output);
    }

    /**
     * Test case: Returning a sliced associative array with string keys as a string.
     * This test checks if the function correctly returns the sliced associative array as a string.
     */
    public function test_rslice_with_associative_array_returning_string()
    {
        $array = ['a' => 1, 'b' => 2, 'c' => 3, 'd' => 4, 'e' => 5];
        $result = rSlice($array, 1, 2, true);
        $expected = print_r(slice($array, 1, 2), true);
        $this->assertEquals($expected, $result);
    }

    /**
     * Test case: Printing a sliced numeric array with a specific offset and length.
     * This test checks if the function correctly prints the sliced array with the given offset and length.
     */
    public function test_rslice_with_offset_and_length_without_returning_string()
    {
        $array = range(1, 100);
        // Use output buffering to capture the output
        ob_start();
        rSlice($array, 10, 20);
        // Get the output and clear the buffer
        $output = ob_get_clean();
        ob_start();
        print_r(slice($array, 10, 20), false);
        $expectedOutput = ob_get_clean();
        $this->assertEquals($expectedOutput, $output);
    }

    /**
     * Test case: Returning a sliced numeric array with a specific offset and length as a string.
     * This test checks if the function correctly returns the sliced array with the given offset and length as a string.
     */
    public function test_rslice_with_offset_and_length_returning_string()
    {
        $array = range(1, 100);
        $result = rSlice($array, 10, 20, true);
        $expected = print_r(slice($array, 10, 20), true);
        $this->assertEquals($expected, $result);
    }

    /**
     * Test case: Truncate an associative array with default parameters.
     * This test checks if the function correctly truncates the array with default offset and length.
     */
    public function test_assoc_array_truncate_with_default_parameters()
    {
        $array = [
            'first' => [10, 'twenty', 30, 'forty', 50, 60, 70],
            'second' => [10, 20, 30, 40, 50],
            'third' => [1, 2, 3, 4, 5],
            'fourth' => [5, 4, 3, 2, 1],
            'fifth' => 'Hello',
        ];
        $result = assoc_array_truncate($array);
        $expected = [
            'first' => [10],
            'second' => [10],
            'third' => [1],
            'fourth' => [5],
            'fifth' => 'Hello',
        ];
        $this->assertEquals($expected, $result);
    }

    /**
     * Test case: Truncate with custom offset and length, preserving keys.
     * This test checks if the function correctly truncates with a specified offset and length, preserving keys.
     */
    public function test_assoc_array_truncate_with_custom_offset_and_length_preserving_keys()
    {
        $array = [
            'first' => [10, 'twenty', 30, 'forty', 50, 60, 70],
            'second' => [10, 20, 30, 40, 50],
            'third' => [1, 2, 3, 4, 5],
            'fourth' => [5, 4, 3, 2, 1],
        ];
        $result = assoc_array_truncate($array, 2, 2);
        $expected = [
            'first' => [2 => 30, 3 => 'forty'],
            'second' => [2 => 30, 3 => 40],
            'third' => [2 => 3, 3 => 4],
            'fourth' => [2 => 3, 3 => 2],
        ];
        $this->assertEquals($expected, $result);
    }

    /**
     * Test case: Truncate with custom offset and length, reordering keys.
     * This test checks if the function correctly truncates with a specified offset and length, reordering keys.
     */
    public function test_assoc_array_truncate_with_custom_offset_and_length_reordering_keys()
    {
        $array = [
            'first' => [10, 'twenty', 30, 'forty', 50, 60, 70],
            'second' => [10, 20, 30, 40, 50],
            'third' => [1, 2, 3, 4, 5],
            'fourth' => [5, 4, 3, 2, 1],
        ];
        $result = assoc_array_truncate($array, 2, 2, FALSE);
        $expected = [
            'first' => [30, 'forty'],
            'second' => [30, 40],
            'third' => [3, 4],
            'fourth' => [3, 2],
        ];
        $this->assertEquals($expected, $result);
    }

    /**
     * Test case: Truncate with offset greater than array length.
     * This test checks if the function correctly handles cases where the offset is greater than the array length.
     */
    public function test_assoc_array_truncate_with_offset_greater_than_array_length()
    {
        $array = [
            'first' => [10, 'twenty', 30],
            'second' => [10, 20],
            'third' => [1, 2, 3],
        ];
        $result = assoc_array_truncate($array, 5, 2);
        $expected = [];
        $this->assertEquals($expected, $result);
    }

    /**
     * Test case: Truncate with non-array elements.
     * This test checks if the function correctly handles arrays that contain non-array elements.
     */
    public function test_assoc_array_truncate_with_non_array_elements()
    {
        $array = [
            'first' => [10, 'twenty', 30],
            'second' => 'Hello',
            'third' => [1, 2, 3],
            'fourth' => 123,
        ];
        $result = assoc_array_truncate($array, 0, 2);
        $expected = [
            'first' => [10, 'twenty'],
            'second' => 'Hello',
            'third' => [1, 2],
            'fourth' => 123,
        ];
        $this->assertEquals($expected, $result);
    }

    /**
     * Test case: Truncate with an invalid offset and length.
     * This test checks if the function correctly throws an exception when invalid offset and length are provided.
     */
    public function test_assoc_array_truncate_with_invalid_offset_and_length()
    {
        $this->expectException(\InvalidArgumentException::class);
        assoc_array_truncate(['first' => [10, 20, 30]], -1, 0);
    }

    /**
     * Test case: Slice an associative array with default parameters.
     * This test checks if the function correctly slices the array with default offset and length.
     */
    public function test_slice_assoc_with_default_parameters()
    {
        $array = [
            'first' => [10, 'twenty', 30, 'forty', 50, 60, 70],
            'second' => [10, 20, 30, 40, 50],
            'third' => [1, 2, 3, 4, 5],
            'fourth' => [5, 4, 3, 2, 1],
            'fifth' => 'Hello',
        ];
        $result = slice_assoc($array);
        $expected = [
            'first' => [10, 'twenty', 30, 'forty', 50, 60, 70],
            'second' => [10, 20, 30, 40, 50],
            'third' => [1, 2, 3, 4, 5],
            'fourth' => [5, 4, 3, 2, 1],
            'fifth' => 'Hello',
        ];
        $this->assertEquals($expected, $result);
    }

    /**
     * Test case: Slice with custom offset and length, preserving keys.
     * This test checks if the function correctly slices with a specified offset and length, preserving keys.
     */
    public function test_slice_assoc_with_custom_offset_and_length_preserving_keys()
    {
        $array = [
            'first' => [10, 'twenty', 30, 'forty', 50, 60, 70],
            'second' => [10, 20, 30, 40, 50],
            'third' => [1, 2, 3, 4, 5],
            'fourth' => [5, 4, 3, 2, 1],
        ];
        $result = slice_assoc($array, 2, 2);
        $expected = [
            'first' => [2 => 30, 3 => 'forty'],
            'second' => [2 => 30, 3 => 40],
            'third' => [2 => 3, 3 => 4],
            'fourth' => [2 => 3, 3 => 2],
        ];
        $this->assertEquals($expected, $result);
    }

    /**
     * Test case: Slice with custom offset and length, reordering keys.
     * This test checks if the function correctly slices with a specified offset and length, reordering keys.
     */
    public function test_slice_assoc_with_custom_offset_and_length_reordering_keys()
    {
        $array = [
            'first' => [10, 'twenty', 30, 'forty', 50, 60, 70],
            'second' => [10, 20, 30, 40, 50],
            'third' => [1, 2, 3, 4, 5],
            'fourth' => [5, 4, 3, 2, 1],
        ];
        $result = slice_assoc($array, 2, 2, FALSE);
        $expected = [
            'first' => [30, 'forty'],
            'second' => [30, 40],
            'third' => [3, 4],
            'fourth' => [3, 2],
        ];
        $this->assertEquals($expected, $result);
    }

    /**
     * Test case: Slice with offset greater than array length.
     * This test checks if the function correctly handles cases where the offset is greater than the array length.
     */
    public function test_slice_assoc_with_offset_greater_than_array_length()
    {
        $array = [
            'first' => [10, 'twenty', 30],
            'second' => [10, 20],
            'third' => [1, 2, 3],
        ];
        $result = slice_assoc($array, 5, 2);
        $expected = [];
        $this->assertEquals($expected, $result);
    }

    /**
     * Test case: Slice with non-array elements.
     * This test checks if the function correctly handles arrays that contain non-array elements.
     */
    public function test_slice_assoc_with_non_array_elements()
    {
        $array = [
            'first' => [10, 'twenty', 30],
            'second' => 'Hello',
            'third' => [1, 2, 3],
            'fourth' => 123,
        ];
        $result = slice_assoc($array, 0, 2);
        $expected = [
            'first' => [10, 'twenty'],
            'second' => 'Hello',
            'third' => [1, 2],
            'fourth' => 123,
        ];
        $this->assertEquals($expected, $result);
    }

    /**
     * Test case: Slice with an invalid offset and length.
     * This test checks if the function correctly throws an exception when invalid offset and length are provided.
     */
    public function test_slice_assoc_with_invalid_offset_and_length()
    {
        $this->expectException(\InvalidArgumentException::class);
        slice_assoc(['first' => [10, 20, 30]], -1, 0);
    }

    /**
     * Test case: Transpose a balanced 2-dimensional indexed array.
     * This test checks if the function correctly transposes a balanced array with associative sub-arrays.
     */
    public function test_transpose_indexed_array_with_balanced_array()
    {
        $array = [
            ['a' => 1, 'b' => 2, 'c' => 3],
            ['a' => 4, 'b' => 5, 'c' => 6],
            ['a' => 7, 'b' => 8, 'c' => 9],
        ];
        $result = transpose_indexed_array($array);
        $expected = [
            'a' => [0 => 1, 1 => 4, 2 => 7],
            'b' => [0 => 2, 1 => 5, 2 => 8],
            'c' => [0 => 3, 1 => 6, 2 => 9],
        ];
        $this->assertEquals($expected, $result);
    }

    /**
     * Test case: Transpose with non-array elements.
     * This test checks if the function throws an exception when given an array containing non-array elements.
     */
    public function test_transpose_indexed_array_with_non_array_elements()
    {
        $this->expectException(\InvalidArgumentException::class);
        $array = [
            ['a' => 1, 'b' => 2, 'c' => 3],
            'not an array',
            ['a' => 7, 'b' => 8, 'c' => 9],
        ];
        transpose_indexed_array($array);
    }

    /**
     * Test case: Transpose with unbalanced sub-arrays.
     * This test checks if the function throws an exception when given an unbalanced array (sub-arrays of different sizes).
     */
    public function test_transpose_indexed_array_with_unbalanced_sub_arrays()
    {
        $this->expectException(\InvalidArgumentException::class);
        $array = [
            ['a' => 1, 'b' => 2],
            ['a' => 3, 'b' => 4, 'c' => 5],
            ['a' => 6, 'b' => 7],
        ];
        transpose_indexed_array($array);
    }

    /**
     * Test case: Transpose an empty array.
     * This test checks if the function correctly handles an empty array.
     */
    public function test_transpose_indexed_array_with_empty_array()
    {
        $array = [];
        $result = transpose_indexed_array($array);
        $expected = [];
        $this->assertEquals($expected, $result);
    }

    /**
     * Test case: Transpose with associative keys in sub-arrays.
     * This test checks if the function correctly transposes arrays with associative keys.
     */
    public function test_transpose_indexed_array_with_associative_keys()
    {
        $array = [
            ['name' => 'Alice', 'age' => 30],
            ['name' => 'Bob', 'age' => 25],
            ['name' => 'Charlie', 'age' => 35],
        ];
        $result = transpose_indexed_array($array);
        $expected = [
            'name' => [0 => 'Alice', 1 => 'Bob', 2 => 'Charlie'],
            'age' => [0 => 30, 1 => 25, 2 => 35],
        ];
        $this->assertEquals($expected, $result);
    }

    /**
     * Test case: Transpose a balanced 2-dimensional associative array.
     * This test checks if the function correctly transposes a balanced associative array with indexed sub-arrays.
     */
    public function test_transpose_associative_array_with_balanced_array()
    {
        $array = [
            'row1' => ['a' => 1, 'b' => 2, 'c' => 3],
            'row2' => ['a' => 4, 'b' => 5, 'c' => 6],
            'row3' => ['a' => 7, 'b' => 8, 'c' => 9],
        ];
        $result = transpose_associative_array($array);
        $expected = [
            'a' => ['row1' => 1, 'row2' => 4, 'row3' => 7],
            'b' => ['row1' => 2, 'row2' => 5, 'row3' => 8],
            'c' => ['row1' => 3, 'row2' => 6, 'row3' => 9],
        ];
        $this->assertEquals($expected, $result);
    }

    /**
     * Test case: Transpose with non-array elements.
     * This test checks if the function throws an exception when given an array containing non-array elements.
     */
    public function test_transpose_associative_array_with_non_array_elements()
    {
        $this->expectException(\InvalidArgumentException::class);
        $array = [
            'row1' => ['a' => 1, 'b' => 2, 'c' => 3],
            'row2' => 'not an array',
            'row3' => ['a' => 7, 'b' => 8, 'c' => 9],
        ];
        transpose_associative_array($array);
    }

    /**
     * Test case: Transpose with unbalanced sub-arrays.
     * This test checks if the function throws an exception when given an unbalanced array (sub-arrays of different sizes).
     */
    public function test_transpose_associative_array_with_unbalanced_sub_arrays()
    {
        $this->expectException(\InvalidArgumentException::class);
        $array = [
            'row1' => ['a' => 1, 'b' => 2],
            'row2' => ['a' => 3, 'b' => 4, 'c' => 5],
            'row3' => ['a' => 6, 'b' => 7],
        ];
        transpose_associative_array($array);
    }

    /**
     * Test case: Transpose an empty array.
     * This test checks if the function correctly handles an empty array.
     */
    public function test_transpose_associative_array_with_empty_array()
    {
        $array = [];
        $result = transpose_associative_array($array);
        $expected = [];
        $this->assertEquals($expected, $result);
    }

    /**
     * Test case: Transpose with different keys in sub-arrays.
     * This test checks if the function correctly transposes arrays with different keys in the sub-arrays.
     */
    public function test_transpose_associative_array_with_different_keys()
    {
        $array = [
            'row1' => ['a' => 1, 'b' => 2],
            'row2' => ['c' => 3, 'd' => 4],
            'row3' => ['e' => 5, 'f' => 6],
        ];
        $result = transpose_associative_array($array);
        $expected = [
            'a' => ['row1' => 1],
            'b' => ['row1' => 2],
            'c' => ['row2' => 3],
            'd' => ['row2' => 4],
            'e' => ['row3' => 5],
            'f' => ['row3' => 6],
        ];
        $this->assertEquals($expected, $result);
    }

    /**
     * Test case: Convert a balanced 2-dimensional array into an HTML table.
     * This test checks if the function correctly generates an HTML table from a balanced array.
     */
    public function test_array_to_table_with_balanced_array()
    {
        $array = [
            'Name' => ['Alice', 'Bob', 'Charlie'],
            'Age' => [25, 30, 35],
            'Occupation' => ['Engineer', 'Designer', 'Manager']
        ];

        $expected = "<table>\n\t<thead>\n\t\t<tr>\n\t\t\t<th>Name</th>\n\t\t\t<th>Age</th>\n\t\t\t<th>Occupation</th>\n\t\t</tr>\n\t</thead>\n\t<tbody>\n\t\t<tr>\n\t\t\t<td>Alice</td>\n\t\t\t<td>25</td>\n\t\t\t<td>Engineer</td>\n\t\t</tr>\n\t\t<tr>\n\t\t\t<td>Bob</td>\n\t\t\t<td>30</td>\n\t\t\t<td>Designer</td>\n\t\t</tr>\n\t\t<tr>\n\t\t\t<td>Charlie</td>\n\t\t\t<td>35</td>\n\t\t\t<td>Manager</td>\n\t\t</tr>\n\t</tbody>\n</table>";

        $this->assertEquals($expected, array_to_table($array));
    }

    /**
     * Test case: Convert an empty array into an empty HTML table.
     * This test checks if the function correctly generates an empty HTML table when the input array is empty.
     */
    public function test_array_to_table_with_empty_array()
    {
        $array = [];

        $expected = "<table>\n</table>";

        $this->assertEquals($expected, array_to_table($array));
    }

    /**
     * Test case: Convert a balanced array into an HTML table with id, class, and style attributes.
     * This test checks if the function correctly adds the id, class, and style attributes to the generated HTML table.
     */
    public function test_array_to_table_with_attributes()
    {
        $array = [
            'Name' => ['Alice', 'Bob'],
            'Age' => [25, 30]
        ];

        $expected = "<table id=\"table_id\" class=\"table-class\" style=\"width: 100%\">\n\t<thead>\n\t\t<tr>\n\t\t\t<th>Name</th>\n\t\t\t<th>Age</th>\n\t\t</tr>\n\t</thead>\n\t<tbody>\n\t\t<tr>\n\t\t\t<td>Alice</td>\n\t\t\t<td>25</td>\n\t\t</tr>\n\t\t<tr>\n\t\t\t<td>Bob</td>\n\t\t\t<td>30</td>\n\t\t</tr>\n\t</tbody>\n</table>";

        $this->assertEquals($expected, array_to_table($array, 'table_id', 'table-class', 'width:100%'));
    }

    /**
     * Test case: Handle a non-balanced array by throwing an InvalidArgumentException.
     * This test checks if the function correctly throws an exception when the input array is not balanced.
     */
    public function test_array_to_table_with_non_balanced_array()
    {
        $this->expectException(\InvalidArgumentException::class);

        $array = [
            'Name' => ['Alice', 'Bob', 'Charlie'],
            'Age' => [25, 30],
            'Occupation' => ['Engineer', 'Designer', 'Manager']
        ];

        array_to_table($array);
    }

    /**
     * Test case: Ensure the function escapes special characters in array keys and values.
     * This test checks if the function correctly escapes special characters to prevent XSS attacks.
     */
    public function test_array_to_table_with_special_characters()
    {
        $array = [
            'Name' => ['<Alice>', 'Bob & Charlie'],
            'Age' => [25, 30]
        ];

        $expected = "<table>\n\t<thead>\n\t\t<tr>\n\t\t\t<th>Name</th>\n\t\t\t<th>Age</th>\n\t\t</tr>\n\t</thead>\n\t<tbody>\n\t\t<tr>\n\t\t\t<td>&lt;Alice&gt;</td>\n\t\t\t<td>25</td>\n\t\t</tr>\n\t\t<tr>\n\t\t\t<td>Bob &amp; Charlie</td>\n\t\t\t<td>30</td>\n\t\t</tr>\n\t</tbody>\n</table>";

        $this->assertEquals($expected, array_to_table($array));
    }

    /**
     * Test case: Convert a balanced 2-dimensional array into a TSV with headers and right-padded first column.
     * This test checks if the function correctly generates a TSV string with headers and right-padded first column.
     */
    public function test_array_nested_to_tsv_with_headers_and_right_padding()
    {
        $array = [
            ['Keyword' => 'apple', 'KD' => '20', 'Volume' => 100],
            ['Keyword' => 'do it yourself', 'KD' => '0', 'Volume' => 1000],
        ];

        $expected = "Keyword       \tKD\tVolume" . PHP_EOL
            . "apple         \t20\t   100" . PHP_EOL
            . "do it yourself\t 0\t  1000" . PHP_EOL;

        $this->assertEquals($expected, array_nested_to_tsv($array, TRUE, TRUE));
    }

    /**
     * Test case: Convert a balanced 2-dimensional array into a TSV with headers and left-padded first column.
     * This test checks if the function correctly generates a TSV string with headers and left-padded first column.
     */
    public function test_array_nested_to_tsv_with_headers_and_left_padding()
    {
        $array = [
            ['Keyword' => 'apple', 'KD' => '20', 'Volume' => 100],
            ['Keyword' => 'do it yourself', 'KD' => '0', 'Volume' => 1000],
        ];

        $expected = "       Keyword\tKD\tVolume" . PHP_EOL
            . "         apple\t20\t   100" . PHP_EOL
            . "do it yourself\t 0\t  1000" . PHP_EOL;

        $this->assertEquals($expected, array_nested_to_tsv($array, TRUE, FALSE));
    }

    /**
     * Test case: Convert a balanced 2-dimensional array into a TSV without headers.
     * This test checks if the function correctly generates a TSV string without headers.
     */
    public function test_array_nested_to_tsv_without_headers()
    {
        $array = [
            ['Keyword' => 'apple', 'KD' => '20', 'Volume' => 100],
            ['Keyword' => 'do it yourself', 'KD' => '0', 'Volume' => 1000],
        ];

        $expected = "apple         \t20\t 100" . PHP_EOL
            . "do it yourself\t 0\t1000" . PHP_EOL;

        $this->assertEquals($expected, array_nested_to_tsv($array, FALSE, TRUE));
    }

    /**
     * Test case: Handle an array with inconsistent keys by throwing an InvalidArgumentException.
     * This test checks if the function correctly throws an exception when the input array has inconsistent keys.
     */
    public function test_array_nested_to_tsv_with_inconsistent_keys()
    {
        $this->expectException(\InvalidArgumentException::class);

        $array = [
            ['Keyword' => 'apple', 'KD' => '20'],
            ['Keyword' => 'do it yourself', 'Volume' => 1000],
        ];

        array_nested_to_tsv($array);
    }

    /**
     * Test case: Convert an empty array into an empty string.
     * This test checks if the function correctly returns an empty string for an empty array.
     */
    public function test_array_nested_to_tsv_with_empty_array()
    {
        $array = [];

        $expected = "";

        $this->assertEquals($expected, array_nested_to_tsv($array, TRUE, TRUE));
    }

    /**
     * Test case: Average calculation with non-strict validation.
     * This test checks if the function correctly calculates the average, ignoring blank strings.
     */
    public function test_array_average_non_strict_validation()
    {
        $array = [10, 20, '', 30, '40', ''];
        // Blank strings are ignored, so (10 + 20 + 30 + 40) / 4 = 25.0
        $expected = 25.0;

        $this->assertEquals($expected, array_average($array, FALSE));
    }

    /**
     * Test case: Average calculation with strict validation.
     * This test checks if the function correctly throws an exception when blank strings are present with strict validation.
     */
    public function test_array_average_strict_validation_with_blank_strings()
    {
        $this->expectException(\InvalidArgumentException::class);

        $array = [10, 20, '', 30, '40', ''];
        // Should throw an exception due to blank strings in strict mode
        array_average($array, TRUE);
    }

    /**
     * Test case: Average calculation with an all-numeric array.
     * This test checks if the function correctly calculates the average for an all-numeric array.
     */
    public function test_array_average_all_numeric()
    {
        $array = [10, 20, 30, 40];
        $expected = 25.0;

        $this->assertEquals($expected, array_average($array, TRUE));
    }

    /**
     * Test case: Average calculation with an empty array.
     * This test checks if the function correctly returns NULL for an empty array.
     */
    public function test_array_average_empty_array()
    {
        $array = [];
        $this->assertNull(array_average($array, TRUE));
    }

    /**
     * Test case: Average calculation with non-numeric elements.
     * This test checks if the function correctly throws an exception when non-numeric elements are present.
     */
    public function test_array_average_with_non_numeric_elements()
    {
        $this->expectException(\InvalidArgumentException::class);

        $array = [10, 20, 'abc', 30];
        // Should throw an exception due to 'abc'
        array_average($array, TRUE);
    }

    /**
     * Test case: Average calculation with a single numeric element.
     * This test checks if the function correctly returns the element itself when the array has only one element.
     */
    public function test_array_average_single_element()
    {
        $array = [42];
        $expected = 42.0;

        $this->assertEquals($expected, array_average($array, TRUE));
    }

    /**
     * Test case: Average calculation with mixed types (numeric strings and integers).
     * This test checks if the function correctly calculates the average for an array with mixed numeric types.
     */
    public function test_array_average_mixed_numeric_types()
    {
        $array = [10, '20', 30.5, '40.5'];
        // (10 + 20 + 30.5 + 40.5) / 4 = 25.25
        $expected = 25.25;

        $this->assertEquals($expected, array_average($array, TRUE));
    }

    /**
     * Test case: Find the median of an array with an odd number of elements.
     * This test checks if the function correctly calculates the median for an odd number of elements.
     */
    public function test_array_median_with_odd_number_of_elements()
    {
        $array = [5, 2, 9];
        $expected = 5.0;

        $this->assertEquals($expected, array_median($array));
    }

    /**
     * Test case: Find the median of an array with an even number of elements.
     * This test checks if the function correctly calculates the median for an even number of elements.
     */
    public function test_array_median_with_even_number_of_elements()
    {
        $array = [1, 2, 3, 4];
        $expected = 2.5;

        $this->assertEquals($expected, array_median($array));
    }

    /**
     * Test case: Strict validation with an array containing non-numeric elements.
     * This test checks if the function throws an exception for non-numeric elements with strict validation.
     */
    public function test_array_median_strict_validation_with_non_numeric_elements()
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('array_median() - The array contains non-numeric elements.');

        $array = [1, 2, 'a', 4];
        array_median($array, TRUE);
    }

    /**
     * Test case: Strict validation with an empty array.
     * This test checks if the function throws an exception when the array is empty and strict validation is enabled.
     */
    public function test_array_median_strict_validation_with_empty_array()
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('array_median() - The array is empty.');

        $array = [];
        array_median($array, TRUE);
    }

    /**
     * Test case: Non-strict validation with non-numeric elements.
     * This test checks if the function correctly filters out non-numeric elements and calculates the median.
     */
    public function test_array_median_non_strict_validation_with_non_numeric_elements()
    {
        $array = [1, 2, 'a', 4];
        $expected = 2.0;

        $this->assertEquals($expected, array_median($array));
    }

    /**
     * Test case: Non-strict validation with all non-numeric elements.
     * This test checks if the function returns NULL when all elements are non-numeric and strict validation is disabled.
     */
    public function test_array_median_non_strict_validation_with_all_non_numeric_elements()
    {
        $array = ['a', 'b', 'c'];
        $expected = NULL;

        $this->assertEquals($expected, array_median($array));
    }

    /**
     * Test case: Non-strict validation with an empty array.
     * This test checks if the function returns NULL when the array is empty and strict validation is disabled.
     */
    public function test_array_median_non_strict_validation_with_empty_array()
    {
        $array = [];
        $expected = NULL;

        $this->assertEquals($expected, array_median($array));
    }

    /**
     * Test case: Non-strict validation with valid numeric values.
     * This test checks if the function correctly calculates the weighted average.
     */
    public function test_array_weighted_average_non_strict_validation_with_valid_numeric_values()
    {
        $values = [1, 2, 3];
        $weights = [0.2, 0.3, 0.5];
        $expected = 2.3;

        $this->assertEquals($expected, array_weighted_average($values, $weights));
    }

    /**
     * Test case: Non-strict validation with non-numeric values causing a mismatch.
     * This test checks if the function correctly filters out non-numeric values, detects the mismatch, and throws an exception.
     */
    public function test_array_weighted_average_non_strict_validation_with_mismatch_after_filtering()
    {
        $values = [1, 'a', 3];
        $weights = [0.2, 0.3, 0.5];

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('array_weighted_average() - The values and weights arrays must have the same number of elements');

        array_weighted_average($values, $weights);
    }

    /**
     * Test case: Non-strict validation with non-numeric values, but the same number of numeric values.
     * This test checks if the function correctly filters out non-numeric values and calculates the weighted average.
     */
    public function test_array_weighted_average_non_strict_validation_with_matching_numeric_values()
    {
        $values = [1, 'a', 3, 'b', 5];
        $weights = [0.2, 'x', 0.5, 0.1];
        // This calculates to 2.75
        $expected = (1 * 0.2 + 3 * 0.5 + 5 * 0.1) / (0.2 + 0.5 + 0.1);

        $this->assertEquals($expected, array_weighted_average($values, $weights));
    }

    /**
     * Test case: Non-strict validation with all non-numeric values.
     * This test checks if the function returns NULL when all values are non-numeric.
     */
    public function test_array_weighted_average_non_strict_validation_with_all_non_numeric_values()
    {
        $values = ['a', 'b', 'c'];
        $weights = [0.2, 0.3, 0.5];

        $this->assertNull(array_weighted_average($values, $weights));
    }

    /**
     * Test case: Strict validation with valid numeric values.
     * This test checks if the function correctly calculates the weighted average.
     */
    public function test_array_weighted_average_strict_validation_with_valid_numeric_values()
    {
        $values = [1, 2, 3];
        $weights = [0.2, 0.3, 0.5];
        $expected = 2.3;

        $this->assertEquals($expected, array_weighted_average($values, $weights, TRUE));
    }

    /**
     * Test case: Strict validation with non-numeric values.
     * This test checks if the function throws an exception when non-numeric values are present.
     */
    public function test_array_weighted_average_strict_validation_with_non_numeric_values()
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('array_weighted_average() - The values array contains non-numeric elements.');

        $values = [1, 'a', 3];
        $weights = [0.2, 0.3, 0.5];

        array_weighted_average($values, $weights, TRUE);
    }

    /**
     * Test case: Strict validation with an empty array.
     * This test checks if the function throws an exception when the array is empty.
     */
    public function test_array_weighted_average_strict_validation_with_empty_array()
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('array_weighted_average() - The array is empty.');

        $values = [];
        $weights = [];

        array_weighted_average($values, $weights, TRUE);
    }

    /**
     * Test case: Non-strict validation with sum of weights zero.
     * This test checks if the function throws an exception when the sum of weights is zero.
     */
    public function test_array_weighted_average_with_zero_total_weight()
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('array_weighted_average() - The sum of weights must be greater than zero');

        $values = [1, 2, 3];
        $weights = [0, 0, 0];

        array_weighted_average($values, $weights);
    }

    /**
     * Test case: Non-strict validation with mismatched lengths.
     * This test checks if the function throws an exception when values and weights arrays have different lengths.
     */
    public function test_array_weighted_average_with_mismatched_lengths()
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('array_weighted_average() - The values and weights arrays must have the same number of elements');

        $values = [1, 2, 3];
        $weights = [0.2, 0.3];

        array_weighted_average($values, $weights);
    }

    /**
     * Test case: Non-strict validation with numeric elements.
     * This test checks if the function correctly calculates the standard deviation.
     */
    public function test_array_stdev_non_strict_validation_with_numeric_elements()
    {
        $array = [1, 2, 3, 4, 5];
        $expected = 1.5811388300842;

        $this->assertEqualsWithDelta($expected, array_stdev($array), 0.000001);
    }

    /**
     * Test case: Non-strict validation with non-numeric elements.
     * This test checks if the function filters out non-numeric elements and calculates the standard deviation.
     */
    public function test_array_stdev_non_strict_validation_with_non_numeric_elements()
    {
        $array = [1, 2, 'a', 4, 5];
        $expected = 1.8257418583506;

        $this->assertEqualsWithDelta($expected, array_stdev($array), 0.000001);
    }

    /**
     * Test case: Non-strict validation with empty array after filtering.
     * This test checks if the function returns NULL when the array is empty after filtering non-numeric elements.
     */
    public function test_array_stdev_non_strict_validation_with_empty_array_after_filtering()
    {
        $array = ['a', 'b', 'c'];

        $this->assertNull(array_stdev($array));
    }

    /**
     * Test case: Strict validation with numeric elements.
     * This test checks if the function correctly calculates the standard deviation when strict validation is enabled.
     */
    public function test_array_stdev_strict_validation_with_numeric_elements()
    {
        $array = [1, 2, 3, 4, 5];
        $expected = 1.5811388300842;

        $this->assertEqualsWithDelta($expected, array_stdev($array, true), 0.000001);
    }

    /**
     * Test case: Strict validation with non-numeric elements.
     * This test checks if the function throws an exception when strict validation is enabled and the array contains non-numeric elements.
     */
    public function test_array_stdev_strict_validation_with_non_numeric_elements()
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('array_stdev() - The array contains non-numeric elements.');

        $array = [1, 2, 'a', 4, 5];
        array_stdev($array, true);
    }

    /**
     * Test case: Strict validation with an empty array.
     * This test checks if the function throws an exception when strict validation is enabled and the array is empty.
     */
    public function test_array_stdev_strict_validation_with_empty_array()
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('array_stdev() - The array is empty.');

        $array = [];
        array_stdev($array, true);
    }

    /**
     * Test case: Non-strict validation with one numeric element.
     * This test checks if the function returns 0 when there is only one numeric element.
     */
    public function test_array_stdev_non_strict_validation_with_one_numeric_element()
    {
        $array = [1];
        $expected = 0;

        $this->assertEquals($expected, array_stdev($array));
    }

    /**
     * Test case: Non-strict validation with one numeric element after filtering.
     * This test checks if the function returns 0 when there is only one numeric element after filtering.
     */
    public function test_array_stdev_non_strict_validation_with_one_numeric_element_after_filtering()
    {
        $array = ['a', 1];
        $expected = 0;

        $this->assertEquals($expected, array_stdev($array));
    }

    /**
     * Test case: Zipping two arrays of equal length.
     * This test checks if the function correctly zips two arrays with the same number of elements.
     */
    public function test_array_map_zip_equal_length()
    {
        $array1 = [1, 2, 3];
        $array2 = ['a', 'b', 'c'];

        $expected = [
            [1, 'a'],
            [2, 'b'],
            [3, 'c'],
        ];

        $this->assertEquals($expected, array_map_zip($array1, $array2));
    }

    /**
     * Test case: Zipping arrays of different lengths.
     * This test checks if the function correctly zips arrays of varying lengths, filling shorter arrays with null.
     */
    public function test_array_map_zip_different_lengths()
    {
        $array1 = [1, 2];
        $array2 = ['a', 'b', 'c'];

        $expected = [
            [1, 'a'],
            [2, 'b'],
            [null, 'c'],
        ];

        $this->assertEquals($expected, array_map_zip($array1, $array2));
    }

    /**
     * Test case: Zipping more than two arrays.
     * This test checks if the function correctly zips three arrays together.
     */
    public function test_array_map_zip_multiple_arrays()
    {
        $array1 = [1, 2, 3];
        $array2 = ['a', 'b', 'c'];
        $array3 = [true, false, null];

        $expected = [
            [1, 'a', true],
            [2, 'b', false],
            [3, 'c', null],
        ];

        $this->assertEquals($expected, array_map_zip($array1, $array2, $array3));
    }

    /**
     * Test case: Zipping with an empty array.
     * This test checks if the function returns an empty array when one of the input arrays is empty.
     */
    public function test_array_map_zip_with_empty_array()
    {
        $array1 = [];
        $array2 = ['a', 'b', 'c'];

        $expected = [];

        $this->assertEquals($expected, array_map_zip($array1, $array2));
    }

    /**
     * Test case: Zipping with no arrays.
     * This test checks if the function returns an empty array when no arrays are provided.
     */
    public function test_array_map_zip_no_arrays()
    {
        $expected = [];

        $this->assertEquals($expected, array_map_zip());
    }

    /**
     * Test case: Zipping two arrays of equal length.
     * This test checks if the function correctly zips two arrays of the same length.
     */
    public function test_python_zip_equal_length_arrays()
    {
        $array1 = [1, 2, 3];
        $array2 = ['one', 'two', 'three'];
        $expected = [
            [1, 'one'],
            [2, 'two'],
            [3, 'three'],
        ];

        $this->assertEquals($expected, python_zip($array1, $array2));
    }

    /**
     * Test case: Zipping arrays of different lengths.
     * This test checks if the function correctly zips arrays of different lengths, truncating to the shortest array.
     */
    public function test_python_zip_different_length_arrays()
    {
        $array1 = [1, 2, 3, 4];
        $array2 = ['one', 'two'];
        $expected = [
            [1, 'one'],
            [2, 'two'],
        ];

        $this->assertEquals($expected, python_zip($array1, $array2));
    }

    /**
     * Test case: Zipping with an empty array.
     * This test checks if the function returns an empty array when one of the input arrays is empty.
     */
    public function test_python_zip_with_empty_array()
    {
        $array1 = [];
        $array2 = ['one', 'two', 'three'];
        $expected = [];

        $this->assertEquals($expected, python_zip($array1, $array2));
    }

    /**
     * Test case: Zipping a single array.
     * This test checks if the function correctly wraps each element in a tuple when only one array is provided.
     */
    public function test_python_zip_single_array()
    {
        $array1 = [1, 2, 3];
        $expected = [
            [1],
            [2],
            [3],
        ];

        $this->assertEquals($expected, python_zip($array1));
    }

    /**
     * Test case: Zipping multiple arrays of different lengths.
     * This test checks if the function correctly zips multiple arrays, truncating to the shortest array.
     */
    public function test_python_zip_multiple_arrays_different_lengths()
    {
        $array1 = [1, 2, 3];
        $array2 = ['one', 'two'];
        $array3 = ['I', 'II', 'III', 'IV'];
        $expected = [
            [1, 'one', 'I'],
            [2, 'two', 'II'],
        ];

        $this->assertEquals($expected, python_zip($array1, $array2, $array3));
    }

    /**
     * Test case: Zipping two arrays of equal length.
     * This test checks if the function correctly produces tuples from arrays of equal length.
     */
    public function test_array_zip_equal_length()
    {
        $array1 = [1, 2, 3];
        $array2 = ['one', 'two', 'three'];
        $expected = [
            [1, 'one'],
            [2, 'two'],
            [3, 'three'],
        ];

        $this->assertEquals($expected, array_zip($array1, $array2));
    }

    /**
     * Test case: Zipping two arrays of different lengths.
     * This test checks if the function correctly handles arrays of different lengths.
     */
    public function test_array_zip_different_lengths()
    {
        $array1 = [1, 2, 3, 4];
        $array2 = ['one', 'two'];
        $expected = [
            [1, 'one'],
            [2, 'two'],
            [3],
            [4],
        ];

        $this->assertEquals($expected, array_zip($array1, $array2));
    }

    /**
     * Test case: Zipping multiple arrays of varying lengths.
     * This test checks if the function correctly produces tuples from multiple arrays of varying lengths.
     */
    public function test_array_zip_multiple_arrays()
    {
        $array1 = [1, 2, 3];
        $array2 = ['one', 'two'];
        $array3 = ['ONE', 'TWO', 'THREE', 'FOUR'];
        $expected = [
            [1, 'one', 'ONE'],
            [2, 'two', 'TWO'],
            [3, 'THREE'],
            ['FOUR'],
        ];

        $this->assertEquals($expected, array_zip($array1, $array2, $array3));
    }

    /**
     * Test case: Zipping with an empty array.
     * This test checks if the function correctly handles an empty array.
     */
    public function test_array_zip_with_empty_array()
    {
        $array1 = [];
        $array2 = ['one', 'two', 'three'];
        $expected = [
            ['one'],
            ['two'],
            ['three'],
        ];

        $this->assertEquals($expected, array_zip($array1, $array2));
    }

    /**
     * Test case: Zipping with no arrays.
     * This test checks if the function returns an empty array when no arrays are provided.
     */
    public function test_array_zip_no_arrays()
    {
        $expected = [];

        $this->assertEquals($expected, array_zip());
    }

    /**
     * Test case: Zipping with non-array values should throw an exception.
     * This test checks if the function throws an exception when non-array values are passed.
     */
    public function test_array_zip_with_non_array_values()
    {
        $this->expectException(\TypeError::class);

        // @phpstan-ignore-next-line
        array_zip([1, 2, 3], 'not an array');
    }

    /**
     * Test case: Averaging across arrays with matching keys and no precision.
     * This test checks if the function correctly calculates the averages without rounding.
     */
    public function test_average_across_arrays_no_precision()
    {
        $arrays = [
            ['a' => 1, 'b' => 2, 'c' => 3],
            ['a' => 4, 'b' => 5, 'c' => 6],
            ['a' => 7, 'b' => 8, 'c' => 9],
        ];

        $expected = [4, 5, 6];

        $this->assertEquals($expected, average_across_arrays($arrays));
    }

    /**
     * Test case: Averaging across arrays with matching keys and precision.
     * This test checks if the function correctly calculates and rounds the averages.
     */
    public function test_average_across_arrays_with_precision()
    {
        $arrays = [
            ['a' => 1.1234, 'b' => 2.5678, 'c' => 3.9101],
            ['a' => 4.2345, 'b' => 5.6789, 'c' => 6.7890],
            ['a' => 7.3456, 'b' => 8.7890, 'c' => 9.6789],
        ];

        $expected = [4.23, 5.68, 6.79];

        $this->assertEquals($expected, average_across_arrays($arrays, 2));
    }

    /**
     * Test case: Averaging across arrays with non-matching keys.
     * This test checks if the function throws an exception when the arrays have non-matching keys.
     */
    public function test_average_across_arrays_non_matching_keys()
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('average_across_arrays() - All values of array must also be arrays, and they all must have the same keys.');

        // 'd' instead of 'c' for second array
        $arrays = [
            ['a' => 1, 'b' => 2, 'c' => 3],
            ['a' => 4, 'b' => 5, 'd' => 6],
        ];

        average_across_arrays($arrays);
    }

    /**
     * Test case: Averaging across empty arrays.
     * This test checks if the function correctly handles empty input arrays.
     */
    public function test_average_across_arrays_empty_arrays()
    {
        $arrays = [
            ['a' => 0, 'b' => 0, 'c' => 0],
            ['a' => 0, 'b' => 0, 'c' => 0],
        ];

        $expected = [0, 0, 0];

        $this->assertEquals($expected, average_across_arrays($arrays));
    }

    /**
     * Test case: Averaging across arrays with null values.
     * This test checks if the function correctly handles null values in the arrays.
     */
    public function test_average_across_arrays_with_null_values()
    {
        $arrays = [
            ['a' => 1, 'b' => null, 'c' => 3],
            ['a' => 4, 'b' => null, 'c' => 6],
            ['a' => 7, 'b' => 8, 'c' => 9],
        ];

        $expected = [4, 8, 6];

        $this->assertEquals($expected, average_across_arrays($arrays));
    }

    /**
     * Test case: Single string input.
     * This test checks if the function correctly adds slashes to a simple string.
     */
    public function test_addslashes_recursive_single_string()
    {
        $input = "O'Reilly";
        $expected = "O\'Reilly";

        $this->assertEquals($expected, addslashes_recursive($input));
    }

    /**
     * Test case: Array of strings.
     * This test checks if the function correctly adds slashes to each string in an array.
     */
    public function test_addslashes_recursive_array_of_strings()
    {
        $input = ["O'Reilly", "Hello \"world\"", "It\\'s cool"];
        $expected = ["O\'Reilly", "Hello \\\"world\\\"", "It\\\\\'s cool"];

        $this->assertEquals($expected, addslashes_recursive($input));
    }

    /**
     * Test case: Nested arrays of strings.
     * This test checks if the function correctly adds slashes to strings in nested arrays.
     */
    public function test_addslashes_recursive_nested_array()
    {
        $input = [
            "O'Reilly",
            ["Hello \"world\"", "It\\'s cool"],
            "Nested 'array' with \"quotes\""
        ];
        $expected = [
            "O\'Reilly",
            ["Hello \\\"world\\\"", "It\\\\\'s cool"],
            "Nested \'array\' with \\\"quotes\\\""
        ];

        $this->assertEquals($expected, addslashes_recursive($input));
    }

    /**
     * Test case: Recursively adding slashes to a multidimensional array.
     * This test checks if the function correctly adds slashes to strings within a multidimensional array.
     */
    public function test_addslashes_recursive_with_multidimensional_array()
    {
        $input = [
            "level1" => [
                "O'Reilly",
                "level2" => ["It's \"great\""]
            ]
        ];
        $expected = [
            "level1" => [
                "O\'Reilly",
                "level2" => ["It\'s \\\"great\\\""]
            ]
        ];

        $this->assertEquals($expected, addslashes_recursive($input));
    }

    /**
     * Test case: Non-string input.
     * This test checks if the function correctly handles non-string input without modification.
     */
    public function test_addslashes_recursive_non_string_input()
    {
        $input = [123, 45.67, true, null];
        $expected = [123, 45.67, true, null];

        $this->assertEquals($expected, addslashes_recursive($input));
    }

    /**
     * Test case: Empty array input.
     * This test checks if the function returns an empty array when given an empty array.
     */
    public function test_addslashes_recursive_empty_array()
    {
        $input = [];
        $expected = [];

        $this->assertEquals($expected, addslashes_recursive($input));
    }

    /**
     * Test case: Mixed data types in array.
     * This test checks if the function correctly adds slashes to strings while leaving other data types unchanged.
     */
    public function test_addslashes_recursive_mixed_data_types()
    {
        $input = [
            "O'Reilly",
            123,
            ["Hello \"world\"", true],
            null,
            "Nested 'array' with \"quotes\""
        ];
        $expected = [
            "O\'Reilly",
            123,
            ["Hello \\\"world\\\"", true],
            null,
            "Nested \'array\' with \\\"quotes\\\""
        ];

        $this->assertEquals($expected, addslashes_recursive($input));
    }

    /**
     * Test case: Simple array with no nested arrays.
     * This test checks if the function correctly removes duplicate values from a flat array.
     */
    public function test_array_unique_recursive_simple_array()
    {
        $array = [1, 2, 2, 3, 4, 4];
        $expected = [1, 2, 3, 4];

        $this->assertEquals($expected, array_values(array_unique_recursive($array)));
    }

    /**
     * Test case: Nested arrays with duplicate values.
     * This test checks if the function correctly removes duplicate values from nested arrays.
     */
    public function test_array_unique_recursive_nested_array()
    {
        $array = [
            'a' => [1, 2, 2, 3],
            'b' => [3, 4, 4, 5],
            'c' => [1, 2, 2, 3],
        ];
        $expected = [
            'a' => [1, 2, 3],
            'b' => [3, 4, 5],
            'c' => [1, 2, 3],
        ];

        $this->assertEquals($expected, array_unique_recursive($array));
    }

    /**
     * Test case: Nested arrays with preservation of keys.
     * This test checks if the function correctly removes duplicate values while preserving keys.
     */
    public function test_array_unique_recursive_nested_array_preserve_keys()
    {
        $array = [
            'a' => [1, 2, 2, 3],
            'b' => [3, 4, 4, 5],
            'c' => [1, 2, 2, 3],
        ];
        $expected = [
            'a' => [0 => 1, 1 => 2, 3 => 3],
            'b' => [0 => 3, 1 => 4, 3 => 5],
            'c' => [0 => 1, 1 => 2, 3 => 3],
        ];

        $this->assertEquals($expected, array_unique_recursive($array, TRUE));
    }

    /**
     * Test case: Complex nested arrays with different levels.
     * This test checks if the function correctly removes duplicate values in a deeply nested structure.
     */
    public function test_array_unique_recursive_complex_nested_array()
    {
        $array = [
            'a' => [1, 2, [2, 3, 3], 3],
            'b' => [3, 4, [4, 5, 5], 5],
            'c' => [1, 2, [2, 3, 3], 3],
        ];
        $expected = [
            'a' => [1, 2, [2, 3], 3],
            'b' => [3, 4, [4, 5], 5],
            'c' => [1, 2, [2, 3], 3],
        ];

        $this->assertEquals($expected, array_unique_recursive($array));
    }

    /**
     * Test case: Array with numeric and string keys.
     * This test checks if the function correctly reorders keys when $preserve_keys is FALSE.
     */
    public function test_array_unique_recursive_reorder_keys()
    {
        $array = [
            1 => [1, 2, 2, 3],
            'key' => [3, 4, 4, 5],
            3 => [1, 2, 2, 3],
        ];
        $expected = [
            0 => [1, 2, 3],
            'key' => [3, 4, 5],
            1 => [1, 2, 3],
        ];

        $this->assertEquals($expected, array_unique_recursive($array));
    }

    /**
     * Test case: Empty array.
     * This test checks if the function returns an empty array when provided with an empty input.
     */
    public function test_array_unique_recursive_empty_array()
    {
        $array = [];
        $expected = [];

        $this->assertEquals($expected, array_unique_recursive($array));
    }

    /**
     * Test case: Generate an array with valid input values.
     * This test checks if the function generates an array of the specified length with values within the specified range.
     */
    public function test_rand_array_with_valid_input()
    {
        $count = 5;
        $min = 1;
        $max = 10;
        $result = rand_array($count, $min, $max);

        $this->assertCount($count, $result);

        foreach ($result as $value) {
            $this->assertGreaterThanOrEqual($min, $value);
            $this->assertLessThanOrEqual($max, $value);
        }
    }

    /**
     * Test case: Generate an empty array with count = 0.
     * This test checks if the function returns an empty array when count is 0.
     */
    public function test_rand_array_with_zero_count()
    {
        $count = 0;
        $min = 1;
        $max = 10;
        $result = rand_array($count, $min, $max);

        $this->assertEmpty($result);
    }

    /**
     * Test case: Generate an array with negative count value should throw an exception.
     * This test checks if the function throws an InvalidArgumentException when count is negative.
     */
    public function test_rand_array_with_negative_count()
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('The count must be a positive integer.');

        rand_array(-5, 1, 10);
    }

    /**
     * Test case: Generate an array with min value greater than max value should throw an exception.
     * This test checks if the function throws an InvalidArgumentException when min is greater than max.
     */
    public function test_rand_array_with_min_greater_than_max()
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('The minimum value cannot be greater than the maximum value.');

        rand_array(5, 10, 1);
    }

    /**
     * Test case: Generate an array with min and max being equal.
     * This test checks if the function returns an array with identical values when min and max are equal.
     */
    public function test_rand_array_with_min_equal_to_max()
    {
        $count = 5;
        $min = 7;
        $max = 7;
        $result = rand_array($count, $min, $max);

        $this->assertCount($count, $result);

        foreach ($result as $value) {
            $this->assertEquals($min, $value);
        }
    }

    /**
     * Test case: Generating random elements with a valid array and length.
     * This test checks if the function returns an array of the correct length with elements from the input array.
     */
    public function test_array_rand_duplicates_valid_input()
    {
        $array = ["Apple", "Bananas", "Grapes", "Oranges"];
        $len = 3;

        $result = array_rand_duplicates($array, $len);

        $this->assertCount($len, $result);
        // Verify that the elements in the result array are also in the input array.
        foreach ($result as $element) {
            $this->assertContains($element, $array);
        }
    }

    /**
     * Test case: Empty input array.
     * This test checks if the function throws an exception when an empty array is provided.
     */
    public function test_array_rand_duplicates_empty_array()
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('The array cannot be empty.');

        array_rand_duplicates([], 3);
    }

    /**
     * Test case: Negative length.
     * This test checks if the function throws an exception when a negative length is provided.
     */
    public function test_array_rand_duplicates_negative_length()
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Length must be a non-negative integer.');

        array_rand_duplicates(["Apple", "Bananas", "Grapes"], -1);
    }

    /**
     * Test case: Length of 0.
     * This test checks if the function returns an empty array when the length is 0.
     */
    public function test_array_rand_duplicates_length_zero()
    {
        $array = ["Apple", "Bananas", "Grapes"];
        $len = 0;

        $result = array_rand_duplicates($array, $len);

        $this->assertEmpty($result);
    }

    /**
     * Test case: Length greater than the size of the array.
     * This test checks if the function returns an array of the correct length even when it's greater than the input array size.
     */
    public function test_array_rand_duplicates_length_greater_than_array_size()
    {
        $array = ["Apple", "Bananas", "Grapes"];
        $len = 5;

        $result = array_rand_duplicates($array, $len);

        $this->assertCount($len, $result);
        foreach ($result as $element) {
            $this->assertContains($element, $array);
        }
    }

    /**
     * Test that the function returns an array with the correct length.
     */
    public function test_shuffle_slice_returns_correct_length()
    {
        $array = range(1, 10);
        $len = 5;

        $result = shuffle_slice($array, $len);

        $this->assertCount($len, $result);
    }

    /**
     * Test that the function returns the entire array if $len exceeds array length.
     */
    public function test_shuffle_slice_returns_full_array_when_len_exceeds_length()
    {
        $array = [1, 2, 3];
        $len = 10;

        $result = shuffle_slice($array, $len);

        $this->assertCount(count($array), $result);
        $this->assertEqualsCanonicalizing($array, $result);
    }

    /**
     * Test that the function handles an empty array.
     */
    public function test_shuffle_slice_handles_empty_array()
    {
        $array = [];
        $len = 5;

        $result = shuffle_slice($array, $len);

        $this->assertEmpty($result);
    }

    /**
     * Test that the function returns an empty array when $len is zero.
     */
    public function test_shuffle_slice_returns_empty_array_when_len_is_zero()
    {
        $array = [1, 2, 3, 4, 5];
        $len = 0;

        $result = shuffle_slice($array, $len);

        $this->assertEmpty($result);
    }

    /**
     * Test that the function works with non-integer values in the array.
     */
    public function test_shuffle_slice_handles_non_integer_values()
    {
        $array = ['a', 'b', 'c', 'd', 'e'];
        $len = 3;

        $result = shuffle_slice($array, $len);

        $this->assertCount($len, $result);
        $this->assertContainsOnly('string', $result);
    }

    /**
     * Test that the function returns a key from the array.
     */
    public function test_array_random_key_returns_valid_key()
    {
        $array = ['a' => 1, 'b' => 2, 'c' => 3];

        $key = array_random_key($array);

        $this->assertArrayHasKey($key, $array);
    }

    /**
     * Test that the function returns different keys for large arrays.
     */
    public function test_array_random_key_returns_different_keys()
    {
        // Large array with identical values
        $array = array_fill(0, 100, 1);

        $keys = [];
        for ($i = 0; $i < 10; $i++) {
            $keys[] = array_random_key($array);
        }

        $uniqueKeys = array_unique($keys);

        $this->assertGreaterThan(1, count($uniqueKeys));
    }

    /**
     * Test that the function throws an exception when given an empty array.
     */
    public function test_array_random_key_throws_exception_on_empty_array()
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('The array cannot be empty.');

        $array = [];
        array_random_key($array);
    }

    /**
     * Test that the function works with an array of integers.
     */
    public function test_array_random_key_with_integer_keys()
    {
        $array = [10 => 'a', 20 => 'b', 30 => 'c'];

        $key = array_random_key($array);

        $this->assertArrayHasKey($key, $array);
        $this->assertIsInt($key);
    }

    /**
     * Test that the function works with an array of string keys.
     */
    public function test_array_random_key_with_string_keys()
    {
        $array = ['first' => 1, 'second' => 2, 'third' => 3];

        $key = array_random_key($array);

        $this->assertArrayHasKey($key, $array);
        $this->assertIsString($key);
    }

    /**
     * Test that the function returns an array of the same length as the input when length is not specified.
     */
    public function test_shuffle_secure_returns_full_array()
    {
        $array = [1, 2, 3, 4, 5];

        $result = shuffle_secure($array);

        $this->assertCount(count($array), $result);
    }

    /**
     * Test that the function returns an array with the specified length when a valid length is provided.
     */
    public function test_shuffle_secure_returns_sliced_array()
    {
        $array = [1, 2, 3, 4, 5];
        $length = 3;

        $result = shuffle_secure($array, $length);

        $this->assertCount($length, $result);
    }

    /**
     * Test that the function returns an empty array when given an empty array.
     */
    public function test_shuffle_secure_handles_empty_array()
    {
        $array = [];

        $result = shuffle_secure($array);

        $this->assertEmpty($result);
    }

    /**
     * Test that the function ignores the length parameter when it is FALSE.
     */
    public function test_shuffle_secure_ignores_false_length()
    {
        $array = [1, 2, 3, 4, 5];
        $length = FALSE;

        $result = shuffle_secure($array, $length);

        $this->assertCount(count($array), $result);
    }

    /**
     * Test that the function works with associative arrays.
     */
    public function test_shuffle_secure_with_associative_array()
    {
        $array = ['a' => 1, 'b' => 2, 'c' => 3];

        $result = shuffle_secure($array);

        $this->assertCount(count($array), $result);
        // Ensure all elements are present
        $this->assertEmpty(array_diff($array, $result));
    }

    /**
     * Test that the function returns an empty array when given an empty array.
     */
    public function test_array_rand_to_array_handles_empty_array()
    {
        $array = [];

        $result = array_rand_to_array($array);

        $this->assertEmpty($result);
    }

    /**
     * Test that the function returns an array with one element when $num is 1.
     */
    public function test_array_rand_to_array_returns_one_element()
    {
        $array = ['Apple', 'Bananas', 'Grapes'];

        $result = array_rand_to_array($array, 1);

        $this->assertCount(1, $result);
        $this->assertContains($result[0], $array);
    }

    /**
     * Test that the function returns an array with the specified number of elements.
     */
    public function test_array_rand_to_array_returns_specified_number_of_elements()
    {
        $array = ['Apple', 'Bananas', 'Grapes', 'Oranges', 'Pears'];

        $result = array_rand_to_array($array, 3);

        $this->assertCount(3, $result);
        foreach ($result as $value) {
            $this->assertContains($value, $array);
        }
    }

    /**
     * Test that the function returns all elements when $num exceeds array size.
     */
    public function test_array_rand_to_array_returns_all_elements_when_num_exceeds_size()
    {
        $array = ['Apple', 'Bananas', 'Grapes'];

        $result = array_rand_to_array($array, 10);

        $this->assertCount(3, $result);
        foreach ($result as $value) {
            $this->assertContains($value, $array);
        }
    }

    /**
     * Test that the function preserves the order of elements in the original array.
     */
    public function test_array_rand_to_array_preserves_order()
    {
        $array = ['Apple', 'Bananas', 'Grapes', 'Oranges', 'Pears'];

        $result = array_rand_to_array($array, 3);

        $this->assertSame(
            array_values(array_intersect($array, $result)),
            $result
        );
    }

    /**
     * Test that the function returns an empty array when given an empty array.
     */
    public function test_array_random_elements_handles_empty_array()
    {
        $array = [];

        $result = array_random_elements($array, 3);

        $this->assertEmpty($result);
    }

    /**
     * Test that the function returns a single random element when $num is 1.
     */
    public function test_array_random_elements_returns_single_element()
    {
        $array = ['Apple', 'Bananas', 'Grapes'];

        $result = array_random_elements($array, 1);

        $this->assertCount(1, $result);
        $this->assertContains($result[0], $array);
    }

    /**
     * Test that the function returns an array with the specified number of elements.
     */
    public function test_array_random_elements_returns_specified_number_of_elements()
    {
        $array = ['Apple', 'Bananas', 'Grapes', 'Oranges', 'Pears'];

        $result = array_random_elements($array, 3);

        $this->assertCount(3, $result);
        foreach ($result as $value) {
            $this->assertContains($value, $array);
        }
    }

    /**
     * Test that the function can return duplicate elements when $num is greater than the array size.
     */
    public function test_array_random_elements_can_return_duplicates()
    {
        $array = ['Apple', 'Bananas', 'Grapes'];

        $result = array_random_elements($array, 5);

        $this->assertCount(5, $result);

        // Check if duplicates are possible
        $uniqueResult = array_unique($result);
        $this->assertNotCount(5, $uniqueResult);
    }

    /**
     * Test that the function returns a single random element from a non-empty array.
     */
    public function test_array_random_element_returns_single_element()
    {
        $array = ['Apple', 'Bananas', 'Grapes'];

        $result = array_random_element($array);

        $this->assertContains($result, $array);
    }

    /**
     * Test that the function returns a consistent type for a single element.
     */
    public function test_array_random_element_returns_correct_type()
    {
        $array = ['Apple', 'Bananas', 'Grapes'];

        $result = array_random_element($array);

        $this->assertIsString($result);
    }

    /**
     * Test that the function works with an array of integers.
     */
    public function test_array_random_element_with_integers()
    {
        $array = [1, 2, 3, 4, 5];

        $result = array_random_element($array);

        $this->assertContains($result, $array);
        $this->assertIsInt($result);
    }

    /**
     * Test that the function works with an array of mixed types.
     */
    public function test_array_random_element_with_mixed_types()
    {
        $array = [1, 'Bananas', 3.14, true];

        $result = array_random_element($array);

        $this->assertContains($result, $array);
    }

    /**
     * Test that the function throws an exception when the input arrays have different lengths.
     */
    public function test_array_multisort_by_array_throws_exception_on_mismatched_array_lengths()
    {
        $array1 = [
            'item1' => ['value' => 10],
            'item2' => ['value' => 20],
        ];

        // Longer than array1
        $array2 = [2, 3, 1];

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage("array_multisort_by_array() - count(\$array1) != count(\$array2). 2 != 3");

        array_multisort_by_array($array1, $array2);
    }

    /**
     * Test that the function handles an empty array correctly.
     */
    public function test_array_multisort_by_array_handles_empty_array()
    {
        $array1 = [];
        $array2 = [];

        array_multisort_by_array($array1, $array2);

        $this->assertSame([], $array1);
    }

    /**
     * Test that the function sorts a multidimensional array based on the values of another array in ascending order.
     */
    public function test_array_multisort_by_array_sort_asc_regular()
    {
        $array1 = [
            'item1' => ['value' => 10],
            'item2' => ['value' => 20],
            'item3' => ['value' => 15],
        ];

        // Sort order should be item3, item1, item2
        $array2 = [2, 3, 1];

        array_multisort_by_array($array1, $array2, SORT_ASC, SORT_REGULAR);

        $expected = [
            'item3' => ['value' => 15],
            'item1' => ['value' => 10],
            'item2' => ['value' => 20],
        ];

        $this->assertSame($expected, $array1);
    }

    /**
     * Test that the function sorts a multidimensional array based on the values of another array in descending order.
     */
    public function test_array_multisort_by_array_sort_desc_regular()
    {
        $array1 = [
            'item1' => ['value' => 10],
            'item2' => ['value' => 20],
            'item3' => ['value' => 15],
        ];

        // Sort order should be item2, item1, item3
        $array2 = [2, 3, 1];

        array_multisort_by_array($array1, $array2, SORT_DESC, SORT_REGULAR);

        $expected = [
            'item2' => ['value' => 20],
            'item1' => ['value' => 10],
            'item3' => ['value' => 15],
        ];

        $this->assertSame($expected, $array1);
    }

    /**
     * Test that the function sorts correctly with SORT_ASC and SORT_STRING.
     */
    public function test_array_multisort_by_array_sort_asc_string()
    {
        $array1 = [
            'item1' => ['value' => 10],
            'item2' => ['value' => 20],
            'item3' => ['value' => 15],
        ];

        // Sort order should be item3, item1, item2 because 'a' < 'b' < 'c'
        $array2 = ['bananas', 'carrots', 'apples'];

        array_multisort_by_array($array1, $array2, SORT_ASC, SORT_STRING);

        $expected = [
            'item3' => ['value' => 15],
            'item1' => ['value' => 10],
            'item2' => ['value' => 20],
        ];

        $this->assertSame($expected, $array1);
    }

    /**
     * Test that the function sorts correctly with SORT_DESC and SORT_STRING.
     */
    public function test_array_multisort_by_array_sort_desc_string()
    {
        $array1 = [
            'item1' => ['value' => 10],
            'item2' => ['value' => 20],
            'item3' => ['value' => 15],
        ];

        // Sort order should be item2, item1, item3 because 'a' < 'b' < 'c'
        $array2 = ['bananas', 'carrots', 'apples'];

        array_multisort_by_array($array1, $array2, SORT_DESC, SORT_STRING);

        $expected = [
            'item2' => ['value' => 20],
            'item1' => ['value' => 10],
            'item3' => ['value' => 15],
        ];

        $this->assertSame($expected, $array1);
    }

    /**
     * Test that the function sorts correctly with SORT_ASC and SORT_STRING.
     * Uses case insensitive sorting.
     */
    public function test_array_multisort_by_array_sort_asc_string_caps()
    {
        $array1 = [
            'item1' => ['value' => 10],
            'item2' => ['value' => 20],
            'item3' => ['value' => 15],
        ];

        // Sort order should be item2, item3, item1 because 'C' < 'a' < 'b'
        $array2 = ['bananas', 'Carrots', 'apples'];

        array_multisort_by_array($array1, $array2, SORT_ASC, SORT_STRING);

        $expected = [
            'item2' => ['value' => 20],
            'item3' => ['value' => 15],
            'item1' => ['value' => 10],
        ];

        $this->assertSame($expected, $array1);
    }

    /**
     * Test that the function sorts correctly with custom sort flags.
     * Uses case insensitive sorting.
     */
    public function test_array_multisort_by_array_sort_desc_string_caps()
    {
        $array1 = [
            'item1' => ['value' => 10],
            'item2' => ['value' => 20],
            'item3' => ['value' => 15],
        ];

        // Sort order should be item1, item3, item2 because 'C' < 'a' < 'b'
        $array2 = ['bananas', 'Carrots', 'apples'];

        array_multisort_by_array($array1, $array2, SORT_DESC, SORT_STRING);

        $expected = [
            'item1' => ['value' => 10],
            'item3' => ['value' => 15],
            'item2' => ['value' => 20],
        ];

        $this->assertSame($expected, $array1);
    }

    /**
     * Test sorting with SORT_ASC and SORT_NUMERIC.
     */
    public function test_array_multisort_by_array_sort_asc_numeric()
    {
        $array1 = [
            'item1' => ['value' => 10],
            'item2' => ['value' => 20],
            'item3' => ['value' => 15],
        ];

        // Sorting order should be item2, item3, item1
        $array2 = [200, 100, 150];

        array_multisort_by_array($array1, $array2, SORT_ASC, SORT_NUMERIC);

        $expected = [
            'item2' => ['value' => 20],
            'item3' => ['value' => 15],
            'item1' => ['value' => 10],
        ];

        $this->assertSame($expected, $array1);
    }

    /**
     * Test sorting with SORT_DESC and SORT_NUMERIC.
     */
    public function test_array_multisort_by_array_sort_desc_numeric()
    {
        $array1 = [
            'item1' => ['value' => 10],
            'item2' => ['value' => 20],
            'item3' => ['value' => 15],
        ];

        // Sorting order should be item1, item3, item2
        $array2 = [3, 1.5, 2];

        array_multisort_by_array($array1, $array2, SORT_DESC, SORT_NUMERIC);

        $expected = [
            'item1' => ['value' => 10],
            'item3' => ['value' => 15],
            'item2' => ['value' => 20],
        ];

        $this->assertSame($expected, $array1);
    }

    /**
     * Test sorting with SORT_ASC and SORT_NATURAL.
     * By SORT_REGULAR, item1 < item10 < item2
     * By SORT_NUMERIC, item1 < item10 < item2
     * By SORT_NATURAL, item1 < item2 < item10
     */
    public function test_array_multisort_by_array_sort_asc_natural()
    {
        $array1 = [
            'item1' => ['value' => 10],
            'item2' => ['value' => 20],
            'item3' => ['value' => 15],
        ];

        // Sorting order should be item3, item2, item1
        $array2 = ['item10', 'item2', 'item1'];

        array_multisort_by_array($array1, $array2, SORT_ASC, SORT_NATURAL);

        $expected = [
            'item3' => ['value' => 15],
            'item2' => ['value' => 20],
            'item1' => ['value' => 10],
        ];

        $this->assertSame($expected, $array1);
    }

    /**
     * Test sorting with SORT_DESC and SORT_NATURAL.
     */
    public function test_array_multisort_by_array_sort_desc_natural()
    {
        $array1 = [
            'item1' => ['value' => 10],
            'item2' => ['value' => 20],
            'item3' => ['value' => 15],
        ];

        // Sorting order should be item1, item2, item3
        $array2 = ['item10', 'item2', 'item1'];

        array_multisort_by_array($array1, $array2, SORT_DESC, SORT_NATURAL);

        $expected = [
            'item1' => ['value' => 10],
            'item2' => ['value' => 20],
            'item3' => ['value' => 15],
        ];

        $this->assertSame($expected, $array1);
    }

    /**
     * Test sorting by a non-existent sub-array field, expecting an exception.
     */
    public function test_array_multisort_nested_invalid_field()
    {
        $this->expectException(\InvalidArgumentException::class);

        $array = [
            'item1' => ['value' => 10],
            'item2' => ['value' => 20],
            'item3' => ['value' => 15],
        ];

        array_multisort_nested($array, 'invalid_field');
    }

    /**
     * Test sorting when no sub-array field is provided, sorting by array values directly.
     */
    public function test_array_multisort_nested_no_field()
    {
        $array = [
            'item1' => 10,
            'item2' => 20,
            'item3' => 15,
        ];

        array_multisort_nested($array);

        $expected = [
            'item1' => 10,
            'item3' => 15,
            'item2' => 20,
        ];

        $this->assertSame($expected, $array);
    }

    /**
     * Test sorting with SORT_ASC and SORT_REGULAR.
     */
    public function test_array_multisort_nested_asc_regular()
    {
        $array = [
            'item1' => ['value' => 'apple'],
            'item2' => ['value' => 'banana'],
            'item3' => ['value' => 'cherry'],
        ];

        array_multisort_nested($array, 'value', SORT_ASC, SORT_REGULAR);

        $expected = [
            'item1' => ['value' => 'apple'],
            'item2' => ['value' => 'banana'],
            'item3' => ['value' => 'cherry'],
        ];

        $this->assertSame($expected, $array);
    }

    /**
     * Test sorting with SORT_DESC and SORT_REGULAR.
     */
    public function test_array_multisort_nested_desc_regular()
    {
        $array = [
            'item1' => ['value' => 'apple'],
            'item2' => ['value' => 'banana'],
            'item3' => ['value' => 'cherry'],
        ];

        array_multisort_nested($array, 'value', SORT_DESC, SORT_REGULAR);

        $expected = [
            'item3' => ['value' => 'cherry'],
            'item2' => ['value' => 'banana'],
            'item1' => ['value' => 'apple'],
        ];

        $this->assertSame($expected, $array);
    }

    /**
     * Test sorting with SORT_ASC and SORT_NUMERIC.
     */
    public function test_array_multisort_nested_asc_numeric()
    {
        $array = [
            'item1' => ['value' => 10],
            'item2' => ['value' => 20],
            'item3' => ['value' => 15],
        ];

        array_multisort_nested($array, 'value', SORT_ASC, SORT_NUMERIC);

        $expected = [
            'item1' => ['value' => 10],
            'item3' => ['value' => 15],
            'item2' => ['value' => 20],
        ];

        $this->assertSame($expected, $array);
    }

    /**
     * Test sorting with SORT_DESC and SORT_NUMERIC.
     */
    public function test_array_multisort_nested_desc_numeric()
    {
        $array = [
            'item1' => ['value' => 10],
            'item2' => ['value' => 20],
            'item3' => ['value' => 15],
        ];

        array_multisort_nested($array, 'value', SORT_DESC, SORT_NUMERIC);

        $expected = [
            'item2' => ['value' => 20],
            'item3' => ['value' => 15],
            'item1' => ['value' => 10],
        ];

        $this->assertSame($expected, $array);
    }

    /**
     * Test sorting with SORT_ASC and SORT_NATURAL.
     */
    public function test_array_multisort_nested_asc_natural()
    {
        $array = [
            'item1' => ['value' => 'item10'],
            'item2' => ['value' => 'item2'],
            'item3' => ['value' => 'item1'],
        ];

        array_multisort_nested($array, 'value', SORT_ASC, SORT_NATURAL);

        $expected = [
            'item3' => ['value' => 'item1'],
            'item2' => ['value' => 'item2'],
            'item1' => ['value' => 'item10'],
        ];

        $this->assertSame($expected, $array);
    }

    /**
     * Test sorting with SORT_DESC and SORT_NATURAL.
     */
    public function test_array_multisort_nested_desc_natural()
    {
        $array = [
            'item1' => ['value' => 'item10'],
            'item2' => ['value' => 'item2'],
            'item3' => ['value' => 'item1'],
        ];

        array_multisort_nested($array, 'value', SORT_DESC, SORT_NATURAL);

        $expected = [
            'item1' => ['value' => 'item10'],
            'item2' => ['value' => 'item2'],
            'item3' => ['value' => 'item1'],
        ];

        $this->assertSame($expected, $array);
    }

    /**
     * Test searching for a value in a numeric array when the value is not found.
     */
    public function test_binary_search_not_found()
    {
        $array = [1, 3, 5, 7, 9];
        sort($array, SORT_NATURAL);
        $needle = 6;

        $result = binary_search($needle, $array);

        // 6 is not in the array
        $this->assertFalse($result);
    }

    /**
     * Test searching for a value in a numeric array with duplicates.
     */
    public function test_binary_search_with_duplicates()
    {
        $array = [1, 3, 3, 3, 7, 9];
        sort($array, SORT_NATURAL);
        $needle = 3;

        $result = binary_search($needle, $array, 'strnatcmp', null, 0, true);

        // 3 first occurs at index 1
        $this->assertSame(1, $result);
    }

    /**
     * Test searching for a value in a numeric array.
     */
    public function test_binary_search_numeric_array()
    {
        $array = [1, 3, 5, 7, 9, 10];
        sort($array, SORT_NATURAL);
        $needle = 5;

        $result = binary_search($needle, $array);

        // 5 is at index 2
        $this->assertSame(2, $result);
    }

    /**
     * Test searching for a value in a string array
     */
    public function test_binary_search_string_array_default()
    {
        $array = ['item1', 'item2', 'item10', 'item20'];
        sort($array, SORT_NATURAL);
        $needle = 'item10';

        $result = binary_search($needle, $array);

        // 'item10' is at index 2
        $this->assertSame(2, $result);
    }

    /**
     * Test binary search using strnatcmp.
     */
    public function test_binary_search_strnatcmp()
    {
        $array = ['item1', 'item10', 'item2'];
        sort($array, SORT_NATURAL);
        $needle = 'item10';

        $result = binary_search($needle, $array, 'strnatcmp');

        // 'item10' should be at index 2 after natural sort
        $this->assertSame(2, $result);
    }

    /**
     * Test binary search using strcmp.
     */
    public function test_binary_search_strcmp()
    {
        $array = ['item1', 'item10', 'item2'];
        sort($array, SORT_STRING);
        $needle = 'item10';

        $result = binary_search($needle, $array, 'strcmp');

        // 'item10' should be at index 1 after string sort
        $this->assertSame(1, $result);
    }

    /**
     * Test binary search with a custom comparison function.
     */
    public function test_binary_search_custom_comparison()
    {
        $array = [10, 20, 30, 40];
        sort($array, SORT_NUMERIC);
        $needle = 30;

        // Custom comparison function: standard numeric comparison
        $customComparison = function ($a, $b) {
            return $a - $b;
        };

        $result = binary_search($needle, $array, $customComparison);

        // 30 should be at index 2
        $this->assertSame(2, $result);
    }

    /**
     * Test binary_in_array with SORT_REGULAR.
     */
    public function test_binary_in_array_sort_regular()
    {
        $haystack = ['apple', 'banana', 'cherry'];
        $needle = 'banana';

        // Sort array with SORT_REGULAR and check if the needle exists
        $result = binary_in_array($needle, $haystack, true, SORT_REGULAR);

        // 'banana' should be found
        $this->assertTrue($result);
    }

    /**
     * Test binary_in_array with SORT_STRING.
     */
    public function test_binary_in_array_sort_string()
    {
        $haystack = ['apple', 'banana', 'cherry', 'Apple'];
        $needle = 'Apple';

        // Sort array with SORT_STRING and check if the needle exists
        $result = binary_in_array($needle, $haystack, true, SORT_STRING);

        // 'Apple' should be found
        $this->assertTrue($result);
    }

    /**
     * Test binary_in_array with SORT_NUMERIC.
     */
    public function test_binary_in_array_sort_numeric()
    {
        $haystack = [10, 2, 33, 25];
        $needle = 25;

        // Sort array with SORT_NUMERIC and check if the needle exists
        $result = binary_in_array($needle, $haystack, true, SORT_NUMERIC);

        // 25 should be found
        $this->assertTrue($result);
    }

    /**
     * Test binary_in_array with SORT_NATURAL.
     */
    public function test_binary_in_array_sort_natural()
    {
        $haystack = ['item1', 'item10', 'item2'];
        $needle = 'item10';

        // Sort array with SORT_NATURAL and check if the needle exists
        $result = binary_in_array($needle, $haystack, true, SORT_NATURAL);

        // 'item10' should be found
        $this->assertTrue($result);
    }

    /**
     * Test binary_in_array when the needle is not found.
     */
    public function test_binary_in_array_needle_not_found()
    {
        $haystack = ['apple', 'banana', 'cherry'];
        $needle = 'orange';

        // Sort array with SORT_REGULAR and check if the needle exists
        $result = binary_in_array($needle, $haystack, true, SORT_REGULAR);

        // 'orange' should not be found
        $this->assertFalse($result);
    }

    /**
     * Test binary_in_array without sorting.
     */
    public function test_binary_in_array_no_sorting()
    {
        $haystack = [10, 2, 33, 25];
        $needle = 33;

        // Check without sorting
        $result = binary_in_array($needle, $haystack);

        // 33 should be found
        $this->assertTrue($result);
    }

    /**
     * Test binary_search_with_sorting with SORT_REGULAR.
     */
    public function test_binary_search_with_sorting_sort_regular()
    {
        $haystack = ['banana', 'apple', 'cherry'];
        $needle = 'apple';

        // Test sorting with SORT_REGULAR and search for 'apple'
        $result = binary_search_with_sorting($needle, $haystack, SORT_REGULAR);

        // 'apple' should be found at index 0 after sorting
        $this->assertSame(0, $result);
    }

    /**
     * Test binary_search_with_sorting with SORT_NATURAL.
     */
    public function test_binary_search_with_sorting_sort_natural()
    {
        $haystack = ['item1', 'item10', 'item2'];
        $needle = 'item10';

        // Test sorting with SORT_NATURAL and search for 'item10'
        $result = binary_search_with_sorting($needle, $haystack, SORT_NATURAL);

        // 'item10' should be found at index 2 after sorting naturally
        $this->assertSame(2, $result);
    }

    /**
     * Test binary_search_with_sorting with SORT_NUMERIC.
     */
    public function test_binary_search_with_sorting_sort_numeric()
    {
        $haystack = [100, 50, 10, 1];
        $needle = 50;

        // Test sorting with SORT_NUMERIC and search for 50
        $result = binary_search_with_sorting($needle, $haystack, SORT_NUMERIC);

        // 50 should be found at index 2 after numeric sorting
        $this->assertSame(2, $result);
    }

    /**
     * Test binary_search_with_sorting with SORT_STRING.
     */
    public function test_binary_search_with_sorting_sort_string()
    {
        $haystack = ['Banana', 'apple', 'Cherry'];
        $needle = 'Cherry';

        // Test sorting with SORT_STRING and search for 'Cherry'
        $result = binary_search_with_sorting($needle, $haystack, SORT_STRING);

        // 'Cherry' should be found at index 1 after string sorting
        $this->assertSame(1, $result);
    }

    /**
     * Test binary_search_with_sorting with a custom callable.
     */
    public function test_binary_search_with_sorting_custom_callable()
    {
        $haystack = [10, 2, 33, 25];
        $needle = 25;

        // Custom comparison function that sorts in descending order
        $custom_compare = function ($a, $b) {
            return $b - $a;
        };

        // Test sorting with a custom callable and search for 25
        $result = binary_search_with_sorting($needle, $haystack, $custom_compare);

        // 25 should be found at index 1 after custom sorting
        $this->assertSame(1, $result);
    }

    /**
     * Test parsing a CSV string with headers.
     */
    public function test_str_csv_to_array_with_headers()
    {
        $csvString = "Domain,URL,Page AS,Ref.Domains,Backlinks,Search Traffic,URL Keywords\n" .
            "example.com,https://example.com/page1,30,12,100,1500,50\n" .
            "testsite.com,https://testsite.com/page2,45,20,300,2000,80\n" .
            "myblog.net,https://myblog.net/page3,25,15,200,1800,60";

        $expected = [
            'headers' => ['Domain', 'URL', 'Page AS', 'Ref.Domains', 'Backlinks', 'Search Traffic', 'URL Keywords'],
            [
                'Domain' => ['example.com', 'testsite.com', 'myblog.net'],
            ],
            [
                'URL' => ['https://example.com/page1', 'https://testsite.com/page2', 'https://myblog.net/page3'],
            ],
            [
                'Page AS' => [30, 45, 25],
            ],
            [
                'Ref.Domains' => [12, 20, 15],
            ],
            [
                'Backlinks' => [100, 300, 200],
            ],
            [
                'Search Traffic' => [1500, 2000, 1800],
            ],
            [
                'URL Keywords' => [50, 80, 60],
            ],
        ];

        $result = str_csv_to_array($csvString, true);
        $this->assertEquals($expected, $result);
    }

    /**
     * Test parsing a CSV string without headers.
     */
    public function test_str_csv_to_array_without_headers()
    {
        $csvString = "Domain,URL,Page AS,Ref.Domains,Backlinks,Search Traffic,URL Keywords\n" .
            "example.com,https://example.com/page1,30,12,100,1500,50\n" .
            "testsite.com,https://testsite.com/page2,45,20,300,2000,80\n" .
            "myblog.net,https://myblog.net/page3,25,15,200,1800,60";

        $expected = [
            ['Domain', 'URL', 'Page AS', 'Ref.Domains', 'Backlinks', 'Search Traffic', 'URL Keywords'],
            ['example.com', 'https://example.com/page1', 30, 12, 100, 1500, 50],
            ['testsite.com', 'https://testsite.com/page2', 45, 20, 300, 2000, 80],
            ['myblog.net', 'https://myblog.net/page3', 25, 15, 200, 1800, 60],
        ];

        $result = str_csv_to_array($csvString, false);
        $this->assertEquals($expected, $result);
    }

    /**
     * Test skipping rows with mismatched fields.
     */
    public function test_str_csv_to_array_with_mismatched_rows()
    {
        $csvString = "Domain,URL,Page AS,Ref.Domains,Backlinks,Search Traffic,URL Keywords\n" .
            "example.com,https://example.com/page1,30,12,100,1500,50\n" .
            "testsite.com,https://testsite.com/page2,45,20,300,2000,80\n" .
            "myblog.net,https://myblog.net/page3,25,15,200,1800,60\n" .
            "invalidrow.com,https://invalidrow.com/page";

        $expected = [
            'headers' => ['Domain', 'URL', 'Page AS', 'Ref.Domains', 'Backlinks', 'Search Traffic', 'URL Keywords'],
            [
                'Domain' => ['example.com', 'testsite.com', 'myblog.net'],
            ],
            [
                'URL' => ['https://example.com/page1', 'https://testsite.com/page2', 'https://myblog.net/page3'],
            ],
            [
                'Page AS' => [30, 45, 25],
            ],
            [
                'Ref.Domains' => [12, 20, 15],
            ],
            [
                'Backlinks' => [100, 300, 200],
            ],
            [
                'Search Traffic' => [1500, 2000, 1800],
            ],
            [
                'URL Keywords' => [50, 80, 60],
            ],
        ];

        $result = str_csv_to_array($csvString, true);
        $this->assertEquals($expected, $result);
    }

    /**
     * Test handling of an empty CSV string.
     */
    public function test_str_csv_to_array_with_empty_string()
    {
        $csvString = "";

        $expected = [];

        $result = str_csv_to_array($csvString, false);
        $this->assertEquals($expected, $result);
    }

    /**
     * Test handling of a CSV string with only headers and no data.
     */
    public function test_str_csv_to_array_with_only_headers()
    {
        $csvString = "Domain,URL,Page AS,Ref.Domains,Backlinks,Search Traffic,URL Keywords";

        $expected = [
            'headers' => ['Domain', 'URL', 'Page AS', 'Ref.Domains', 'Backlinks', 'Search Traffic', 'URL Keywords'],
            [
                'Domain' => [],
            ],
            [
                'URL' => [],
            ],
            [
                'Page AS' => [],
            ],
            [
                'Ref.Domains' => [],
            ],
            [
                'Backlinks' => [],
            ],
            [
                'Search Traffic' => [],
            ],
            [
                'URL Keywords' => [],
            ],
        ];

        $result = str_csv_to_array($csvString, true);
        $this->assertEquals($expected, $result);
    }

    /**
     * Test handling of a CSV string with only headers and no data, with headers disabled.
     */
    public function test_str_csv_to_array_with_only_headers_no_headers_option()
    {
        $csvString = "Domain,URL,Page AS,Ref.Domains,Backlinks,Search Traffic,URL Keywords";

        $expected = [
            ['Domain', 'URL', 'Page AS', 'Ref.Domains', 'Backlinks', 'Search Traffic', 'URL Keywords']
        ];

        $result = str_csv_to_array($csvString, false);
        $this->assertEquals($expected, $result);
    }

    /**
     * Test conversion of a well-formed CSV array into an associative array.
     */
    public function test_csv_array_to_assoc_basic_conversion()
    {
        $csv_data = [
            'headers' => ['Domain', 'URL', 'Page AS'],
            [
                'Domain' => ['example.com', 'testsite.com', 'myblog.net'],
            ],
            [
                'URL' => ['https://example.com', 'https://testsite.com', 'https://myblog.net'],
            ],
            [
                'Page AS' => [30, 45, 25],
            ]
        ];

        $expected = [
            'Domain' => ['example.com', 'testsite.com', 'myblog.net'],
            'URL' => ['https://example.com', 'https://testsite.com', 'https://myblog.net'],
            'Page AS' => [30, 45, 25]
        ];

        $result = csv_array_to_assoc($csv_data);
        $this->assertEquals($expected, $result);
    }

    /**
     * Test conversion when the CSV array has no data rows (only headers).
     */
    public function test_csv_array_to_assoc_with_only_headers()
    {
        $csv_data = [
            'headers' => ['Domain', 'URL', 'Page AS'],
            [
                'Domain' => [],
            ],
            [
                'URL' => [],
            ],
            [
                'Page AS' => [],
            ]
        ];

        $expected = [
            'Domain' => [],
            'URL' => [],
            'Page AS' => []
        ];

        $result = csv_array_to_assoc($csv_data);
        $this->assertEquals($expected, $result);
    }

    /**
     * Test conversion with mixed types in the CSV data.
     */
    public function test_csv_array_to_assoc_with_mixed_data_types()
    {
        $csv_data = [
            'headers' => ['Domain', 'Active', 'Page Views'],
            [
                'Domain' => ['example.com', 'testsite.com', 'myblog.net'],
            ],
            [
                'Active' => [TRUE, FALSE, TRUE],
            ],
            [
                'Page Views' => [1000, 2500, 1800],
            ]
        ];

        $expected = [
            'Domain' => ['example.com', 'testsite.com', 'myblog.net'],
            'Active' => [TRUE, FALSE, TRUE],
            'Page Views' => [1000, 2500, 1800]
        ];

        $result = csv_array_to_assoc($csv_data);
        $this->assertEquals($expected, $result);
    }

    /**
     * Test conversion with empty CSV data.
     */
    public function test_csv_array_to_assoc_with_empty_data()
    {
        $csv_data = [
            'headers' => ['Domain', 'URL', 'Page AS'],
            [
                'Domain' => [],
            ],
            [
                'URL' => [],
            ],
            [
                'Page AS' => [],
            ]
        ];

        $expected = [
            'Domain' => [],
            'URL' => [],
            'Page AS' => []
        ];

        $result = csv_array_to_assoc($csv_data);
        $this->assertEquals($expected, $result);
    }

    /**
     * Test conversion with non-standard headers and data.
     */
    public function test_csv_array_to_assoc_with_non_standard_headers()
    {
        $csv_data = [
            'headers' => ['Header1', 'Header2', 'Header3'],
            [
                'Header1' => ['Value1', 'Value2', 'Value3'],
            ],
            [
                'Header2' => ['A', 'B', 'C'],
            ],
            [
                'Header3' => [10, 20, 30],
            ]
        ];

        $expected = [
            'Header1' => ['Value1', 'Value2', 'Value3'],
            'Header2' => ['A', 'B', 'C'],
            'Header3' => [10, 20, 30]
        ];

        $result = csv_array_to_assoc($csv_data);
        $this->assertEquals($expected, $result);
    }

    /**
     * Test filling empty values in an associative array with standard data.
     */
    public function test_assoc_array_fill_empty_values_standard()
    {
        $array = [
            'Keyword' => ['Apples', 'Bananas', 'Oranges', 'Pears'],
            'Color' => ['Red', 'Yellow'],
            'Size' => [],
            'Age' => [1, 2, 3, 4]
        ];

        $expected = [
            'Keyword' => ['Apples', 'Bananas', 'Oranges', 'Pears'],
            'Color' => ['Red', 'Yellow', '', ''],
            'Size' => ['', '', '', ''],
            'Age' => [1, 2, 3, 4]
        ];

        $result = assoc_array_fill_empty_values($array);
        $this->assertEquals($expected, $result);
    }

    /**
     * Test handling an associative array with no missing values.
     */
    public function test_assoc_array_fill_empty_values_no_missing_values()
    {
        $array = [
            'Keyword' => ['Apples', 'Bananas'],
            'Color' => ['Red', 'Yellow'],
            'Size' => ['Small', 'Medium'],
            'Age' => [1, 2]
        ];

        $expected = [
            'Keyword' => ['Apples', 'Bananas'],
            'Color' => ['Red', 'Yellow'],
            'Size' => ['Small', 'Medium'],
            'Age' => [1, 2]
        ];

        $result = assoc_array_fill_empty_values($array);
        $this->assertEquals($expected, $result);
    }

    /**
     * Test handling an associative array where the index field is empty.
     */
    public function test_assoc_array_fill_empty_values_empty_index_field()
    {
        $array = [
            'Keyword' => [],
            'Color' => ['Red', 'Yellow'],
            'Size' => ['Small'],
            'Age' => [1, 2]
        ];

        $expected = [
            'Keyword' => [],
            'Color' => ['Red', 'Yellow'],
            'Size' => ['Small'],
            'Age' => [1, 2]
        ];

        $result = assoc_array_fill_empty_values($array);
        $this->assertEquals($expected, $result);
    }

    /**
     * Test handling when the index field does not exist in the array.
     * Expecting an exception to be thrown.
     */
    public function test_assoc_array_fill_empty_values_index_field_not_exists()
    {
        // Expect an InvalidArgumentException to be thrown.
        $this->expectException(\InvalidArgumentException::class);

        // Expect the exception message to match.
        $this->expectExceptionMessage("Index field 'Keyword' not found in the associative array.");

        $array = [
            'Color' => ['Red', 'Yellow'],
            'Size' => ['Small', 'Medium'],
            'Age' => [1, 2]
        ];

        // Call the function, which should throw an exception.
        assoc_array_fill_empty_values($array, 'Keyword');
    }

    /**
     * Test handling an associative array with numeric index values.
     */
    public function test_assoc_array_fill_empty_values_numeric_index()
    {
        $array = [
            'Keyword' => [10, 20, 30],
            'Color' => [1 => 'Red', 2 => 'Yellow']
        ];

        $expected = [
            'Keyword' => [10, 20, 30],
            'Color' => [1 => 'Red', 2 => 'Yellow', 0 => '']
        ];

        $result = assoc_array_fill_empty_values($array);
        $this->assertEquals($expected, $result);
    }

    /**
     * Test handling an associative array with mixed data types.
     */
    public function test_assoc_array_fill_empty_values_mixed_data_types()
    {
        $array = [
            'Keyword' => ['Apple', 'Banana', 'Cherry'],
            'Color' => ['Red'],
            'Size' => [null, 'Large'],
            'Age' => [5.5]
        ];

        $expected = [
            'Keyword' => ['Apple', 'Banana', 'Cherry'],
            'Color' => ['Red', '', ''],
            'Size' => [null, 'Large', ''],
            'Age' => [5.5, '', '']
        ];

        $result = assoc_array_fill_empty_values($array);
        $this->assertEquals($expected, $result);
    }

    /**
     * Test merging arrays with a common index field.
     */
    public function test_assoc_array_merge_basic_merge()
    {
        $first = [
            'FieldA' => [1, 2, 3],
            'FieldB' => [10, 20, 30]
        ];

        $second = [
            'FieldA' => ['A', 'B', 'C'],
            'FieldB' => ['Apple', 'Banana', 'Carrot']
        ];

        $third = [
            'FieldA' => [100, 2, 300],
            'FieldB' => [1000, 9999, 3000]
        ];

        $expected = [
            'FieldA' => [1, 2, 3, 'A', 'B', 'C', 100, 300],
            'FieldB' => [10, 20, 30, 'Apple', 'Banana', 'Carrot', 1000, 3000]
        ];

        $result = assoc_array_merge('FieldA', $first, $second, $third);
        $this->assertEquals($expected, $result);
    }

    /**
     * Test merging arrays where the index field is missing in one array.
     */
    public function test_assoc_array_merge_missing_index_field()
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage("Index field 'FieldA' not found in one of the arrays.");

        $first = [
            'FieldA' => [1, 2, 3],
            'FieldB' => [10, 20, 30]
        ];

        $second = [
            'FieldX' => ['A', 'B', 'C'],
            'FieldB' => ['Apple', 'Banana', 'Carrot']
        ];

        assoc_array_merge('FieldA', $first, $second);
    }

    /**
     * Test merging arrays with non-overlapping index field values.
     */
    public function test_assoc_array_merge_non_overlapping_values()
    {
        $first = [
            'FieldA' => [1, 2],
            'FieldB' => [10, 20]
        ];

        $second = [
            'FieldA' => [3, 4],
            'FieldB' => [30, 40]
        ];

        $expected = [
            'FieldA' => [1, 2, 3, 4],
            'FieldB' => [10, 20, 30, 40]
        ];

        $result = assoc_array_merge('FieldA', $first, $second);
        $this->assertEquals($expected, $result);
    }

    /**
     * Test merging arrays with completely overlapping index field values.
     */
    public function test_assoc_array_merge_completely_overlapping_values()
    {
        $first = [
            'FieldA' => [1, 2],
            'FieldB' => [10, 20]
        ];

        $second = [
            'FieldA' => [2, 1],
            'FieldB' => [30, 40]
        ];

        $expected = [
            'FieldA' => [1, 2],
            'FieldB' => [10, 20]
        ];

        $result = assoc_array_merge('FieldA', $first, $second);
        $this->assertEquals($expected, $result);
    }

    /**
     * Test merging arrays with some overlapping and some non-overlapping index field values.
     */
    public function test_assoc_array_merge_partial_overlap()
    {
        $first = [
            'FieldA' => [1, 2],
            'FieldB' => [10, 20]
        ];

        $second = [
            'FieldA' => [2, 3],
            'FieldB' => [25, 35]
        ];

        $expected = [
            'FieldA' => [1, 2, 3],
            'FieldB' => [10, 20, 35]
        ];

        $result = assoc_array_merge('FieldA', $first, $second);
        $this->assertEquals($expected, $result);
    }

    /**
     * Test merging arrays where one of the arrays is empty.
     */
    public function test_assoc_array_merge_with_empty_array()
    {
        $first = [
            'FieldA' => [1, 2],
            'FieldB' => [10, 20]
        ];

        $second = [
            'FieldA' => [],
            'FieldB' => []
        ];

        $expected = [
            'FieldA' => [1, 2],
            'FieldB' => [10, 20]
        ];

        $result = assoc_array_merge('FieldA', $first, $second);
        $this->assertEquals($expected, $result);
    }

    /**
     * Test merging multiple arrays with distinct associative keys.
     */
    public function test_array_merge_recursive_distinct_with_associative_keys()
    {
        $array1 = [
            'headers' => ['User-Agent' => 'Mozilla/5.0', 'Accept-Language' => 'en-US,en;q=1.0'],
            'connect_timeout' => 10,
            'timeout' => 10
        ];

        $array2 = [
            'headers' => ['User-Agent' => 'Guzzle', 'X-Foo' => ['Bar', 'Baz']],
            'connect_timeout' => 20,
            'timeout' => 20
        ];

        $expected = [
            'headers' => [
                'User-Agent' => 'Guzzle',
                'Accept-Language' => 'en-US,en;q=1.0',
                'X-Foo' => ['Bar', 'Baz']
            ],
            'connect_timeout' => 20,
            'timeout' => 20
        ];

        $result = array_merge_recursive_distinct($array1, $array2);
        $this->assertEquals($expected, $result);
    }

    /**
     * Test merging multiple arrays with numeric keys.
     */
    public function test_array_merge_recursive_distinct_with_numeric_keys()
    {
        $array1 = [
            'headers' => ['User-Agent' => 'Mozilla/5.0', 'Accept-Language' => 'en-US,en;q=1.0'],
            'connect_timeout' => 10,
            'timeout' => 10,
            'options' => [1, 2, 3]
        ];

        $array2 = [
            'headers' => ['User-Agent' => 'Guzzle', 'X-Foo' => ['Bar', 'Baz']],
            'connect_timeout' => 20,
            'timeout' => 20,
            'options' => [3, 4, 5]
        ];

        $expected = [
            'headers' => [
                'User-Agent' => 'Guzzle',
                'Accept-Language' => 'en-US,en;q=1.0',
                'X-Foo' => ['Bar', 'Baz']
            ],
            'connect_timeout' => 20,
            'timeout' => 20,
            'options' => [1, 2, 3, 4, 5]
        ];

        $result = array_merge_recursive_distinct($array1, $array2);
        $this->assertEquals($expected, $result);
    }

    /**
     * Test merging arrays where latter values overwrite earlier ones.
     */
    public function test_array_merge_recursive_distinct_with_overwriting()
    {
        $array1 = [
            'timeout' => 10,
            'headers' => ['User-Agent' => 'Mozilla/5.0']
        ];

        $array2 = [
            'timeout' => 20,
            'headers' => ['User-Agent' => 'Guzzle']
        ];

        $expected = [
            'timeout' => 20,
            'headers' => ['User-Agent' => 'Guzzle']
        ];

        $result = array_merge_recursive_distinct($array1, $array2);
        $this->assertEquals($expected, $result);
    }

    /**
     * Test merging arrays with nested structures.
     */
    public function test_array_merge_recursive_distinct_with_nested_arrays()
    {
        $array1 = [
            'headers' => [
                'User-Agent' => 'Mozilla/5.0',
                'X-Foo' => ['Bar', 'Baz']
            ]
        ];

        $array2 = [
            'headers' => [
                'User-Agent' => 'Guzzle',
                'X-Foo' => ['Baz', 'Qux']
            ]
        ];

        $expected = [
            'headers' => [
                'User-Agent' => 'Guzzle',
                'X-Foo' => ['Bar', 'Baz', 'Qux']
            ]
        ];

        $result = array_merge_recursive_distinct($array1, $array2);
        $this->assertEquals($expected, $result);
    }

    /**
     * Test merging empty arrays.
     */
    public function test_array_merge_recursive_distinct_with_empty_arrays()
    {
        $array1 = [];
        $array2 = ['headers' => ['User-Agent' => 'Guzzle']];

        $expected = ['headers' => ['User-Agent' => 'Guzzle']];

        $result = array_merge_recursive_distinct($array1, $array2);
        $this->assertEquals($expected, $result);
    }

    /**
     * Test merging 3 arrays recursively.
     */
    public function test_array_merge_recursive_distinct_with_3_arrays()
    {
        $array1 = [
            'headers' => ['User-Agent' => 'Mozilla/5.0', 'Accept-Language' => 'en-US,en;q=1.0'],
            'connect_timeout' => 10,
            'timeout' => 10
        ];

        $array2 = [
            'headers' => ['User-Agent' => 'Guzzle', 'X-Foo' => ['Bar', 'Baz']],
            'connect_timeout' => 20
        ];

        $array3 = [
            'headers' => ['X-Foo' => ['BazB', 'Qux']],
            'timeout' => 30
        ];

        $expected = [
            'headers' => [
                'User-Agent' => 'Guzzle',
                'Accept-Language' => 'en-US,en;q=1.0',
                'X-Foo' => ['Bar', 'Baz', 'BazB', 'Qux']
            ],
            'connect_timeout' => 20,
            'timeout' => 30
        ];

        $result = array_merge_recursive_distinct($array1, $array2, $array3);
        $this->assertEquals($expected, $result);
    }

    /**
     * Test merging 4 arrays recursively.
     */
    public function test_array_merge_recursive_distinct_with_4_arrays()
    {
        $array1 = [
            'headers' => ['User-Agent' => 'Mozilla/5.0', 'Accept-Language' => 'en-US,en;q=1.0'],
            'connect_timeout' => 10,
            'timeout' => 10
        ];

        $array2 = [
            'headers' => ['User-Agent' => 'Guzzle', 'X-Foo' => ['Bar', 'Baz']],
            'connect_timeout' => 20
        ];

        $array3 = [
            'headers' => ['X-Foo' => ['BazB', 'Qux']],
            'timeout' => 30
        ];

        $array4 = [
            'headers' => ['X-Foo' => ['QuxB', 'BazB']],
            'timeout' => 40
        ];

        $expected = [
            'headers' => [
                'User-Agent' => 'Guzzle',
                'Accept-Language' => 'en-US,en;q=1.0',
                'X-Foo' => ['Bar', 'Baz', 'BazB', 'Qux', 'QuxB']
            ],
            'connect_timeout' => 20,
            'timeout' => 40
        ];

        $result = array_merge_recursive_distinct($array1, $array2, $array3, $array4);
        $this->assertEquals($expected, $result);
    }

    /**
     * Test merging 6 arrays recursively.
     */
    public function test_array_merge_recursive_distinct_with_6_arrays()
    {
        $array1 = [
            'headers' => ['User-Agent' => 'Mozilla/5.0', 'Accept-Language' => 'en-US,en;q=1.0'],
            'connect_timeout' => 10,
            'timeout' => 10
        ];

        $array2 = [
            'headers' => ['User-Agent' => 'Guzzle', 'X-Foo' => ['Bar', 'Baz']],
            'connect_timeout' => 20
        ];

        $array3 = [
            'headers' => ['X-Foo' => ['BazB', 'Qux']],
            'timeout' => 30
        ];

        $array4 = [
            'headers' => ['X-Foo' => ['QuxB', 'BazB']],
            'timeout' => 40
        ];

        $array5 = [
            'headers' => ['User-Agent' => 'PHP', 'X-Foo' => ['Apple', 'BarB']],
            'timeout' => 50
        ];

        $array6 = [
            'headers' => ['X-Foo' => ['Baz', 'Pear', 'BazB']],
            'timeout' => 60
        ];

        $expected = [
            'headers' => [
                'User-Agent' => 'PHP',
                'Accept-Language' => 'en-US,en;q=1.0',
                'X-Foo' => ['Bar', 'Baz', 'BazB', 'Qux', 'QuxB', 'Apple', 'BarB', 'Pear']
            ],
            'connect_timeout' => 20,
            'timeout' => 60
        ];

        $result = array_merge_recursive_distinct($array1, $array2, $array3, $array4, $array5, $array6);
        $this->assertEquals($expected, $result);
    }

    /**
     * Test that an invalid filename throws an InvalidArgumentException.
     */
    public function test_file_to_array_with_invalid_filename()
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage("Invalid filename: ../some/invalid/path.txt");

        file_to_array('../some/invalid/path.txt');
    }

    /**
     * Test that a non-existent file throws a RuntimeException.
     */
    public function test_file_to_array_with_non_existent_file()
    {
        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage("File cannot be read: non_existent_file.txt");

        file_to_array('non_existent_file.txt');
    }

    /**
     * Test reading a plain text file and returning it as an array of lines.
     */
    public function test_file_to_array_plain_text()
    {
        $filename = 'test_plain_' . uniqid() . '.txt';
        $file_contents = "line1\nline2\nline3";
        file_put_contents($filename, $file_contents);

        $expected = ['line1', 'line2', 'line3'];
        $result = file_to_array($filename);

        $this->assertEquals($expected, $result);

        // Clean up the test file
        unlink($filename);
    }

    /**
     * Test reading a CSV file and returning it as an array using str_csv_to_array().
     */
    public function test_file_to_array_csv()
    {
        $filename = 'test_' . uniqid() . '.csv';
        $file_contents = "header1,header2\nvalue1,value2\nvalue3,value4";
        file_put_contents($filename, $file_contents);

        $expected = [
            'headers' => ['header1', 'header2'],
            ['header1' => ['value1', 'value3']],
            ['header2' => ['value2', 'value4']],
        ];

        $result = file_to_array($filename, ',', true);

        $this->assertEquals($expected, $result);

        // Clean up the test file
        unlink($filename);
    }

    /**
     * Test that a file with Windows-style line endings (\r\n) is correctly processed.
     */
    public function test_file_to_array_with_windows_line_endings()
    {
        $filename = 'test_windows_' . uniqid() . '.txt';
        $file_contents = "line1\r\nline2\r\nline3";
        file_put_contents($filename, $file_contents);

        $expected = ['line1', 'line2', 'line3'];
        $result = file_to_array($filename);

        $this->assertEquals($expected, $result);

        // Clean up the test file
        unlink($filename);
    }

    /**
     * Test that a file with Mac-style line endings (\r) is correctly processed.
     */
    public function test_file_to_array_with_mac_line_endings()
    {
        $filename = 'test_mac_' . uniqid() . '.txt';
        $file_contents = "line1\rline2\rline3";
        file_put_contents($filename, $file_contents);

        $expected = ['line1', 'line2', 'line3'];
        $result = file_to_array($filename);

        $this->assertEquals($expected, $result);

        // Clean up the test file
        unlink($filename);
    }

    /**
     * Test that the function returns an array.
     */
    public function test_build_nested_array_returns_array()
    {
        $result = build_nested_array();
        $this->assertIsArray($result);
    }

    /**
     * Test that the function returns an array of the correct length.
     */
    public function test_build_nested_array_correct_length()
    {
        $items = 5;
        $result = build_nested_array([], 0, $items);
        $this->assertCount($items, $result);
    }

    /**
     * Test that the function returns a nested array with the correct depth.
     */
    public function test_build_nested_array_correct_depth()
    {
        $depth = 3;
        $result = build_nested_array([], 0, 1, $depth);
        $this->assertIsArray($result);

        // Recursively check depth
        $current = $result;
        for ($i = 0; $i < $depth; $i++) {
            $this->assertIsArray($current, "Failed at depth $i");
            $this->assertCount(1, $current, "Array at depth $i should contain exactly one element");
            // Move to the next nested level
            $current = reset($current);
        }
        $this->assertIsArray($current, "Array did not reach the expected depth of $depth");
        $this->assertCount(1, $current, "Final level array should contain exactly one element");
    }


    /**
     * Test that the function handles string key probability correctly.
     */
    public function test_build_nested_array_string_key_probability()
    {
        // 100% probability for string keys
        $result = build_nested_array([], 1.0, 5);
        foreach ($result as $key => $value) {
            $this->assertIsString($key);
        }

        // 0% probability for string keys
        $result = build_nested_array([], 0.0, 5);
        foreach ($result as $key => $value) {
            $this->assertIsInt($key);
        }
    }

    /**
     * Test that the function randomizes item counts correctly.
     */
    public function test_build_nested_array_randomizes_item_counts()
    {
        $result1 = build_nested_array([], 0, 5, 1, TRUE, 'hash1');
        $result2 = build_nested_array([], 0, 5, 1, TRUE, 'hash2');

        // Different hash strings should lead to different item counts
        $this->assertNotEquals(count($result1), count($result2));
    }

    /**
     * Test that the function works with empty options.
     */
    public function test_build_nested_array_with_empty_options()
    {
        $result = build_nested_array([], 0, 5);
        $this->assertNotEmpty($result);
    }

    /**
     * Helper function to recursively check if all elements are in the provided options.
     *
     * @param mixed $element The element or array of elements to check.
     * @param array $options The array of valid options.
     */
    private function assertAllElementsAreInOptions($element, array $options)
    {
        if (is_array($element)) {
            foreach ($element as $value) {
                $this->assertAllElementsAreInOptions($value, $options);
            }
        } else {
            $this->assertContains($element, $options, "The value '$element' is not in the provided options.");
        }
    }

    /**
     * Test that the function works with non-empty options.
     */
    public function test_build_nested_array_with_non_empty_options()
    {
        $options = ['apple', 'banana', 'cherry'];
        $result = build_nested_array($options, 0, 5, 1, FALSE, 'hash');

        // Recursively ensure all elements are from the provided options
        $this->assertAllElementsAreInOptions($result, $options);
    }

    /**
     * Test that the function handles a provided hash string correctly.
     */
    public function test_build_nested_array_with_hash_string()
    {
        $options = ['apple', 'banana', 'cherry'];
        $hash_string = 'test_hash';
        $result1 = build_nested_array($options, 0, 5, 1, FALSE, $hash_string);
        $result2 = build_nested_array($options, 0, 5, 1, FALSE, $hash_string);

        // With the same hash string, the results should be the same
        $this->assertEquals($result1, $result2);
    }

    /**
     * Test that the function returns the symmetric difference when both arrays have distinct elements.
     */
    public function test_array_diff_net_with_distinct_elements()
    {
        // Define the input arrays
        $array1 = [1, 2, 3];
        $array2 = [4, 5, 6];

        // Define the expected result
        $expected = [1, 2, 3, 4, 5, 6];

        // Call the function
        $result = array_diff_net($array1, $array2);

        // Assert that the result matches the expected result
        $this->assertEquals($expected, $result);
    }

    /**
     * Test that the function returns an empty array when both arrays are identical.
     */
    public function test_array_diff_net_with_identical_arrays()
    {
        // Define the input arrays
        $array1 = [1, 2, 3];
        $array2 = [1, 2, 3];

        // Define the expected result
        $expected = [];

        // Call the function
        $result = array_diff_net($array1, $array2);

        // Assert that the result matches the expected result
        $this->assertEquals($expected, $result);
    }

    /**
     * Test that the function returns the symmetric difference when arrays have overlapping elements.
     */
    public function test_array_diff_net_with_overlapping_elements()
    {
        // Define the input arrays
        $array1 = [1, 2, 3, 4];
        $array2 = [3, 4, 5, 6];

        // Define the expected result
        $expected = [1, 2, 5, 6];

        // Call the function
        $result = array_diff_net($array1, $array2);

        // Assert that the result matches the expected result
        $this->assertEquals($expected, $result);
    }

    /**
     * Test that the function handles empty arrays correctly.
     */
    public function test_array_diff_net_with_empty_arrays()
    {
        // Define the input arrays
        $array1 = [];
        $array2 = [];

        // Define the expected result
        $expected = [];

        // Call the function
        $result = array_diff_net($array1, $array2);

        // Assert that the result matches the expected result
        $this->assertEquals($expected, $result);
    }

    /**
     * Test that the function handles cases where one array is empty.
     */
    public function test_array_diff_net_with_one_empty_array()
    {
        // Define the input arrays
        $array1 = [1, 2, 3];
        $array2 = [];

        // Define the expected result
        $expected = [1, 2, 3];

        // Call the function
        $result = array_diff_net($array1, $array2);

        // Assert that the result matches the expected result
        $this->assertEquals($expected, $result);

        // Repeat the test with reversed arrays
        $expected = [1, 2, 3];
        $result = array_diff_net($array2, $array1);
        $this->assertEquals($expected, $result);
    }

    /**
     * Test that the function handles arrays with non-integer values correctly.
     */
    public function test_array_diff_net_with_non_integer_values()
    {
        // Define the input arrays
        $array1 = ['apple', 'banana', 'cherry'];
        $array2 = ['banana', 'date', 'fig'];

        // Define the expected result
        $expected = ['apple', 'cherry', 'date', 'fig'];

        // Call the function
        $result = array_diff_net($array1, $array2);

        // Assert that the result matches the expected result
        $this->assertEquals($expected, $result);
    }

    /**
     * Test that the function returns the correct difference when both arrays are flat and distinct.
     */
    public function test_array_diff_recursive_with_flat_distinct_arrays()
    {
        // Define the input arrays
        $array1 = [1, 2, 3];
        $array2 = [4, 5, 6];

        // Define the expected result
        $expected = [1, 2, 3];

        // Call the function
        $result = array_diff_recursive($array1, $array2);

        // Assert that the result matches the expected result
        $this->assertEquals($expected, $result);
    }

    /**
     * Test that the function returns an empty array when both arrays are identical.
     */
    public function test_array_diff_recursive_with_identical_arrays()
    {
        // Define the input arrays
        $array1 = [1, 2, 3];
        $array2 = [1, 2, 3];

        // Define the expected result
        $expected = [];

        // Call the function
        $result = array_diff_recursive($array1, $array2);

        // Assert that the result matches the expected result
        $this->assertEquals($expected, $result);
    }

    /**
     * Test that the function returns the correct difference when arrays have overlapping elements.
     */
    public function test_array_diff_recursive_with_overlapping_elements()
    {
        // Define the input arrays
        $array1 = [1, 2, 3, 4];
        $array2 = [3, 4, 5, 6];

        // Define the expected result
        $expected = [1, 2];

        // Call the function
        $result = array_diff_recursive($array1, $array2);

        // Assert that the result matches the expected result
        $this->assertEquals($expected, $result);
    }

    /**
     * Test that the function returns the correct difference with nested arrays.
     */
    public function test_array_diff_recursive_with_nested_arrays()
    {
        // Define the input arrays
        $array1 = ['a' => [1, 2, 3], 'b' => [4, 5, 6]];
        $array2 = ['a' => [1, 2], 'b' => [5, 7]];

        // Define the expected result
        $expected = ['a' => [2 => 3], 'b' => [0 => 4, 2 => 6]];

        // Call the function
        $result = array_diff_recursive($array1, $array2);

        // Assert that the result matches the expected result
        $this->assertEquals($expected, $result);
    }

    /**
     * Test that the function handles empty arrays correctly.
     */
    public function test_array_diff_recursive_with_empty_arrays()
    {
        // Define the input arrays
        $array1 = [];
        $array2 = [];

        // Define the expected result
        $expected = [];

        // Call the function
        $result = array_diff_recursive($array1, $array2);

        // Assert that the result matches the expected result
        $this->assertEquals($expected, $result);
    }

    /**
     * Test that the function handles cases where one array is empty.
     */
    public function test_array_diff_recursive_with_one_empty_array()
    {
        // Define the input arrays
        $array1 = [1, 2, 3];
        $array2 = [];

        // Define the expected result
        $expected = [1, 2, 3];

        // Call the function
        $result = array_diff_recursive($array1, $array2);

        // Assert that the result matches the expected result
        $this->assertEquals($expected, $result);

        // Repeat the test with reversed arrays
        $expected = [];
        $result = array_diff_recursive($array2, $array1);
        $this->assertEquals($expected, $result);
    }

    /**
     * Test that the function returns the correct difference when arrays have non-integer values.
     */
    public function test_array_diff_recursive_with_non_integer_values()
    {
        // Define the input arrays
        $array1 = ['apple', 'banana', 'cherry'];
        $array2 = ['banana', 'date', 'fig'];

        // Define the expected result values only
        $expected = ['apple', 'cherry'];

        // Call the function
        // Use array_values() to reindex keys
        $result = array_values(array_diff_recursive($array1, $array2));

        // Assert that the result matches the expected values, ignoring keys
        $this->assertEquals($expected, $result);
    }

    /**
     * Tests that array_diff_recursive_net returns the correct differences 
     * when the arrays have different values and structures.
     */
    public function test_array_diff_recursive_net_with_different_arrays()
    {
        $array1 = [
            0,
            1,
            2,
            'three' => 3,
            4,
            'five' => 5,
            6,
            'six' => 6,
            7,
            'notseven' => 6,
            8,
            [3, 4, 5],
            'hundreds' => [100, 200, 300, 400]
        ];

        $array2 = [
            0,
            'three' => 3,
            4,
            1,
            7,
            'notseven' => 666,
            'five' => 555,
            2,
            10,
            11,
            'twelve' => 12,
            13,
            'hundreds' => [100, 300, 500],
            [3, 7, 5]
        ];

        $expected = [
            'five' => [
                0 => 5,
                1 => 555,
            ],
            0 => 6,
            'six' => 6,
            'notseven' => [
                0 => 6,
                1 => 666,
            ],
            1 => 8,
            2 => [
                0 => 3,
                1 => 4,
                2 => 5,
            ],
            'hundreds' => [
                1 => 200,
                3 => 400,
                4 => 500,
            ],
            3 => 10,
            4 => 11,
            'twelve' => 12,
            5 => 13,
            6 => [
                0 => 3,
                1 => 7,
                2 => 5,
            ],
        ];

        $result = array_diff_recursive_net($array1, $array2);

        $this->assertEquals($expected, $result);
    }

    /**
     * Tests that array_diff_recursive_net returns an empty array 
     * when both arrays are identical.
     */
    public function test_array_diff_recursive_net_with_identical_arrays()
    {
        $array1 = [
            0,
            1,
            2,
            'three' => 3,
            4,
            'five' => 5,
            6,
            'six' => 6,
            7,
            'notseven' => 6,
            8,
            [3, 4, 5],
            'hundreds' => [100, 200, 300, 400]
        ];

        $array2 = [
            0,
            1,
            2,
            'three' => 3,
            4,
            'five' => 5,
            6,
            'six' => 6,
            7,
            'notseven' => 6,
            8,
            [3, 4, 5],
            'hundreds' => [100, 200, 300, 400]
        ];

        $expected = [];

        $result = array_diff_recursive_net($array1, $array2);

        $this->assertEquals($expected, $result);
    }

    /**
     * Tests that array_diff_recursive_net handles nested arrays correctly.
     */
    public function test_array_diff_recursive_net_with_nested_arrays()
    {
        $array1 = [
            'first' => [1, 2, 3],
            'second' => [4, 5, [6, 7]]
        ];

        $array2 = [
            'first' => [1, 2, 4],
            'second' => [4, 5, [6, 8]]
        ];

        $expected = [
            'first' => [
                2 => 3,
                3 => 4,
            ],
            'second' => [
                2 => [
                    1 => 7,
                ],
                3 => [
                    1 => 8,
                ],
            ],
        ];

        $result = array_diff_recursive_net($array1, $array2);

        $this->assertEquals($expected, $result);
    }

    /**
     * Tests that array_diff_recursive_net returns the correct result 
     * when one array is empty.
     */
    public function test_array_diff_recursive_net_with_empty_array()
    {
        $array1 = [];
        $array2 = [
            0,
            1,
            2,
            'three' => 3,
            4,
            'five' => 5,
            6,
            'six' => 6,
            7,
            'notseven' => 666,
            'hundreds' => [100, 300, 500]
        ];

        $expected = $array2;

        $result = array_diff_recursive_net($array1, $array2);

        $this->assertEquals($expected, $result);
    }

    /**
     * Tests that array_sort_by_fields sorts correctly by a single field in descending order.
     */
    public function test_array_sort_by_fields_single_field_desc()
    {
        $data = [
            ['volume' => 67, 'edition' => 2],
            ['volume' => 86, 'edition' => 1],
            ['volume' => 85, 'edition' => 6],
            ['volume' => 98, 'edition' => 2],
        ];

        $expected = [
            ['volume' => 98, 'edition' => 2],
            ['volume' => 86, 'edition' => 1],
            ['volume' => 85, 'edition' => 6],
            ['volume' => 67, 'edition' => 2],
        ];

        $result = array_sort_by_fields($data, 'volume', SORT_DESC);

        $this->assertEquals($expected, $result);
    }

    /**
     * Tests that array_sort_by_fields sorts correctly by multiple fields.
     * The first field is sorted in descending order, and the second field is sorted in ascending order.
     */
    public function test_array_sort_by_fields_multiple_fields()
    {
        $data = [
            ['volume' => 86, 'edition' => 6],
            ['volume' => 67, 'edition' => 7],
            ['volume' => 86, 'edition' => 1],
            ['volume' => 67, 'edition' => 2],
            ['volume' => 98, 'edition' => 2],
        ];

        $expected = [
            ['volume' => 98, 'edition' => 2],
            ['volume' => 86, 'edition' => 1],
            ['volume' => 86, 'edition' => 6],
            ['volume' => 67, 'edition' => 2],
            ['volume' => 67, 'edition' => 7],
        ];

        $result = array_sort_by_fields($data, 'volume', SORT_DESC, 'edition', SORT_ASC);

        $this->assertEquals($expected, $result);
    }

    /**
     * Tests that array_sort_by_fields handles an empty array correctly.
     */
    public function test_array_sort_by_fields_empty_array()
    {
        $data = [];

        $expected = [];

        $result = array_sort_by_fields($data, 'volume', SORT_DESC);

        $this->assertEquals($expected, $result);
    }

    /**
     * Tests that array_sort_by_fields does not modify the original array passed as an argument.
     */
    public function test_array_sort_by_fields_does_not_modify_original_array()
    {
        $data = [
            ['volume' => 86, 'edition' => 6],
            ['volume' => 67, 'edition' => 7],
        ];

        $original_data = $data;

        array_sort_by_fields($data, 'volume', SORT_DESC);

        $this->assertEquals($original_data, $data);
    }

    /**
     * Tests that array_sort_by_fields handles sorting by non-existing fields gracefully.
     */
    public function test_array_sort_by_fields_non_existing_field()
    {
        $data = [
            ['volume' => 86, 'edition' => 6],
            ['volume' => 67, 'edition' => 7],
        ];

        // The order should remain the same
        $expected = $data;

        $result = array_sort_by_fields($data, 'non_existing_field', SORT_DESC);

        $this->assertEquals($expected, $result);
    }

    /**
     * Tests that the function increments the count at the start of the range
     * and decrements the count at the end when step is positive.
     */
    public function test_adjust_counts_with_positive_step()
    {
        $counts = [4 => 3, 5 => 5, 6 => 2, 7 => 4];

        // Call the function with step = 1
        $result = adjust_counts($counts, 4, 7, 1);

        // Verify that the function returned 1
        $this->assertEquals(1, $result);

        // Verify that the counts were adjusted
        $this->assertEquals([4 => 3, 5 => 4, 6 => 3, 7 => 4], $counts);
    }

    /**
     * Tests that the function increments the count at the end of the range
     * and decrements the count at the start when step is negative.
     */
    public function test_adjust_counts_with_negative_step()
    {
        $counts = [4 => 3, 5 => 5, 6 => 2, 7 => 4];

        // Call the function with step = -1
        $result = adjust_counts($counts, 7, 4, -1);

        // Verify that the function returned 1
        $this->assertEquals(1, $result);

        // Verify that the counts were adjusted correctly
        // The count for 6 should increase by 1, and the count for 7 should decrease by 1
        $this->assertEquals([4 => 3, 5 => 5, 6 => 3, 7 => 3], $counts);
    }

    /**
     * Test that the function throws an exception when step is zero.
     */
    public function test_adjust_counts_with_zero_step()
    {
        $this->expectException(\InvalidArgumentException::class);

        $counts = [4 => 3, 5 => 2, 6 => 4];

        // Call the function with step = 0, which should trigger an exception
        $result = adjust_counts($counts, 4, 6, 0);

        // The following assertions are redundant since the exception is expected,
        // but they're left here to show what was originally intended.
        $this->assertEquals(0, $result);
        $this->assertEquals([4 => 3, 5 => 2, 6 => 4], $counts);
    }

    /**
     * Tests that the function throws an exception when step is not 1 or -1.
     */
    public function test_adjust_counts_invalid_step()
    {
        $this->expectException(\InvalidArgumentException::class);

        $counts = [4 => 3, 5 => 5, 6 => 2, 7 => 4];

        // Call the function with an invalid step value
        adjust_counts($counts, 4, 7, 2);
    }

    /**
     * Tests that the function returns 0 and makes no changes
     * when no adjustment is needed.
     */
    public function test_adjust_counts_no_adjustment_needed()
    {
        $counts = [4 => 4, 5 => 4, 6 => 4, 7 => 4];

        // Call the function with step = 1
        $result = adjust_counts($counts, 4, 7, 1);

        // Verify that the function returned 0
        $this->assertEquals(0, $result);

        // Verify that the counts were not adjusted
        $this->assertEquals([4 => 4, 5 => 4, 6 => 4, 7 => 4], $counts);
    }

    /**
     * Tests that the function correctly handles edge cases where
     * the range is only one element.
     */
    public function test_adjust_counts_single_element_range()
    {
        $counts = [4 => 4, 5 => 4];

        // Call the function with step = 1 and a single element range
        $result = adjust_counts($counts, 4, 4, 1);

        // Verify that the function returned 0
        $this->assertEquals(0, $result);

        // Verify that the counts were not adjusted
        $this->assertEquals([4 => 4, 5 => 4], $counts);
    }

    /**
     * Test adjustment when counts can be balanced
     */
    public function test_adjust_counts_with_imbalance()
    {
        $counts = [4 => 3, 5 => 2, 6 => 4];
        $result = adjust_counts($counts, 6, 4, -1);

        $this->assertEquals(1, $result);
        $this->assertEquals([4 => 3, 5 => 3, 6 => 3], $counts);
    }

    /**
     * Test no adjustment when counts are already balanced
     */
    public function test_adjust_counts_with_balance()
    {
        $counts = [4 => 3, 5 => 3, 6 => 3];
        $result = adjust_counts($counts, 6, 4, -1);

        $this->assertEquals(0, $result);
        $this->assertEquals([4 => 3, 5 => 3, 6 => 3], $counts);
    }

    /**
     * Test adjustment at the edges of the range.
     */
    public function test_adjust_counts_at_range_edges()
    {
        $counts = [4 => 2, 5 => 3, 6 => 4];

        // Call the function to adjust counts from 6 to 4 with a step of -1
        $result = adjust_counts($counts, 6, 4, -1);

        // Verify that the function returned 1, indicating an adjustment was made
        $this->assertEquals(1, $result);

        // Verify that the counts were adjusted correctly
        // Count at 6 should decrease by 1, and count at 5 should increase by 1
        $this->assertEquals([4 => 2, 5 => 4, 6 => 3], $counts);
    }

    /**
     * Test adjustment with a positive step on non-sequential keys.
     */
    public function test_adjust_counts_with_positive_step_non_sequential_keys()
    {
        $counts = [2 => 4, 5 => 2, 8 => 3];

        // Call the function with step = 1
        $result = adjust_counts($counts, 2, 8, 1);

        // Verify that the function returned 1, indicating an adjustment was made
        $this->assertEquals(1, $result);

        // Verify that the counts were adjusted correctly
        // Count at 2 should decrease by 1, and count at 5 should increase by 1
        $this->assertEquals([2 => 3, 5 => 3, 8 => 3], $counts);
    }

    /**
     * Test adjustment with a negative step on non-sequential keys.
     */
    public function test_adjust_counts_with_negative_step_non_sequential_keys()
    {
        $counts = [2 => 4, 5 => 2, 8 => 3];

        // Call the function with step = -1
        $result = adjust_counts($counts, 8, 2, -1);

        // Verify that the function returned 1, indicating an adjustment was made
        $this->assertEquals(1, $result);

        // Verify that the counts were adjusted correctly
        // Count at 8 should decrease by 1, and count at 5 should increase by 1
        $this->assertEquals([2 => 4, 5 => 3, 8 => 2], $counts);
    }

    /**
     * Test that no adjustment is made when the counts are already balanced with non-sequential keys.
     */
    public function test_adjust_counts_no_adjustment_needed_non_sequential_keys()
    {
        $counts = [2 => 3, 5 => 3, 8 => 3];

        // Call the function with step = 1
        $result = adjust_counts($counts, 2, 8, 1);

        // Verify that the function returned 0, indicating no adjustment was made
        $this->assertEquals(0, $result);

        // Verify that the counts remain unchanged
        $this->assertEquals([2 => 3, 5 => 3, 8 => 3], $counts);
    }

    /**
     * Test that the function handles cases where only the edge keys need adjustment with non-sequential keys.
     */
    public function test_adjust_counts_edge_case_non_sequential_keys()
    {
        $counts = [2 => 2, 5 => 4, 8 => 3];

        // Call the function with step = -1
        $result = adjust_counts($counts, 8, 2, -1);

        // Verify that the function returned 1, indicating an adjustment was made
        $this->assertEquals(1, $result);

        // Verify that the counts were adjusted correctly
        // Count at 5 should decrease by 1, and count at 2 should increase by 1
        $this->assertEquals([2 => 3, 5 => 3, 8 => 3], $counts);
    }

    /**
     * Tests that the function throws an exception when count is zero or negative.
     */
    public function test_generate_balanced_array_throws_exception_for_invalid_count()
    {
        $this->expectException(\InvalidArgumentException::class);

        // Call the function with count = 0
        generate_balanced_array(0, 4, 8);
    }

    /**
     * Tests that the function throws an exception when min is greater than or equal to max.
     */
    public function test_generate_balanced_array_throws_exception_for_invalid_min_max()
    {
        $this->expectException(\InvalidArgumentException::class);

        // Call the function with min >= max
        generate_balanced_array(10, 8, 4);
    }

    /**
     * Tests that the function generates an evenly distributed array
     * when count is exactly divisible by the range.
     */
    public function test_generate_balanced_array_generates_even_distribution()
    {
        $result = generate_balanced_array(20, 4, 8);

        // Count the occurrences of each number in the result
        $counts = array_count_values($result);

        // Verify that the distribution is even
        $this->assertEquals([4 => 4, 5 => 4, 6 => 4, 7 => 4, 8 => 4], $counts);

        // Verify that the weighted sum is correct
        $weighted_sum = array_sum(array_map(function ($k, $v) {
            return $k * $v;
        }, array_keys($counts), $counts));
        $this->assertEquals(120, $weighted_sum);
    }

    /**
     * Tests that the function generates an evenly distributed array
     * when the count is not exactly divisible by the range.
     */
    public function test_generate_balanced_array_generates_distribution_with_remainder()
    {
        $result = generate_balanced_array(23, 4, 8);

        // Count the occurrences of each number in the result
        $counts = array_count_values($result);

        // Verify that the total count is correct
        $this->assertEquals(23, array_sum($counts));

        // Verify that the weighted sum is correct
        $weighted_sum = array_sum(array_map(function ($k, $v) {
            return $k * $v;
        }, array_keys($counts), $counts));
        $target_sum = (int) ((4 + 8) / 2 * 23);
        $this->assertEquals($target_sum, $weighted_sum);
    }

    /**
     * Tests that the function handles small ranges correctly.
     */
    public function test_generate_balanced_array_handles_small_ranges()
    {
        $result = generate_balanced_array(7, 4, 6);

        // Count the occurrences of each number in the result
        $counts = array_count_values($result);

        // Verify that the distribution is correct
        $this->assertEquals(7, array_sum($counts));

        // Verify that the weighted sum is correct
        $weighted_sum = array_sum(array_map(function ($k, $v) {
            return $k * $v;
        }, array_keys($counts), $counts));
        $target_sum = (int) ((4 + 6) / 2 * 7);
        $this->assertEquals($target_sum, $weighted_sum);
    }

    /**
     * Tests that the function handles edge cases where the range is very small.
     */
    public function test_generate_balanced_array_handles_edge_cases()
    {
        $result = generate_balanced_array(10, 5, 5);

        // Count the occurrences of each number in the result
        $counts = array_count_values($result);

        // Verify that all values are the same
        $this->assertEquals([5 => 10], $counts);

        // Verify that the weighted sum is correct
        $weighted_sum = array_sum(array_map(function ($k, $v) {
            return $k * $v;
        }, array_keys($counts), $counts));
        $this->assertEquals(50, $weighted_sum);
    }

    /**
     * Tests that the function returns TRUE when the variable is set and is an array.
     */
    public function test_isset_array_with_array()
    {
        $array = [1, 2, 3];

        // Call the function with an array
        $result = isset_array($array);

        // Verify that the function returns TRUE
        $this->assertTrue($result);
    }

    /**
     * Tests that the function returns FALSE when the variable is set but is not an array.
     */
    public function test_isset_array_with_non_array()
    {
        $nonArray = 123;

        // Call the function with a non-array
        $result = isset_array($nonArray);

        // Verify that the function returns FALSE
        $this->assertFalse($result);
    }

    /**
     * Tests that the function returns FALSE when the variable is NULL.
     */
    public function test_isset_array_with_null()
    {
        $nullValue = NULL;

        // Call the function with a NULL value
        $result = isset_array($nullValue);

        // Verify that the function returns FALSE
        $this->assertFalse($result);
    }

    /**
     * Tests that the function returns FALSE when the variable is not set.
     */
    public function test_isset_array_with_undefined_variable()
    {
        // Declare the variable as null
        $undefinedVar = null;

        // Call the function
        $result = isset_array(@$undefinedVar);

        // Verify that the function returns FALSE
        $this->assertFalse($result);
    }

    /**
     * Tests that the function returns TRUE for an empty array.
     */
    public function test_isset_array_with_empty_array()
    {
        $emptyArray = [];

        // Call the function with an empty array
        $result = isset_array($emptyArray);

        // Verify that the function returns TRUE
        $this->assertTrue($result);
    }

    /**
     * Tests that the function returns FALSE for an empty string.
     */
    public function test_isset_array_with_empty_string()
    {
        $emptyString = '';

        // Call the function with an empty string
        $result = isset_array($emptyString);

        // Verify that the function returns FALSE
        $this->assertFalse($result);
    }

    /**
     * Tests that the function returns the correct substring when a match is found.
     */
    public function test_strstr_array_returns_substring_on_match()
    {
        $haystack = "This is a test string.";
        $needles = ["test", "string", "example"];

        // Call the function
        $result = strstr_array($haystack, $needles);

        // Verify that the function returns the correct substring
        $this->assertEquals("test string.", $result);
    }

    /**
     * Tests that the function returns FALSE when no match is found.
     */
    public function test_strstr_array_returns_false_on_no_match()
    {
        $haystack = "This is a test string.";
        $needles = ["example", "dummy", "placeholder"];

        // Call the function
        $result = strstr_array($haystack, $needles);

        // Verify that the function returns FALSE
        $this->assertFalse($result);
    }

    /**
     * Tests that the function returns the correct part of the string before the needle when $before_needle is TRUE.
     */
    public function test_strstr_array_returns_before_needle_on_match()
    {
        $haystack = "This is a test string.";
        $needles = ["test", "string", "example"];

        // Call the function with before_needle = TRUE
        $result = strstr_array($haystack, $needles, TRUE);

        // Verify that the function returns the part of the string before the needle
        $this->assertEquals("This is a ", $result);
    }

    /**
     * Tests that the function handles an empty array of needles.
     */
    public function test_strstr_array_with_empty_needles()
    {
        $haystack = "This is a test string.";
        $needles = [];

        // Call the function with an empty needles array
        $result = strstr_array($haystack, $needles);

        // Verify that the function returns FALSE since no needles were provided
        $this->assertFalse($result);
    }

    /**
     * Tests that the function returns the first match found in the needles array.
     */
    public function test_strstr_array_returns_first_match()
    {
        $haystack = "This is a test string.";
        $needles = ["string", "test", "is"];

        // Call the function
        $result = strstr_array($haystack, $needles);

        // Verify that the function returns the first match found
        $this->assertEquals("string.", $result);
    }

    /**
     * Tests that the function handles a case where the needle appears multiple times.
     */
    public function test_strstr_array_with_multiple_occurrences()
    {
        $haystack = "This is a test string with test cases.";
        $needles = ["test", "cases"];

        // Call the function
        $result = strstr_array($haystack, $needles);

        // Verify that the function returns the first occurrence
        $this->assertEquals("test string with test cases.", $result);
    }

    /**
     * Tests that the function returns the correct substring
     * when the first, second, and fourth needles are not present,
     * but the third needle is present, with $before_needle = FALSE.
     */
    public function test_strstr_array_third_needle_match_before_needle_false()
    {
        $haystack = "This is a test string for unit testing.";
        $needles = ["absent", "missing", "test", "none"];

        // Call the function with $before_needle = FALSE
        $result = strstr_array($haystack, $needles, FALSE);

        // Verify that the function returns the correct substring starting from the third needle
        $this->assertEquals("test string for unit testing.", $result);
    }

    /**
     * Tests that the function returns the part of the string before the third needle
     * when the first, second, and fourth needles are not present,
     * but the third needle is present, with $before_needle = TRUE.
     */
    public function test_strstr_array_third_needle_match_before_needle_true()
    {
        $haystack = "This is a test string for unit testing.";
        $needles = ["absent", "missing", "test", "none"];

        // Call the function with $before_needle = TRUE
        $result = strstr_array($haystack, $needles, TRUE);

        // Verify that the function returns the part of the string before the third needle
        $this->assertEquals("This is a ", $result);
    }

    /**
     * Tests that the function returns the correct substring
     * when a match is found, ignoring case.
     */
    public function test_stristr_array_returns_substring_on_match()
    {
        $haystack = "This is a Test String.";
        $needles = ["test", "string", "example"];

        // Call the function
        $result = stristr_array($haystack, $needles);

        // Verify that the function returns the correct substring, ignoring case
        $this->assertEquals("Test String.", $result);
    }

    /**
     * Tests that the function returns FALSE when no match is found.
     */
    public function test_stristr_array_returns_false_on_no_match()
    {
        $haystack = "This is a Test String.";
        $needles = ["example", "dummy", "placeholder"];

        // Call the function
        $result = stristr_array($haystack, $needles);

        // Verify that the function returns FALSE
        $this->assertFalse($result);
    }

    /**
     * Tests that the function returns the part of the string before the needle
     * when $before_needle is TRUE.
     */
    public function test_stristr_array_returns_before_needle_on_match()
    {
        $haystack = "This is a Test String.";
        $needles = ["test", "string", "example"];

        // Call the function with $before_needle = TRUE
        $result = stristr_array($haystack, $needles, TRUE);

        // Verify that the function returns the part of the string before the needle, ignoring case
        $this->assertEquals("This is a ", $result);
    }

    /**
     * Tests that the function handles an empty array of needles.
     */
    public function test_stristr_array_with_empty_needles()
    {
        $haystack = "This is a Test String.";
        $needles = [];

        // Call the function with an empty needles array
        $result = stristr_array($haystack, $needles);

        // Verify that the function returns FALSE since no needles were provided
        $this->assertFalse($result);
    }

    /**
     * Tests that the function returns the first match found in the needles array.
     */
    public function test_stristr_array_returns_first_match()
    {
        $haystack = "This is a Test String.";
        $needles = ["string", "test", "is"];

        // Call the function
        $result = stristr_array($haystack, $needles);

        // Verify that the function returns the first match found, ignoring case
        $this->assertEquals("String.", $result);
    }

    /**
     * Tests that the function handles a case where the needle appears multiple times.
     */
    public function test_stristr_array_with_multiple_occurrences()
    {
        $haystack = "This is a test string with test cases.";
        $needles = ["test", "cases"];

        // Call the function
        $result = stristr_array($haystack, $needles);

        // Verify that the function returns the first occurrence, ignoring case
        $this->assertEquals("test string with test cases.", $result);
    }

    /**
     * Tests for is_indexed_array function
     */
    public function test_is_indexed_array_with_sequential_array()
    {
        $this->assertTrue(is_indexed_array([1, 2, 3]));
    }

    public function test_is_indexed_array_with_non_sequential_array()
    {
        $this->assertTrue(is_indexed_array([5 => 1, 10 => 2, 15 => 3]));
    }

    public function test_is_indexed_array_with_associative_array()
    {
        $this->assertFalse(is_indexed_array(['a' => 1, 'b' => 2]));
    }

    public function test_is_indexed_array_with_mixed_array()
    {
        $this->assertFalse(is_indexed_array([1, 'a' => 2, 3]));
    }

    public function test_is_indexed_array_with_empty_array()
    {
        $this->assertTrue(is_indexed_array([]));
    }

    public function test_is_indexed_array_with_nested_array()
    {
        $this->assertFalse(is_indexed_array([1, [2, 3], 4]));
    }

    /**
     * Tests for is_column_based_array function
     */
    public function test_is_column_based_array_with_valid_array()
    {
        $input = [
            'name' => ['Alice', 'Bob'],
            'age' => [30, 25]
        ];
        $this->assertTrue(is_column_based_array($input));
    }

    public function test_is_column_based_array_with_empty_array()
    {
        $this->assertFalse(is_column_based_array([]));
    }

    public function test_is_column_based_array_with_indexed_array()
    {
        $this->assertFalse(is_column_based_array([1, 2, 3]));
    }

    public function test_is_column_based_array_with_inconsistent_lengths()
    {
        $input = [
            'name' => ['Alice', 'Bob'],
            'age' => [30, 25, 35]
        ];
        $this->assertFalse(is_column_based_array($input));
    }

    public function test_is_column_based_array_with_nested_arrays()
    {
        $input = [
            'name' => ['Alice', 'Bob'],
            'data' => [[1, 2], [3, 4]]
        ];
        $this->assertFalse(is_column_based_array($input));
    }

    /**
     * Tests for is_row_based_array function
     */
    public function test_is_row_based_array_with_valid_array()
    {
        $input = [
            ['name' => 'Alice', 'age' => 30],
            ['name' => 'Bob', 'age' => 25]
        ];
        $this->assertTrue(is_row_based_array($input));
    }

    public function test_is_row_based_array_with_empty_array()
    {
        $this->assertFalse(is_row_based_array([]));
    }

    public function test_is_row_based_array_with_inconsistent_keys()
    {
        $input = [
            ['name' => 'Alice', 'age' => 30],
            ['name' => 'Bob', 'height' => 180]
        ];
        $this->assertFalse(is_row_based_array($input));
    }

    public function test_is_row_based_array_with_nested_arrays()
    {
        $input = [
            ['name' => 'Alice', 'data' => [1, 2]],
            ['name' => 'Bob', 'data' => [3, 4]]
        ];
        $this->assertFalse(is_row_based_array($input));
    }

    /**
     * Test converting an empty array.
     */
    public function test_convert_array_to_row_structure_empty_array()
    {
        $input = [];
        $expected = [];
        $this->assertEquals($expected, convert_array_to_row_structure($input));
    }

    /**
     * Test converting a column-based array.
     */
    public function test_convert_array_to_row_structure_column_based()
    {
        $input = [
            'name' => ['Alice', 'Bob', 'Charlie'],
            'age' => [25, 30, 35],
            'city' => ['New York', 'London', 'Paris']
        ];
        $expected = [
            ['name' => 'Alice', 'age' => 25, 'city' => 'New York'],
            ['name' => 'Bob', 'age' => 30, 'city' => 'London'],
            ['name' => 'Charlie', 'age' => 35, 'city' => 'Paris']
        ];
        $this->assertEquals($expected, convert_array_to_row_structure($input));
    }

    /**
     * Test converting a row-based array.
     */
    public function test_convert_array_to_row_structure_row_based()
    {
        $input = [
            ['name' => 'Alice', 'age' => 25, 'city' => 'New York'],
            ['name' => 'Bob', 'age' => 30, 'city' => 'London'],
            ['name' => 'Charlie', 'age' => 35, 'city' => 'Paris']
        ];
        $this->assertEquals($input, convert_array_to_row_structure($input));
    }

    /**
     * Test converting an indexed array.
     */
    public function test_convert_array_to_row_structure_indexed()
    {
        $input = [1, 2, 3, 4, 5];
        $expected = $input;
        $this->assertEquals($expected, convert_array_to_row_structure($input));
    }

    /**
     * Test converting a column-based array with missing values.
     */
    public function test_convert_array_to_row_structure_column_based_missing_values()
    {
        // Explicitly represent missing value
        $input = [
            'name' => ['Alice', 'Bob', ''],
            'age' => [25, 30, 35],
            'city' => ['New York', 'London', 'Paris']
        ];
        $expected = [
            ['name' => 'Alice', 'age' => 25, 'city' => 'New York'],
            ['name' => 'Bob', 'age' => 30, 'city' => 'London'],
            ['name' => '', 'age' => 35, 'city' => 'Paris']
        ];
        $this->assertEquals($expected, convert_array_to_row_structure($input));
    }

    /**
     * Test converting a column-based array with empty strings.
     */
    public function test_convert_array_to_row_structure_column_based_empty_strings()
    {
        $input = [
            'name' => ['Alice', '', 'Charlie'],
            'age' => [25, 30, ''],
            'city' => ['New York', 'London', '']
        ];
        $expected = [
            ['name' => 'Alice', 'age' => 25, 'city' => 'New York'],
            ['name' => '', 'age' => 30, 'city' => 'London'],
            ['name' => 'Charlie', 'age' => '', 'city' => '']
        ];
        $this->assertEquals($expected, convert_array_to_row_structure($input));
    }

    /**
     * Test converting a "dirty" array with mixed types.
     */
    public function test_convert_array_to_row_structure_dirty_array()
    {
        $input = [
            'col1' => [1, '2', null, false],
            'col2' => ['a', 'b', 'c', 'd']
        ];
        $expected = [
            ['col1' => 1, 'col2' => 'a'],
            ['col1' => '2', 'col2' => 'b'],
            ['col1' => null, 'col2' => 'c'],
            ['col1' => false, 'col2' => 'd']
        ];
        $this->assertEquals($expected, convert_array_to_row_structure($input));
    }

    /**
     * Test error scenario with invalid array structure.
     */
    public function test_convert_array_to_row_structure_invalid_structure()
    {
        $input = [
            'key1' => 'value1',
            'key2' => 'value2'
        ];
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Invalid array structure. Must be indexed, column-based, or row-based.');
        convert_array_to_row_structure($input);
    }

    /**
     * Test error scenario with nested arrays.
     */
    public function test_convert_array_to_row_structure_nested_arrays()
    {
        $input = [
            'col1' => [1, [2, 3], 4],
            'col2' => ['a', 'b', 'c']
        ];
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Invalid array structure. Must be indexed, column-based, or row-based.');
        convert_array_to_row_structure($input);
    }

    /**
     * Tests for get_csv_headers function
     */
    public function test_get_csv_headers_with_column_based_array()
    {
        $input = [
            'name' => ['Alice', 'Bob'],
            'age' => [30, 25]
        ];
        $expected = ['name', 'age'];
        $this->assertEquals($expected, get_csv_headers($input, true));
    }

    public function test_get_csv_headers_with_row_based_array()
    {
        $input = [
            ['name' => 'Alice', 'age' => 30],
            ['name' => 'Bob', 'age' => 25]
        ];
        $expected = ['name', 'age'];
        $this->assertEquals($expected, get_csv_headers($input, false));
    }

    public function test_get_csv_headers_with_empty_array()
    {
        $this->assertEquals([], get_csv_headers([], true));
        $this->assertEquals([], get_csv_headers([], false));
    }

    /**
     * Tests for write_csv_row function
     */
    public function test_write_csv_row()
    {
        $handle = fopen('php://temp', 'r+');
        $row = ['Alice', 30, 'New York'];
        write_csv_row($handle, $row, ',', '"', '\\');
        rewind($handle);
        $this->assertEquals("Alice,30,\"New York\"\n", fgets($handle));
        fclose($handle);
    }

    public function test_write_csv_row_with_empty_row()
    {
        $handle = fopen('php://temp', 'r+');
        write_csv_row($handle, [], ',', '"', '\\');
        rewind($handle);
        $this->assertEquals("\n", fgets($handle));
        fclose($handle);
    }

    /**
     * Tests for write_column_based_data function
     */
    public function test_write_column_based_data()
    {
        $handle = fopen('php://temp', 'r+');
        $data = [
            'name' => ['Alice', 'Bob'],
            'age' => [30, 25]
        ];
        write_column_based_data($handle, $data, ',', '"', '\\');
        rewind($handle);
        $expected = "Alice,30\nBob,25\n";
        $this->assertEquals($expected, stream_get_contents($handle));
        fclose($handle);
    }

    public function test_write_column_based_data_with_missing_values()
    {
        $handle = fopen('php://temp', 'r+');
        $data = [
            'name' => ['Alice', 'Bob'],
            'age' => [30]
        ];
        write_column_based_data($handle, $data, ',', '"', '\\');
        rewind($handle);
        $expected = "Alice,30\nBob,\n";
        $this->assertEquals($expected, stream_get_contents($handle));
        fclose($handle);
    }

    /**
     * Tests for write_row_based_data function
     */
    public function test_write_row_based_data()
    {
        $handle = fopen('php://temp', 'r+');
        $data = [
            ['name' => 'Alice', 'age' => 30],
            ['name' => 'Bob', 'age' => 25]
        ];
        write_row_based_data($handle, $data, ',', '"', '\\');
        rewind($handle);
        $expected = "Alice,30\nBob,25\n";
        $this->assertEquals($expected, stream_get_contents($handle));
        fclose($handle);
    }

    public function test_write_row_based_data_with_invalid_row()
    {
        $handle = fopen('php://temp', 'r+');
        $data = [
            ['name' => 'Alice', 'age' => 30],
            'Invalid Row'
        ];
        $this->expectException(\InvalidArgumentException::class);
        write_row_based_data($handle, $data, ',', '"', '\\');
        fclose($handle);
    }

    /**
     * Tests for array_to_csv function
     */
    public function test_array_to_csv_with_indexed_array()
    {
        $input = [1, 2, 3, 4, 5];
        $expected = "1,2,3,4,5\n";
        $this->assertEquals($expected, array_to_csv($input));
    }

    public function test_array_to_csv_with_column_based_array()
    {
        $input = [
            'name' => ['Alice', 'Bob'],
            'age' => [30, 25]
        ];
        $expected = "name,age\nAlice,30\nBob,25\n";
        $this->assertEquals($expected, array_to_csv($input));
    }

    public function test_array_to_csv_with_row_based_array()
    {
        $input = [
            ['name' => 'Alice', 'age' => 30],
            ['name' => 'Bob', 'age' => 25]
        ];
        $expected = "name,age\nAlice,30\nBob,25\n";
        $this->assertEquals($expected, array_to_csv($input));
    }

    public function test_array_to_csv_with_empty_array()
    {
        $this->assertEquals('', array_to_csv([]));
    }

    public function test_array_to_csv_with_custom_delimiter()
    {
        $input = ['a', 'b', 'c'];
        $expected = "a;b;c\n";
        $this->assertEquals($expected, array_to_csv($input, ';'));
    }

    public function test_array_to_csv_without_headers()
    {
        $input = [
            ['name' => 'Alice', 'age' => 30],
            ['name' => 'Bob', 'age' => 25]
        ];
        $expected = "Alice,30\nBob,25\n";
        $this->assertEquals($expected, array_to_csv($input, ',', '"', '\\', false));
    }

    public function test_array_to_csv_with_dirty_array()
    {
        $input = [
            'a' => 1,
            'b' => [2, 3],
            'c' => 4
        ];
        $this->expectException(\InvalidArgumentException::class);
        array_to_csv($input);
    }

    /**
     * Test basic functionality with a simple indexed array
     */
    public function test_align_array_columns_simple_indexed_array()
    {
        $input = ['Alice', 25, 'New York', 'Bob', 30, 'London'];
        $expected = "Alice 25 New York Bob 30 London";
        $this->assertEquals($expected, align_array_columns($input));
    }

    /**
     * Test with a row-based array
     */
    public function test_align_array_columns_row_based_array()
    {
        $input = [
            ['name' => 'Alice', 'age' => 25, 'city' => 'New York'],
            ['name' => 'Bob', 'age' => 30, 'city' => 'London'],
        ];
        $expected = "Alice 25 New York" . PHP_EOL . "Bob   30 London  ";
        $this->assertEquals($expected, align_array_columns($input));
    }

    /**
     * Test with a column-based array
     */
    public function test_align_array_columns_column_based_array()
    {
        $input = [
            'name' => ['Alice', 'Bob'],
            'age' => [25, 30],
            'city' => ['New York', 'London'],
        ];
        $expected = "Alice 25 New York" . PHP_EOL . "Bob   30 London  ";
        $this->assertEquals($expected, align_array_columns($input));
    }

    /**
     * Test with mixed data types including null values
     */
    public function test_align_array_columns_mixed_data_types()
    {
        $input = [
            ['name' => 'Alice', 'age' => null, 'active' => true],
            ['name' => 'Bob', 'age' => 30, 'active' => false],
        ];
        $expected = "Alice    1" . PHP_EOL . "Bob   30  ";
        $this->assertEquals($expected, align_array_columns($input));
    }

    /**
     * Test with an empty array
     */
    public function test_align_array_columns_empty_array()
    {
        $this->assertEquals('', align_array_columns([]));
    }

    /**
     * Test with a single-element array
     */
    public function test_align_array_columns_single_element_array()
    {
        $input = ['Alice'];
        $expected = "Alice";
        $this->assertEquals($expected, align_array_columns($input));
    }

    /**
     * Test with very long strings
     */
    public function test_align_array_columns_long_strings()
    {
        $input = [
            ['name' => 'Alice', 'description' => str_repeat('a', 1000)],
            ['name' => 'Bob', 'description' => str_repeat('b', 500)],
        ];
        $expected = "Alice " . str_repeat('a', 1000) . PHP_EOL .
            "Bob   " . str_repeat('b', 500) . str_repeat(' ', 500);
        $this->assertEquals($expected, align_array_columns($input));
    }

    /**
     * Test with different alignment options
     */
    public function test_align_array_columns_alignment_options()
    {
        $input = [
            ['name' => 'Alice', 'age' => 25],
            ['name' => 'Bob', 'age' => 30],
        ];

        // Test left alignment (default)
        $expected_left = "Alice 25" . PHP_EOL . "Bob   30";
        $this->assertEquals($expected_left, align_array_columns($input));

        // Test right alignment
        $expected_right = "Alice 25" . PHP_EOL . "  Bob 30";
        $this->assertEquals($expected_right, align_array_columns($input, 'right'));

        // Test center alignment
        $expected_center = "Alice 25" . PHP_EOL . " Bob  30";
        $this->assertEquals($expected_center, align_array_columns($input, 'center'));
    }

    /**
     * Test with multibyte characters
     */
    public function test_align_array_columns_multibyte_characters()
    {
        $input = [
            ['name' => '中文', 'greeting' => 'こんにちは'],
            ['name' => 'Alice', 'greeting' => 'Hello'],
        ];
        $expected = "中文  こんにちは" . PHP_EOL . "Alice Hello     ";
        $this->assertEquals($expected, align_array_columns($input));
    }

    /**
     * Test with inconsistent array structures (dirty case)
     */
    public function test_align_array_columns_inconsistent_structures()
    {
        $input = [
            ['name' => 'Alice', 'age' => 25],
            ['name' => 'Bob', 'city' => 'London'],
        ];
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Invalid array structure. Must be indexed, column-based, or row-based.');
        align_array_columns($input);
    }

    /**
     * Test with nested arrays (edge case)
     */
    public function test_align_array_columns_nested_arrays()
    {
        $input = [
            ['name' => 'Alice', 'details' => ['age' => 25, 'city' => 'New York']],
            ['name' => 'Bob', 'details' => ['age' => 30, 'city' => 'London']],
        ];
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Invalid array structure. Must be indexed, column-based, or row-based.');
        align_array_columns($input);
    }

    /**
     * Test with invalid alignment option (error scenario)
     */
    public function test_align_array_columns_invalid_alignment()
    {
        $input = [['name' => 'Alice']];
        $this->expectException(\InvalidArgumentException::class);
        align_array_columns($input, 'invalid_alignment');
    }

    /**
     * Test with a valid row-based array to ensure basic functionality still works
     */
    public function test_align_array_columns_valid_row_based_array()
    {
        $input = [
            ['name' => 'Alice', 'age' => 25, 'city' => 'New York'],
            ['name' => 'Bob', 'age' => 30, 'city' => 'London'],
        ];
        $expected = "Alice 25 New York" . PHP_EOL . "Bob   30 London  ";
        $this->assertEquals($expected, align_array_columns($input));
    }
}
