<?php
/**
 * @package axy\errors
 * @author Oleg Grigoriev <go.vasac@gmail.com>
 */

namespace axy\errors;

/**
 * A method requires override
 *
 * @link https://github.com/axypro/errors/blob/master/doc/classes/RequiresOverride.md documentation
 */
class RequiresOverride extends Logic implements Error
{
    /**
     * {@inheritdoc}
     */
    protected $defaultMessage = 'Method {{ method }} requires override';

    /**
     * Constructor
     *
     * @param string|boolean $method [optional]
     *        the method name (if TRUE - method where the exception was thrown)
     * @param \Exception $previous [optional]
     * @param mixed $thrower [optional]
     */
    public function __construct($method = true, \Exception $previous = null, $thrower = null)
    {
        if ($method === true) {
            $trace = debug_backtrace();
            $method = '';
            if (isset($trace[1])) {
                $trace = $trace[1];
                if (!empty($trace['class'])) {
                    $method .= $trace['class'].'::';
                }
                if (!empty($trace['function'])) {
                    $method .= $trace['function'];
                }
            }
        }
        $this->method = $method;
        $message = [
            'method' => $method,
        ];
        parent::__construct($message, 0, $previous, $thrower);
    }

    /**
     * @return string
     */
    final public function getMethod()
    {
        return $this->method;
    }

    /**
     * @var string
     */
    protected $method;
}
