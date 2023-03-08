<?php
/**
 * @package axy\errors
 * @author Oleg Grigoriev <go.vasac@gmail.com>
 */

namespace axy\errors;

/**
 * This service is disabled in the current environment
 *
 * @link https://github.com/axypro/errors/blob/master/doc/classes/Disabled.md documentation
 */
class Disabled extends Logic implements Forbidden
{
    /**
     * {@inheritdoc}
     */
    protected $defaultMessage = '{{ service }} is disabled';

    /**
     * The constructor
     *
     * @param object|string $service [optional]
     *        the service or its name
     * @param \Exception $previous
     * @param object $thrower [optional]
     */
    public function __construct($service = null, \Exception $previous = null, $thrower = null)
    {
        $this->service = $service;
        $message = [
            'service' => $service,
        ];
        parent::__construct($message, 0, $previous, $thrower);
    }

    /**
     * Get the disabled service
     *
     * @return object|string
     */
    public function getService()
    {
        return $this->service;
    }

    /**
     * @var object|string
     */
    private $service;
}
