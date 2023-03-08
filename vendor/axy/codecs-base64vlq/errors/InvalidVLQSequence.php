<?php
/**
 * @package axy\codecs\base64vlq
 * @author Oleg Grigoriev <go.vasac@gmail.com>
 */

namespace axy\codecs\base64vlq\errors;

use axy\errors\Runtime;

/**
 * The VLQ sequence is invalid
 *
 * The last digit has continuation bit for example.
 */
class InvalidVLQSequence extends Runtime implements VLQ
{
    /**
     * {@inheritdoc}
     */
    protected $defaultMessage = 'VLQ sequence is invalid: [{{ digits }}]';

    /**
     * The constructor
     *
     * @param string|int[] $digits
     * @param \Exception $previous [optional]
     * @param mixed $thrower [optional]
     */
    public function __construct($digits, \Exception $previous = null, $thrower = null)
    {
        $this->digits = $digits;
        $message = [
            'digits' => is_array($digits) ? implode(',', $digits) : $digits,
        ];
        parent::__construct($message, 0, $previous, $thrower);
    }

    /**
     * @return string|int[]
     */
    public function getDigits()
    {
        return $this->digits;
    }

    /**
     * @var string|int[]
     */
    private $digits;
}
