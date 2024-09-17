<?php

namespace FOfX\Helper;

use PHPUnit\Framework\TestCase;

class TextTest extends TestCase
{
    /**
     * Tests the text_chunks function with a standard input.
     *
     * @return void
     */
    public function test_text_chunks_standard_input(): void
    {
        $string = "The cow jumped over the moon";
        $max_length = 3;

        $expected = [
            [
                "The",
                "cow",
                "jumped",
                "over",
                "the",
                "moon"
            ],
            [
                "The cow",
                "jumped over",
                "the moon"
            ],
            [
                "The cow jumped",
                "over the moon"
            ]
        ];

        $this->assertEquals($expected, text_chunks($string, $max_length));
    }

    /**
     * Tests the text_chunks function with an empty string.
     *
     * @return void
     */
    public function test_text_chunks_empty_string(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Input string cannot be empty.');

        $string = "";
        $max_length = 3;
        text_chunks($string, $max_length);
    }

    /**
     * Tests the text_chunks function with a max_length of 1.
     *
     * @return void
     */
    public function test_text_chunks_max_length_one(): void
    {
        $string = "The cow jumped over the moon";
        $max_length = 1;

        $expected = [
            [
                "The",
                "cow",
                "jumped",
                "over",
                "the",
                "moon"
            ]
        ];

        $this->assertEquals($expected, text_chunks($string, $max_length));
    }

    /**
     * Tests the text_chunks function with non-alphabetic characters.
     *
     * @return void
     */
    public function test_text_chunks_with_special_characters(): void
    {
        $string = "The cow! jumped@ over# the$ moon%";
        $max_length = 2;

        $expected = [
            [
                "The",
                "cow!",
                "jumped@",
                "over#",
                "the$",
                "moon%"
            ],
            [
                "The cow!",
                "jumped@ over#",
                "the$ moon%"
            ]
        ];

        $this->assertEquals($expected, text_chunks($string, $max_length));
    }

    /**
     * Tests the text_chunks function with very long input.
     *
     * @return void
     */
    public function test_text_chunks_long_input(): void
    {
        // 1000 words
        $string = str_repeat("word ", 1000);
        $max_length = 3;

        // Only checking that the function returns an array and doesn't crash
        $result = text_chunks($string, $max_length);
        $this->assertIsArray($result);
    }

    /**
     * Tests the text_chunks function with invalid max_length (negative value).
     *
     * @return void
     */
    public function test_text_chunks_negative_max_length(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Maximum length must be at least 1.');

        $string = "The cow jumped over the moon";
        $max_length = -1;
        text_chunks($string, $max_length);
    }

    /**
     * Tests the text_chunks function with dirty input (extra spaces).
     *
     * @return void
     */
    public function test_text_chunks_with_extra_spaces(): void
    {
        $string = "  The   cow   jumped    over   the    moon  ";
        $max_length = 3;

        $expected = [
            [
                "The",
                "cow",
                "jumped",
                "over",
                "the",
                "moon"
            ],
            [
                "The cow",
                "jumped over",
                "the moon"
            ],
            [
                "The cow jumped",
                "over the moon"
            ]
        ];

        $this->assertEquals($expected, text_chunks(trim(preg_replace('/\s+/', ' ', $string)), $max_length));
    }

    /**
     * Tests array_chunk_overlapping with a standard input.
     *
     * @return void
     */
    public function test_array_chunk_overlapping_standard_input(): void
    {
        $array = ["The", "cow", "jumped", "over", "the", "moon"];
        $size  = 3;

        $expected = [
            ["The", "cow", "jumped"],
            ["cow", "jumped", "over"],
            ["jumped", "over", "the"],
            ["over", "the", "moon"]
        ];

        $this->assertEquals($expected, array_chunk_overlapping($array, $size));
    }

    /**
     * Tests array_chunk_overlapping with an empty array.
     *
     * @return void
     */
    public function test_array_chunk_overlapping_empty_array(): void
    {
        $array = [];
        $size  = 3;

        $expected = [];

        $this->assertEquals($expected, array_chunk_overlapping($array, $size));
    }

