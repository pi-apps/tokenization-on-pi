<?php
/**
 * @package axy\backtrace
 * @author Oleg Grigoriev <go.vasac@gmail.com>
 */

namespace axy\backtrace\helpers;

/**
 * Representation of a backtrace as a string
 *
 * @author Oleg Grigoriev <go.vasac@gmail.com>
 */
class Represent
{
    /**
     * The maximum length of a method argument
     *
     * @var int
     */
    const MAX_LEN = 15;

    /**
     * Represents an argument of a method as a string
     *
     * @param mixed $value
     * @return string
     */
    public static function arg($value)
    {
        switch (gettype($value)) {
            case 'NULL':
                return 'NULL';
            case 'boolean':
                return $value ? 'true' : 'false';
            case 'array':
                return 'Array';
            case 'object':
                return 'Object('.get_class($value).')';
            case 'string':
                return "'".self::cutString((string)$value)."'";
            default:
                return (string)$value;
        }
    }

    /**
     * Represents a method call as a string
     *
     * @param array $item
     * @return string
     */
    public static function method(array $item)
    {
        if (empty($item['function'])) {
            return '';
        }
        $method = $item['function'];
        if (!empty($item['class'])) {
            $type = (empty($item['type']) ? '->' : $item['type']);
            $class = $item['class'].$type;
        } else {
            $class = '';
        }
        $args = isset($item['args']) ? $item['args'] : [];
        foreach ($args as &$arg) {
            $arg = self::arg($arg);
        }
        unset($arg);
        return $class.$method.'('.implode(', ', $args).')';
    }

    /**
     * Represents a call point as a string
     *
     * @param array $item
     * @return string
     */
    public static function point(array $item)
    {
        if (empty($item['file'])) {
            $result = '[internal function]';
        } else {
            $result = $item['file'];
            if (!empty($item['line'])) {
                $result .= '('.$item['line'].')';
            }
        }
        return $result;
    }

    /**
     * Represents a trace item as a string
     *
     * @param array $item
     *        a backtrace item
     * @param int $number [optional]
     *        a number of the item in the trace
     * @return string
     */
    public static function item(array $item, $number = null)
    {
        if ($number !== null) {
            $number = '#'.$number.' ';
        }
        return $number.self::point($item).': '.self::method($item);
    }

    /**
     * Represents a trace as a string
     *
     * @param array $items
     *        a trace items list
     * @param string $sep
     *        a line separator
     * @return string
     */
    public static function trace(array $items, $sep = PHP_EOL)
    {
        $lines = [];
        foreach ($items as $number => $item) {
            $lines[] = self::item($item, $number);
        }
        $lines[] = '#'.(count($items)).' {main}';
        return implode($sep, $lines).$sep;
    }

    /**
     * Cuts a string by the max length
     *
     * @param string $str
     * @return string
     */
    private static function cutString($str)
    {
        static $mb;
        if ($mb === null) {
            $mb = function_exists('mb_strlen');
        }
        if ($mb) {
            $len = mb_strlen($str, 'UTF-8');
        } else {
            $len = strlen($str);
        }
        if ($len > self::MAX_LEN) {
            if ($mb) {
                return mb_substr($str, 0, self::MAX_LEN, 'UTF-8').'...';
            } else {
                return substr($str, 0, self::MAX_LEN).'...';
            }
        }
        return $str;
    }
}
