<?php
/**
 * @package axy\errors
 * @author Oleg Grigoriev <go.vasac@gmail.com>
 */

namespace axy\errors;

/**
 * A configuration has an invalid format
 *
 * @link https://github.com/axypro/errors/blob/master/doc/classes/InvalidConfig.md documentation
 */
class InvalidConfig extends Logic
{
    /**
     * {@inheritdoc}
     */
    protected $defaultMessage = '{{ configName }} has an invalid format: "{{ errorMessage }}"';

    /**
     * The constructor
     *
     * @param string $configName [optional]
     *        the config name
     * @param string $errorMessage [optional]
     *        the error message
     * @param int $code [optional]
     *        the error code
     * @param \Exception $p [optional]
     * @param mixed $t [optional]
     */
    public function __construct($configName = null, $errorMessage = null, $code = 0, \Exception $p = null, $t = null)
    {
        $this->configName = $configName;
        $this->errorMessage = $errorMessage;
        $message = [
            'configName' => $configName,
            'errorMessage' => $errorMessage,
        ];
        parent::__construct($message, $code, $p, $t);
    }

    /**
     * @return string
     */
    final public function getConfigName()
    {
        return $this->configName;
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
    protected $configName;

    /**
     * @var int
     */
    protected $errorMessage;
}
