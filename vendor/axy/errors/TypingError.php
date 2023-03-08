<?php
/**
 * @package axy\errors
 * @author Oleg Grigoriev <go.vasac@gmail.com>
 */

namespace axy\errors;

/**
 * The error of a value type
 *
 * @link https://github.com/axypro/errors/blob/master/doc/classes/TypingError.md documentation
 */
class TypingError extends Logic implements InvalidValue
{
    /**
     * {@inheritdoc}
     */
    protected $defaultMessage = '{{ varName }} must be {{ expected }}';

    /**
     * The constructor
     *
     * @param string $varName [optional]
     * @param string|array $expected [optional]
     *        the list of the expected types
     * @param \Exception $previous [optional]
     * @param mixed $thrower [optional]
     */
    public function __construct($varName = null, $expected = null, \Exception $previous = null, $thrower = null)
    {
        $this->varName = $varName;
        if (is_array($expected)) {
            $this->expected = $expected;
            if (count($expected) > 0) {
                $last = array_pop($expected);
                if (!empty($expected)) {
                    $expected = implode(', ', $expected).' or '.$last;
                } else {
                    $expected = $last;
                }
            } else {
                $expected = 'a different type';
            }
        } elseif ($expected !== null) {
            $this->expected = [$expected];
        } else {
            $this->expected = [];
            $expected = 'a different type';
        }
        $message = [
            'varName' => $varName ?: 'A value',
            'expected' => $expected,
        ];
        parent::__construct($message, 0, $previous, $thrower);
    }

    /**
     * @return string
     */
    public function getVarName()
    {
        return $this->varName;
    }

    /**
     * @return array
     */
    public function getExpected()
    {
        return $this->expected;
    }

    /**
     * @var array
     */
    protected $varName;

    /**
     * @var array
     */
    protected $expected;
}