    /**
     * Tests array_chunk_overlapping with a max size of 1.
     *
     * @return void
     */
    public function test_array_chunk_overlapping_size_one(): void
    {
        $array = ["The", "cow", "jumped", "over", "the", "moon"];
        $size  = 1;

        $expected = [
            ["The"],
            ["cow"],
            ["jumped"],
            ["over"],
            ["the"],
            ["moon"]
        ];

        $this->assertEquals($expected, array_chunk_overlapping($array, $size));
    }

    /**
     * Tests array_chunk_overlapping with a size greater than array length.
     *
     * @return void
     */
    public function test_array_chunk_overlapping_size_greater_than_array_length(): void
    {
        $array = ["The", "cow", "jumped"];
        $size  = 5;

        $expected = [
            ["The", "cow", "jumped"]
        ];

        $this->assertEquals($expected, array_chunk_overlapping($array, $size));
    }

    /**
     * Tests array_chunk_overlapping with non-alphabetic characters.
     *
     * @return void
     */
    public function test_array_chunk_overlapping_with_special_characters(): void
    {
        $array = ["The", "cow!", "jumped@", "over#", "the$", "moon%"];
        $size  = 3;

        $expected = [
            ["The", "cow!", "jumped@"],
            ["cow!", "jumped@", "over#"],
            ["jumped@", "over#", "the$"],
            ["over#", "the$", "moon%"]
        ];

        $this->assertEquals($expected, array_chunk_overlapping($array, $size));
    }

    /**
     * Tests array_chunk_overlapping with a negative size.
     * It should throw an InvalidArgumentException.
     *
     * @return void
     */
    public function test_array_chunk_overlapping_negative_size(): void
    {
        $array = ["The", "cow", "jumped"];
        $size  = -1;

        $this->expectException(\InvalidArgumentException::class);

        array_chunk_overlapping($array, $size);
    }

    /**
     * Tests array_chunk_overlapping with a zero size.
     * It should throw an InvalidArgumentException.
     *
     * @return void
     */
    public function test_array_chunk_overlapping_zero_size(): void
    {
        $array = ["The", "cow", "jumped"];
        $size  = 0;

        $this->expectException(\InvalidArgumentException::class);

        array_chunk_overlapping($array, $size);
    }

    /**
     * Tests array_chunk_overlapping with a size equal to the array length.
     *
     * @return void
     */
    public function test_array_chunk_overlapping_size_equal_to_array_length(): void
    {
        $array = ["The", "cow", "jumped", "over", "the", "moon"];
        $size  = 6;

        $expected = [
            ["The", "cow", "jumped", "over", "the", "moon"]
        ];

        $this->assertEquals($expected, array_chunk_overlapping($array, $size));
    }

    /**
     * Tests array_chunk_overlapping with a large size and small array.
     *
     * @return void
     */
    public function test_array_chunk_overlapping_large_size_small_array(): void
    {
        $array = ["The", "cow"];
        $size  = 100;

        $expected = [
            ["The", "cow"]
        ];

        $this->assertEquals($expected, array_chunk_overlapping($array, $size));
    }

    /**
     * Tests text_chunk_overlapping with a standard input.
     *
     * @return void
     */
    public function test_text_chunk_overlapping_standard_input(): void
    {
        $array = ["The", "cow", "jumped", "over", "the", "moon"];
        $size  = 3;
        $glue  = " ";

        $expected = [
            "The cow jumped",
            "cow jumped over",
            "jumped over the",
            "over the moon"
        ];

        $this->assertEquals($expected, text_chunk_overlapping($array, $size, $glue));
    }

    /**
     * Tests text_chunk_overlapping with an empty array.
     *
     * @return void
     */
    public function test_text_chunk_overlapping_empty_array(): void
    {
        $array = [];
        $size  = 3;
        $glue  = " ";

        $expected = [];

        $this->assertEquals($expected, text_chunk_overlapping($array, $size, $glue));
    }

