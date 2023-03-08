<?php
/**
 * @package axy\errors
 * @author Oleg Grigoriev <go.vasac@gmail.com>
 */

namespace axy\errors;

/**
 * A field does not exist in a fixed list of the container
 *
 * @link https://github.com/axypro/errors/blob/master/doc/classes/FieldNotExists.md documentation
 */
class FieldNotExist extends Logic implements NotFound
{
    /**
     * {@inheritdoc}
     */
    protected $defaultMessage = 'Field "{{ key }}" is not exist in "{{ container }}"';

    /**
     * The constructor
     *
     * @param string $key [optional]
     * @param object|string $container [optional]
     * @param \Exception $previous [optional]
     * @param mixed $thrower [optional]
     */
    public function __construct($key = null, $container = null, \Exception $previous = null, $thrower = null)
    {
        $this->key = $key;
        $this->container = $container;
        $message = [
            'key' => $key,
            'container' => $container,
        ];
        parent::__construct($message, 0, $previous, $thrower);
    }

    /**
     * @return string
     */
    final public function getKey()
    {
        return $this->key;
    }

    /**
     * @return object|string
     */
    final public function getContainer()
    {
        return $this->container;
    }

    /**
     * @var string
     */
    protected $key;

    /**
     * @var object|string
     */
    protected $container;
}
