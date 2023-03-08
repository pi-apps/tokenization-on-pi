<?php
/**
 * @package axy\codecs\base64vlq
 * @author Oleg Grigoriev <go.vasac@gmail.com>
 */

namespace axy\codecs\base64vlq\errors;

use axy\errors\Runtime;

/**
 * The number for base64 is invalid
 */
class InvalidBase64Input extends Runtime implements VLQ
{
    /**
     * {@inheritdoc}
     */
    protected $defaultMessage = 'Number {{ input }} is not found in Base64 alphabet';

    /**
     * The constructor
     *
     * @param int $input
     * @param \Exception $previous [optional]
     * @param mixed $thrower [optional]
     */
    public function __construct($input, \Exception $previous = null, $thrower = null)
    {
        $this->input = $input;
        $message = [
            'input' => $input,
        ];
        parent::__construct($message, 0, $previous, $thrower);
    }

    /**
     * @return int
     */
    public function getInput()
    {
        return $this->input;
    }

    /**
     * @var int
     */
    private $input;
}