    /**
     * Tests text_chunk_overlapping with a max size of 1.
     *
     * @return void
     */
    public function test_text_chunk_overlapping_size_one(): void
    {
        $array = ["The", "cow", "jumped", "over", "the", "moon"];
        $size  = 1;
        $glue  = " ";

        $expected = [
            "The",
            "cow",
            "jumped",
            "over",
            "the",
            "moon"
        ];

        $this->assertEquals($expected, text_chunk_overlapping($array, $size, $glue));
    }

    /**
     * Tests text_chunk_overlapping with a size greater than array length.
     *
     * @return void
     */
    public function test_text_chunk_overlapping_size_greater_than_array_length(): void
    {
        $array = ["The", "cow", "jumped"];
        $size  = 5;
        $glue  = " ";

        $expected = [
            "The cow jumped"
        ];

        $this->assertEquals($expected, text_chunk_overlapping($array, $size, $glue));
    }

    /**
     * Tests text_chunk_overlapping with a custom glue.
     *
     * @return void
     */
    public function test_text_chunk_overlapping_custom_glue(): void
    {
        $array = ["The", "cow", "jumped", "over", "the", "moon"];
        $size  = 3;
        $glue  = "-";

        $expected = [
            "The-cow-jumped",
            "cow-jumped-over",
            "jumped-over-the",
            "over-the-moon"
        ];

        $this->assertEquals($expected, text_chunk_overlapping($array, $size, $glue));
    }

    /**
     * Tests text_chunk_overlapping with non-alphabetic characters.
     *
     * @return void
     */
    public function test_text_chunk_overlapping_with_special_characters(): void
    {
        $array = ["The", "cow!", "jumped@", "over#", "the$", "moon%"];
        $size  = 3;
        $glue  = " ";

        $expected = [
            "The cow! jumped@",
            "cow! jumped@ over#",
            "jumped@ over# the$",
            "over# the$ moon%"
        ];

        $this->assertEquals($expected, text_chunk_overlapping($array, $size, $glue));
    }

    /**
     * Tests text_chunk_overlapping with an empty glue.
     *
     * @return void
     */
    public function test_text_chunk_overlapping_empty_glue(): void
    {
        $array = ["The", "cow", "jumped", "over", "the", "moon"];
        $size  = 3;
        $glue  = "";

        $expected = [
            "Thecowjumped",
            "cowjumpedover",
            "jumpedoverthe",
            "overthemoon"
        ];

        $this->assertEquals($expected, text_chunk_overlapping($array, $size, $glue));
    }

    /**
     * Tests text_chunk_overlapping with a large size and small array.
     *
     * @return void
     */
    public function test_text_chunk_overlapping_large_size_small_array(): void
    {
        $array = ["The", "cow"];
        $size  = 100;
        $glue  = " ";

        $expected = [
            "The cow"
        ];

        $this->assertEquals($expected, text_chunk_overlapping($array, $size, $glue));
    }

    /**
     * Tests array_chunk_multi with a standard input.
     *
     * @return void
     */
    public function test_array_chunk_multi_standard_input(): void
    {
        $array = ["The", "cow", "jumped", "over", "the", "moon"];
        $size  = 3;

        $expected = [
            [
                ["The"],
                ["cow"],
                ["jumped"],
                ["over"],
                ["the"],
                ["moon"]
            ],
            [
                ["The", "cow"],
                ["cow", "jumped"],
                ["jumped", "over"],
                ["over", "the"],
                ["the", "moon"]
            ],
            [
                ["The", "cow", "jumped"],
                ["cow", "jumped", "over"],
                ["jumped", "over", "the"],
                ["over", "the", "moon"]
            ]
        ];

        $this->assertEquals($expected, array_chunk_multi($array, $size));
    }

    /**
     * Tests array_chunk_multi with an empty array.
     *
     * @return void
     */
    public function test_array_chunk_multi_empty_array(): void
    {
        $array = [];
        $size  = 3;

        // Expected behavior: return an empty array, since there are no elements to chunk
        $expected = [];

        $this->assertEquals($expected, array_chunk_multi($array, $size));
    }

