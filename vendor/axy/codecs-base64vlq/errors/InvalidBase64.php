<?php
/**
 * @package axy\codecs\base64vlq
 * @author Oleg Grigoriev <go.vasac@gmail.com>
 */

namespace axy\codecs\base64vlq\errors;

use axy\errors\Runtime;

/**
 * The Base64 string is invalid
 */
class InvalidBase64 extends Runtime implements VLQ
{
    /**
     * {@inheritdoc}
     */
    protected $defaultMessage = 'Base-64 string is invalid: "{{ base64string }}"';

    /**
     * The constructor
     *
     * @param string|string[] $base64string
     * @param \Exception $previous [optional]
     * @param mixed $thrower [optional]
     */
    public function __construct($base64string, \Exception $previous = null, $thrower = null)
    {
        $this->base64string = $base64string;
        $message = [
            'base64string' => is_array($base64string) ? implode('', $base64string) : $base64string,
        ];
        parent::__construct($message, 0, $previous, $thrower);
    }

    /**
     * @return string|string[]
     */
    public function getBase64string()
    {
        return $this->base64string;
    }

    /**
     * @var string|int[]
     */
    private $base64string;
}
