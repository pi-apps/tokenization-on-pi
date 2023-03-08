<?php
/**
 * @package axy\errors
 * @author Oleg Grigoriev <go.vasac@gmail.com>
 */

namespace axy\errors\helpers;

/**
 * The trait for common actions of Runtime and Logic exceptions
 */
trait ErrorTrait
{
    use MessageBuilder;
    use TraceTruncate;

    /**
     * Performs common actions
     *
     * @param mixed $message
     *        a error message or variables for the message template
     * @param int $code
     *        a error code
     * @param \Exception $previous
     *        an object of a previous exception
     * @param mixed $thrower
     *        one who has thrown exception
     */
    public function callErrorTrait($message, $code, $previous, $thrower)
    {
        $message = $this->createMessage($message, $code);
        /** @noinspection PhpUndefinedMethodInspection */
        /** @noinspection PhpUndefinedClassInspection */
        parent::__construct($message, $code, $previous);
        $this->truncateTrace($thrower);
    }
}
