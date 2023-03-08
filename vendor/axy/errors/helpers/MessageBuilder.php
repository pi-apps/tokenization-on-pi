<?php
/**
 * @package axy\errors
 * @author Oleg Grigoriev <go.vasac@gmail.com>
 */

namespace axy\errors\helpers;

/**
 * The error message builder
 *
 * @link https://github.com/axypro/errors/blob/master/doc/message.md documentation
 */
trait MessageBuilder
{
    /**
     * The default message or the template for it
     *
     * @var string
     */
    protected $defaultMessage = '';

    /**
     * Creates the message for the exception instance
     *
     * @param mixed $message
     *        the original message or variables for the template
     * @param int $code
     *        the error code
     * @return string
     */
    private function createMessage($message, $code)
    {
        if (is_array($message)) {
            if (!array_key_exists('code', $message)) {
                $message['code'] = $code;
            }
            $callback = function ($m) use ($message) {
                $key = trim($m[1]);
                if (!isset($message[$key])) {
                    return '';
                }
                $value = $message[$key];
                if (is_object($value)) {
                    if (method_exists($value, '__toString')) {
                        $value = (string)$value;
                    } else {
                        $value = get_class($value);
                    }
                }
                return $value;
            };
            return preg_replace_callback('~\{\{(.*?)\}\}~', $callback, $this->defaultMessage);
        }
        return ($message !== null) ? $message : $this->defaultMessage;
    }
}
