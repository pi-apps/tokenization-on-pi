<?php
/**
 * @package axy\codecs\base64vlq
 * @author Oleg Grigoriev <go.vasac@gmail.com>
 */

namespace axy\codecs\base64vlq;

use axy\codecs\base64vlq\errors\InvalidVLQSequence;

/**
 * Creating sequence of "VLQ digits" for integers
 *
 * Example: 12345
 * Binary: 11000000111001
 * After sign-transform: 110000001110010
 * Groups (5 bit): 11000 00011 10010
 * Revert groups and continuation bit: 110010 100011 011000
 * VLQ-digits: 50, 35, 24
 */
class VLQ
{
    /**
     * The constructor
     *
     * @param int $bits [optional]
     *        bit capacity of VLQ digits
     * @param bool $signed [optional]
     *        required sign-transform
     */
    public function __construct($bits = 6, $signed = true)
    {
        $this->bits = ($bits ?: 6) - 1;
        $this->cBit = (1 << $this->bits);
        $this->signed = $signed;
    }

    /**
     * Encodes a block of numbers to a VLQ-sequence
     *
     * @param int[]|int $numbers
     *        a block of numbers of a single number
     * @return int[]
     */
    public function encode($numbers)
    {
        if (!is_array($numbers)) {
            $numbers = [$numbers];
        }
        if ($this->signed) {
            $numbers = Signed::encodeBlock($numbers);
        }
        $digits = [];
        $bits = $this->bits;
        $cBit = $this->cBit;
        foreach ($numbers as $number) {
            do {
                $digit = ($number % $this->cBit);
                $number >>= $bits;
                $continue = ($number > 0);
                if ($continue) {
                    $digit += $cBit;
                }
                $digits[] = $digit;
            } while ($continue);
        }
        return $digits;
    }

    /**
     * Decodes a VLQ-sequence to a block of numbers
     *
     * @param int[] $digits
     * @return int[]
     * @throws \axy\codecs\base64vlq\errors\InvalidVLQSequence
     */
    public function decode(array $digits)
    {
        $result = [];
        $current = 0;
        $cBit = $this->cBit;
        $bits = $this->bits;
        $shift = 0;
        foreach ($digits as $digit) {
            $current += (($digit % $cBit) << $shift);
            if ($digit < $cBit) {
                $result[] = $current;
                $current = 0;
                $shift = 0;
            } else {
                $shift += $bits;
            }
        }
        if ($current > 0) {
            throw new InvalidVLQSequence($digits);
        }
        if ($this->signed) {
            $result = Signed::decodeBlock($result);
        }
        return $result;
    }

    /**
     * @var bool
     */
    private $signed;

    /**
     * @var int
     */
    private $bits;

    /**
     * @var int
     */
    private $cBit;
}
