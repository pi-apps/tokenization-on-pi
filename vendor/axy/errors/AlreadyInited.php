<?php
/**
 * @package axy\errors
 * @author Oleg Grigoriev <go.vasac@gmail.com>
 */

namespace axy\errors;

/**
 * Attempt to re-initialize an object
 *
 * @link https://github.com/axypro/errors/blob/master/doc/classes/AlreadyInited.md documentation
 */
class AlreadyInited extends Logic implements Init
{
    /**
     * {@inheritdoc}
     */
    protected $defaultMessage = '{{ object }} has already been initialized';

    /**
     * The constructor
     *
     * @param object|string $object [optional]
     *        the object or its name
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