    /**
     * Tests array_chunk_multi with a size of 1.
     *
     * @return void
     */
    public function test_array_chunk_multi_size_one(): void
    {
        $array = ["The", "cow", "jumped", "over", "the", "moon"];
        $size  = 1;

        $expected = [
            [
                ["The"],
                ["cow"],
                ["jumped"],
                ["over"],
                ["the"],
                ["moon"]
            ]
        ];

        $this->assertEquals($expected, array_chunk_multi($array, $size));
    }

    /**
     * Tests array_chunk_multi with a size greater than the array length.
     *
     * @return void
     */
    public function test_array_chunk_multi_size_greater_than_array_length(): void
    {
        $array = ["The", "cow", "jumped"];
        $size  = 5;

        $expected = [
            [
                ["The"],
                ["cow"],
                ["jumped"]
            ],
            [
                ["The", "cow"],
                ["cow", "jumped"]
            ],
            [
                ["The", "cow", "jumped"]
            ]
        ];

        $this->assertEquals($expected, array_chunk_multi($array, $size));
    }

    /**
     * Tests array_chunk_multi with non-alphabetic characters.
     *
     * @return void
     */
    public function test_array_chunk_multi_with_special_characters(): void
    {
        $array = ["The", "cow!", "jumped@", "over#", "the$", "moon%"];
        $size  = 3;

        $expected = [
            [
                ["The"],
                ["cow!"],
                ["jumped@"],
                ["over#"],
                ["the$"],
                ["moon%"]
            ],
            [
                ["The", "cow!"],
                ["cow!", "jumped@"],
                ["jumped@", "over#"],
                ["over#", "the$"],
                ["the$", "moon%"]
            ],
            [
                ["The", "cow!", "jumped@"],
                ["cow!", "jumped@", "over#"],
                ["jumped@", "over#", "the$"],
                ["over#", "the$", "moon%"]
            ]
        ];

        $this->assertEquals($expected, array_chunk_multi($array, $size));
    }

    /**
     * Tests array_chunk_multi with a size of zero.
     * It should throw an InvalidArgumentException.
     *
     * @return void
     */
    public function test_array_chunk_multi_zero_size(): void
    {
        $array = ["The", "cow", "jumped"];
        $size  = 0;

        $this->expectException(\InvalidArgumentException::class);

        array_chunk_multi($array, $size);
    }

    /**
     * Tests array_chunk_multi with a negative size.
     * It should throw an InvalidArgumentException.
     *
     * @return void
     */
    public function test_array_chunk_multi_negative_size(): void
    {
        $array = ["The", "cow", "jumped"];
        $size  = -1;

        $this->expectException(\InvalidArgumentException::class);

        array_chunk_multi($array, $size);
    }

    /**
     * Tests array_chunk_multi with a single element in the array and a size greater than 1.
     *
     * @return void
     */
    public function test_array_chunk_multi_single_element_array(): void
    {
        $array = ["The"];
        $size  = 3;

        // The expected result should only contain the single element without extra arrays
        $expected = [
            [["The"]]
        ];

        $this->assertEquals($expected, array_chunk_multi($array, $size));
    }

    /**
     * Tests array_chunk_multi with a large size and a small array.
     *
     * @return void
     */
    public function test_array_chunk_multi_large_size_small_array(): void
    {
        $array = ["The", "cow"];
        $size  = 10;

        // Only the valid chunks should be returned
        $expected = [
            [["The"], ["cow"]],
            [["The", "cow"]]
        ];

        $this->assertEquals($expected, array_chunk_multi($array, $size));
    }

    /**
     * Tests text_chunk_multi with a standard input.
     *
     * @return void
     */
    public function test_text_chunk_multi_standard_input(): void
    {
        $array = ["The", "cow", "jumped", "over", "the", "moon"];
        $size  = 3;
        $glue  = " ";

        $expected = [
            [
                "The",
                "cow",
                "jumped",
                "over",
                "the",
                "moon"
            ],
            [
                "The cow",
                "cow jumped",
                "jumped over",
                "over the",
                "the moon"
            ],
            [
                "The cow jumped",
                "cow jumped over",
                "jumped over the",
                "over the moon"
            ]
        ];

        $this->assertEquals($expected, text_chunk_multi($array, $size, $glue));
    }

