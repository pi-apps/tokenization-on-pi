<?php
/**
 * @package axy\errors
 * @author Oleg Grigoriev <go.vasac@gmail.com>
 */

namespace axy\errors;

/**
 * Invalid format of some string
 */
class InvalidFormat extends Logic
{
    /**
     * {@inheritdoc}
     */
    protected $defaultMessage = '{{ type }} {{ value }} has invalid format';

    /**
     * Brackets for the value in the message
     */
    protected $brackets = ['"', '"'];

    /**
     * The constructor
     *
     * @param string $value [optional]
     * @param string $type [optional]
     * @param \Exception $previous [optional]
     * @param mixed $thrower [optional]
     */
    public function __construct($value = null, $type = null, \Exception $previous = null, $thrower = null)
    {
        $this->value = $value;
        $this->type = $type;
        $message= [
            'value' => $this->brackets[0].$value.$this->brackets[1],
            'type' => $type ?: 'String',
        ];
        parent::__construct($message, 0, $previous, $thrower);
    }

    /**
     * @return string
     */
    final public function getValue()
    {
        return $this->value;
    }

    /**
     * @return string
     */
    final public function getType()
    {
        return $this->type;
    }

    /**
     * @var string
     */
    private $value;

    /**
     * @var string
     */
    private $type;
}
