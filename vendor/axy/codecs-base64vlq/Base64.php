<?php
/**
 * @package axy\codecs\base64vlq
 * @author Oleg Grigoriev <go.vasac@gmail.com>
 */

namespace axy\codecs\base64vlq;

use axy\codecs\base64vlq\errors\InvalidBase64Input;
use axy\codecs\base64vlq\errors\InvalidBase64;

/**
 * Convert binary-to-text
 */
class Base64
{
    /**
     * The constructor
     *
     * @param string|string[] $alphabet [optional]
     *        the transform alphabet.
     *        Array: number => char
     *        Or string: index of char => number
     *        Standard base64 alphabet by default.
     */
    public function __construct($alphabet = null)
    {
        if ($alphabet !== null) {
            $this->alphabet = $alphabet;
        }
        if (!is_array($this->alphabet)) {
            $this->alphabet = str_split($this->alphabet);
        }
        $this->char2int = array_flip($this->alphabet);
    }

    /**
     * Encodes a block of numbers to a base64-string
     *
     * @param int[]|int $numbers
     * @return string
     * @throws \axy\codecs\base64vlq\errors\InvalidBase64Input
     */
    public function encode($numbers)
    {
        $chars = [];
        $alphabet = $this->alphabet;
        foreach ($numbers as $number) {
            if (isset($alphabet[$number])) {
                $chars[] = $alphabet[$number];
            } else {
                throw new InvalidBase64Input($number);
            }
        }
        return implode('', $chars);
    }

    /**
     * Decodes a base64-string to a block of numbers
     *
     * @param string|string[] $based
     *        the base64-string or the array of characters
     * @return int[]
     * @throws \axy\codecs\base64vlq\errors\InvalidBase64
     */
    public function decode($based)
    {
        if (!is_array($based)) {
            $based = str_split($based);
        }
        $numbers = [];
        $char2int = $this->char2int;
        foreach ($based as $char) {
            if (isset($char2int[$char])) {
                $numbers[] = $char2int[$char];
            } else {
                throw new InvalidBase64($based);
            }
        }
        return $numbers;
    }

    /**
     * @var string|array
     */
    private $alphabet = 'ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789+/';

    /**
     * @var array
     */
    private $char2int;
}