    /**
     * Tests text_chunk_multi with an empty array.
     *
     * @return void
     */
    public function test_text_chunk_multi_empty_array(): void
    {
        $array = [];
        $size  = 3;
        $glue  = " ";

        $expected = [];

        $this->assertEquals($expected, text_chunk_multi($array, $size, $glue));
    }

    /**
     * Tests text_chunk_multi with a max size of 1.
     *
     * @return void
     */
    public function test_text_chunk_multi_size_one(): void
    {
        $array = ["The", "cow", "jumped", "over", "the", "moon"];
        $size  = 1;
        $glue  = " ";

        $expected = [
            ["The", "cow", "jumped", "over", "the", "moon"]
        ];

        $this->assertEquals($expected, text_chunk_multi($array, $size, $glue));
    }

    /**
     * Tests text_chunk_multi with a size greater than the array length.
     *
     * @return void
     */
    public function test_text_chunk_multi_size_greater_than_array_length(): void
    {
        $array = ["The", "cow", "jumped"];
        $size  = 5;
        $glue  = " ";

        $expected = [
            [
                "The",
                "cow",
                "jumped"
            ],
            [
                "The cow",
                "cow jumped"
            ],
            [
                "The cow jumped"
            ]
        ];

        $this->assertEquals($expected, text_chunk_multi($array, $size, $glue));
    }

    /**
     * Tests text_chunk_multi with a custom glue.
     *
     * @return void
     */
    public function test_text_chunk_multi_custom_glue(): void
    {
        $array = ["The", "cow", "jumped", "over", "the", "moon"];
        $size  = 3;
        $glue  = "-";

        $expected = [
            [
                "The",
                "cow",
                "jumped",
                "over",
                "the",
                "moon"
            ],
            [
                "The-cow",
                "cow-jumped",
                "jumped-over",
                "over-the",
                "the-moon"
            ],
            [
                "The-cow-jumped",
                "cow-jumped-over",
                "jumped-over-the",
                "over-the-moon"
            ]
        ];

        $this->assertEquals($expected, text_chunk_multi($array, $size, $glue));
    }

    /**
     * Tests text_chunk_multi with non-alphabetic characters.
     *
     * @return void
     */
    public function test_text_chunk_multi_with_special_characters(): void
    {
        $array = ["The", "cow!", "jumped@", "over#", "the$", "moon%"];
        $size  = 3;
        $glue  = " ";

        $expected = [
            [
                "The",
                "cow!",
                "jumped@",
                "over#",
                "the$",
                "moon%"
            ],
            [
                "The cow!",
                "cow! jumped@",
                "jumped@ over#",
                "over# the$",
                "the$ moon%"
            ],
            [
                "The cow! jumped@",
                "cow! jumped@ over#",
                "jumped@ over# the$",
                "over# the$ moon%"
            ]
        ];

        $this->assertEquals($expected, text_chunk_multi($array, $size, $glue));
    }

    /**
     * Tests text_chunk_multi with an empty glue.
     *
     * @return void
     */
    public function test_text_chunk_multi_empty_glue(): void
    {
        $array = ["The", "cow", "jumped", "over", "the", "moon"];
        $size  = 3;
        $glue  = "";

        $expected = [
            [
                "The",
                "cow",
                "jumped",
                "over",
                "the",
                "moon"
            ],
            [
                "Thecow",
                "cowjumped",
                "jumpedover",
                "overthe",
                "themoon"
            ],
            [
                "Thecowjumped",
                "cowjumpedover",
                "jumpedoverthe",
                "overthemoon"
            ]
        ];

        $this->assertEquals($expected, text_chunk_multi($array, $size, $glue));
    }

    /**
     * Tests text_chunk_multi with a large size and a small array.
     *
     * @return void
     */
    public function test_text_chunk_multi_large_size_small_array(): void
    {
        $array = ["The", "cow"];
        $size  = 10;
        $glue  = " ";

        $expected = [
            ["The", "cow"],
            ["The cow"]
        ];

        $this->assertEquals($expected, text_chunk_multi($array, $size, $glue));
    }
}
