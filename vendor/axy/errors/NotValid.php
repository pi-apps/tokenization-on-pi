<?php
/**
 * @package axy\errors
 * @author Oleg Grigoriev <go.vasac@gmail.com>
 */

namespace axy\errors;

/**
 * A value is not valid for this action
 *
 * @link https://github.com/axypro/errors/blob/master/doc/classes/NotValid.md documentation
 */
class NotValid extends Logic implements InvalidValue
{
    /**
     * {@inheritdoc}
     */
    protected $defaultMessage = 'Value of {{ varName }} is not valid: {{ errorMessage }}';

    /**
     * The constructor
     *
     * @param string $varName [optional]
     *        name of a variable who contains the value
     * @param string $errorMessage [optional]
     *        the error message
     * @param \Exception $previous [optional]
     * @param mixed $thrower [optional]
     */
    public function __construct($varName = null, $errorMessage = null, \Exception $previous = null, $thrower = null)
    {
        $message = [
            'varName' => $varName,
            'errorMessage' => $errorMessage,
        ];
        $this->varName = $varName;
        $this->errorMessage = $errorMessage;
        parent::__construct($message, 0, $previous, $thrower);
    }

    /**
     * @return string
     */
    final public function getVarName()
    {
        return $this->varName;
    }

    /**
     * @return string
     */
    final public function getErrorMessage()
    {
        return $this->errorMessage;
    }

    /**
     * @var string
     */
    protected $varName;

    /**
     * @var string
     */
    protected $errorMessage;
}
