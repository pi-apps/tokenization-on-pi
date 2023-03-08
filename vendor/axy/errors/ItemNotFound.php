<?php
/**
 * @package axy\errors
 * @author Oleg Grigoriev <go.vasac@gmail.com>
 */

namespace axy\errors;

/**
 * An item was not found in a variable container at the current moment
 *
 * @link https://github.com/axypro/errors/blob/master/doc/classes/ItemNotFound.md documentation
 */
class ItemNotFound extends Runtime implements NotFound
{
    /**
     * {@inheritdoc}
     */
    protected $defaultMessage = 'Item "{{ key }}" is not found in "{{ container }}"';

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
