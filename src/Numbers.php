<?php

/**
 * Number to Words Conversion Class
 *
 * This file defines the Numbers class, which provides functionality
 * for converting numeric values to their word representations.
 *
 * Key features:
 * - Integer to word conversion
 * - Float to word conversion
 * - Customizable formatting options for word representations
 */

namespace FOfX\Helper;

class Numbers
{
    /**
     * @var array|null The dictionary mapping numbers to words.
     */
    private ?array $dictionary = null;

    /**
     * Retrieves the number dictionary for conversion.
     *
     * This method uses a property to cache the dictionary,
     * optimizing performance for repeated calls.
     *
     * @return array The dictionary mapping numbers to words.
     */
    private function getNumberDictionary(): array
    {
        if ($this->dictionary === null) {
            $this->dictionary = [
                0                   => 'zero',
                1                   => 'one',
                2                   => 'two',
                3                   => 'three',
                4                   => 'four',
                5                   => 'five',
                6                   => 'six',
                7                   => 'seven',
                8                   => 'eight',
                9                   => 'nine',
                10                  => 'ten',
                11                  => 'eleven',
                12                  => 'twelve',
                13                  => 'thirteen',
                14                  => 'fourteen',
                15                  => 'fifteen',
                16                  => 'sixteen',
                17                  => 'seventeen',
                18                  => 'eighteen',
                19                  => 'nineteen',
                20                  => 'twenty',
                30                  => 'thirty',
                40                  => 'forty',
                50                  => 'fifty',
                60                  => 'sixty',
                70                  => 'seventy',
                80                  => 'eighty',
                90                  => 'ninety',
                100                 => 'hundred',
                1000                => 'thousand',
                1000000             => 'million',
                1000000000          => 'billion',
                1000000000000       => 'trillion',
                1000000000000000    => 'quadrillion',
                1000000000000000000 => 'quintillion',
            ];
        }

        return $this->dictionary;
    }

    /**
     * Converts an integer number to its word representation.
     *
     * @param int    $number      The integer to convert.
     * @param string $hyphen      String used to join tens and units.
     * @param string $conjunction String used between words for numbers.
     * @param string $separator   String used to separate groups of numbers.
     * @param string $minus       String used for negative numbers.
     *
     * @return string The integer number in words.
     */
    public function convertIntegerToWords(
        int $number,
        string $hyphen = '-',
        string $conjunction = ' ',
        string $separator = ' ',
        string $minus = 'minus '
    ): string {
        $dictionary = $this->getNumberDictionary();

        if ($number === 0) {
            return $dictionary[0];
        }

        if ($number < 0) {
            return $minus . $this->convertIntegerToWords(abs($number), $hyphen, $conjunction, $separator, $minus);
        }

        $string = '';

        if ($number < 21) {
            $string = $dictionary[$number];
        } elseif ($number < 100) {
            $tens   = ((int)($number / 10)) * 10;
            $units  = $number % 10;
            $string = $dictionary[$tens];
            if ($units) {
                $string .= $hyphen . $dictionary[$units];
            }
        } elseif ($number < 1000) {
            $hundreds  = (int)($number / 100);
            $remainder = $number % 100;
            $string    = $dictionary[$hundreds] . ' ' . $dictionary[100];
            if ($remainder) {
                $string .= $conjunction . $this->convertIntegerToWords($remainder, $hyphen, $conjunction, $separator, $minus);
            }
        } else {
            $baseUnit     = pow(1000, floor(log($number, 1000)));
            $numBaseUnits = (int)($number / $baseUnit);
            $remainder    = $number % $baseUnit;
            $string       = $this->convertIntegerToWords($numBaseUnits, $hyphen, $conjunction, $separator, $minus) . ' ' . $dictionary[$baseUnit];
            if ($remainder) {
                $string .= $separator . $this->convertIntegerToWords($remainder, $hyphen, $conjunction, $separator, $minus);
            }
        }

        return $string;
    }

    /**
     * Converts a float number to its word representation.
     *
     * @param float    $number         The float to convert.
     * @param string   $hyphen         String used to join tens and units.
     * @param string   $conjunction    String used between words for numbers.
     * @param string   $separator      String used to separate groups of numbers.
     * @param string   $minus          String used for negative numbers.
     * @param string   $point          String used for decimal point.
     * @param int|null $decimal_places Number of decimal places to consider (null for all).
     *
     * @throws \Exception If the number is represented in scientific notation.
     *
     * @return string The float number in words.
     */
    public function convertFloatToWords(
        float $number,
        string $hyphen = '-',
        string $conjunction = ' ',
        string $separator = ' ',
        string $minus = 'minus ',
        string $point = ' point ',
        ?int $decimal_places = null
    ): string {
        // Throw an exception if number is in scientific notation
        if (stripos((string)$number, 'e') !== false) {
            throw new \Exception('Scientific notation is not supported.');
        }

        // Handle negative numbers
        $sign   = $number < 0 ? $minus : '';
        $number = abs($number);

        // Split the number into integer and fractional parts
        $parts          = explode('.', (string)$number);
        $integerPart    = (int)$parts[0];
        $fractionalPart = isset($parts[1]) ? $parts[1] : '';

        // Apply decimal limit if specified
        if ($decimal_places !== null) {
            $fractionalPart = substr($fractionalPart, 0, $decimal_places);
        }

        // Convert the integer part
        $string = $this->convertIntegerToWords($integerPart, $hyphen, $conjunction, $separator);

        // Add fractional part if it exists
        if ($fractionalPart !== '') {
            $string .= $point;
            $digits = str_split($fractionalPart);
            foreach ($digits as $digit) {
                $string .= $this->convertIntegerToWords((int)$digit, $hyphen, $conjunction, $separator) . $separator;
            }
            $string = rtrim($string, $separator);
        }

        return $sign . $string;
    }

    /**
     * Converts a number to its word representation.
     *
     * Can be used instead of NumberFormatter::SPELLOUT.
     *
     * @param int|float $number         The number to convert to words.
     * @param string    $hyphen         String used to join tens and units.
     * @param string    $conjunction    String used between words for numbers.
     * @param string    $separator      String used to separate groups of numbers.
     * @param string    $minus          String used for negative numbers.
     * @param string    $decimal        String used for decimal points.
     * @param int|null  $decimal_places Number of decimal places to consider (null for all).
     *
     * @throws \Exception If the number is represented in scientific notation.
     *
     * @return string The number in words.
     */
    public function numberToWords(
        int|float $number,
        string $hyphen = '-',
        string $conjunction = ' ',
        string $separator = ' ',
        string $minus = 'minus ',
        string $decimal = ' point ',
        ?int $decimal_places = null
    ): string {
        // Throw an exception if number is in scientific notation
        if (stripos((string)$number, 'e') !== false) {
            throw new \Exception('Scientific notation is not supported.');
        }

        if (is_int($number)) {
            return $this->convertIntegerToWords($number, $hyphen, $conjunction, $separator, $minus);
        } else {
            return $this->convertFloatToWords($number, $hyphen, $conjunction, $separator, $minus, $decimal, $decimal_places);
        }
    }
}
