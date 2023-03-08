<?php
/**
 * @package axy\codecs\base64vlq
 * @author Oleg Grigoriev <go.vasac@gmail.com>
 */

namespace axy\codecs\base64vlq;

/**
 * Codec for VLQ in Base64
 */
class Encoder
{
    /**
     * The constructor
     *
     * @param string|string[] $base64alphabet [optional]
     *        the alphabet for the Base64 encoder
     * @param int $vlqBits [optional]
     *        bit capacity of VLQ digits
     * @param bool $signed [optional]
     *        required sign-transform
     */
    public function __construct($base64alphabet = null, $vlqBits = null, $signed = true)
    {
        $this->vlq = new VLQ($vlqBits, $signed);
        $this->base64 = new Base64($base64alphabet);
    }

    /**
     * Encodes a block of numbers
     *
     * @param int[]|int $numbers
     * @return string
     * @throws \axy\codecs\base64vlq\errors\Base64
     * @throws \axy\codecs\base64vlq\errors\VLQ
     */
    public function encode($numbers)
    {
        return $this->base64->encode($this->vlq->encode($numbers));
    }

    /**
     * Decodes an encoded string to a block of numbers
     *
     * @param string|string[] $encoded
     * @return int[]
     * @throws \axy\codecs\base64vlq\errors\Base64
     * @throws \axy\codecs\base64vlq\errors\VLQ
     */
    public function decode($encoded)
    {
        return $this->vlq->decode($this->base64->decode($encoded));
    }

    /**
     * Returns the instance with standard options (base64-alphabet, 6 bits, signed)
     *
     * @return \axy\codecs\base64vlq\Encoder
     */
    public static function getStandardInstance()
    {
        if (!self::$instance) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    /**
     * @var \axy\codecs\base64vlq\VLQ
     */
    private $vlq;

    /**
     * @var \axy\codecs\base64vlq\Base64
     */
    private $base64;

    /**
     * @var \axy\codecs\base64vlq\Encoder
     */
    private static $instance;
}
