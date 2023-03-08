<?php
/**
 * @package axy\errors
 * @author Oleg Grigoriev <go.vasac@gmail.com>
 */

namespace axy\errors;

/**
 * An object is not initialized
 *
 * @link https://github.com/axypro/errors/blob/master/doc/classes/NotInited.md documentation
 */
class NotInited extends Logic implements Init
{
    /**
     * {@inheritdoc}
     */
    protected $defaultMessage = '{{ object }} is not initialized';

    /**
     * The constructor
     *
     * @param object|string $object [optional]
     * @param \Exception $previous [optional]
     * @param mixed $thrower [optional]
     */
    public function __construct($object = null, \Exception $previous = null, $thrower = null)
    {
        $this->object = $object;
        $message = [
            'object' => $object,
        ];
        parent::__construct($message, 0, $previous, $thrower);
    }

    /**
     * @return object|string
     */
    final public function getObject()
    {
        return $this->object;
    }

    /**
     * @var object|string
     */
    protected $object;
}
