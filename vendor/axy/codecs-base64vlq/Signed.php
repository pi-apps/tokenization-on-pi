<?php
/**
 * @package axy\codecs\base64vlq
 * @author Oleg Grigoriev <go.vasac@gmail.com>
 */

namespace axy\codecs\base64vlq;

/**
 * Transform signed integers to unsigned for further VQL-coding
 *
 * The sign bit is transferred to the end of the number.
 * 5 -> 00000101 -> 00001010 -> 10
 * -5 -> unsigned, shift and + sign -> 00001011 -> 11
 *
 * The class may not work properly with big numbers (about 10^9 for 32-bit system).
 */
class Signed
{
    /**
     * Encodes a signed integer
     *
     * @param int $signed
     * @return int
     */
    public static function encode($signed)
    {
        $encoded = $signed * 2;
        if ($encoded < 0) {
            $encoded = 1 - $encoded;
        }
        return $encoded;
    }

    /**
     * Decodes an encoded integer to an original signed integer
     *
     * @param int $encoded
     * @return int
     */
    public static function decode($encoded)
    {
        $signed = $encoded >> 1;
        if (($encoded & 1) === 1) {
            $signed = -$signed;
        }
        return $signed;
    }

    /**
     * Encodes a block of signed integers
     *
     * @param int[] $sBlock
     * @return int[]
     */
    public static function encodeBlock(array $sBlock)
    {
        return array_map([__CLASS__, 'encode'], $sBlock);
    }

    /**
     * Decodes a block of encoded integers
     *
     * @param int[] $eBlock
     * @return int[]
     */
    public static function decodeBlock(array $eBlock)
    {
        return array_map([__CLASS__, 'decode'], $eBlock);
    }
}
