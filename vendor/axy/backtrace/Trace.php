<?php
/**
 * @package axy\backtrace
 * @author Oleg Grigoriev <go.vasac@gmail.com>
 */

namespace axy\backtrace;

use axy\backtrace\helpers\Represent;

/**
 * The class of call trace
 *
 * @link https://github.com/axypro/backtrace/blob/master/doc/Trace.md documentation
 * @property-read array $items
 *                the current state of the backtrace
 * @property-read array $originalItems
 *                the original state of the backtrace
 * @SuppressWarnings(PHPMD.TooManyPublicMethods)
 */
class Trace implements \Countable, \IteratorAggregate, \ArrayAccess
{
    /**
     * Filtering result: do not truncate
     *
     * @var mixed
     */
    const FILTER_SKIP = false;

    /**
     * Filtering result: truncate, but leave this item
     *
     * @var int
     */
    const FILTER_LEAVE = 1;

    /**
     * Filtering result: truncate together with this item
     *
     * @var int
     */
    const FILTER_LEFT = 2;

    /**
     * The constructor
     *
     * @param mixed $items [optional]
     *        a trace array or NULL (for the current trace)
     */
    public function __construct(array $items = null)
    {
        if ($items === null) {
            $items = debug_backtrace();
            array_shift($items);
        }
        $this->props = [
            'items' => $items,
            'originalItems' => $items,
        ];
    }

    /**
     * Normalizes the trace items
     */
    final public function normalize()
    {
        foreach ($this->props['items'] as &$item) {
            $item = array_replace($this->defaultItem, $item);
        }
        unset($item);
    }

    /**
     * Truncates the trace by a limit
     *
     * @param int $limit
     * @return bool
     */
    final public function truncateByLimit($limit)
    {
        if (count($this->props['items']) <= $limit) {
            return false;
        }
        $this->props['items'] = array_slice($this->props['items'], 0, $limit);
        return true;
    }

    /**
     * Trims a filename by a basic directory name
     *
     * @param string $prefix
     * @return bool
     */
    public function trimFilename($prefix)
    {
        $affected = false;
        $len = strlen($prefix);
        foreach ($this->props['items'] as &$item) {
            if ((!empty($item['file'])) && (strpos($item['file'], $prefix) === 0)) {
                $item['file'] = substr($item['file'], $len);
                $affected = true;
            }
        }
        unset($item);
        return $affected;
    }

    /**
     * Truncates the trace by a options
     *
     * @link https://github.com/axypro/backtrace/blob/master/doc/truncate.md documentation
     * @param array $options
     *        the options (see $defaultOptions for list)
     * @return boolean
     *         Was found truncation point?
     */
    public function truncate(array $options)
    {
        $options = array_replace($this->defaultOptions, $options);
        $nItems = [];
        foreach (array_reverse($this->props['items']) as $item) {
            $f = $this->filterItem($item, $options);
            if ($f) {
                if ($f !== self::FILTER_LEFT) {
                    $nItems[] = $item;
                }
                $this->props['items'] = array_reverse($nItems);
                return true;
            }
            $nItems[] = $item;
        }
        return false;
    }

    /**
     * Truncates the trace by a filter
     *
     * @link https://github.com/axypro/backtrace/blob/master/doc/truncate.md documentation
     * @param callable $filter
     * @return boolean
     */
    final public function truncateByFilter($filter)
    {
        return $this->truncate(['filter' => $filter]);
    }

    /**
     * Truncates the trace by a namespace
     *
     * @link https://github.com/axypro/backtrace/blob/master/doc/truncate.md documentation
     * @param string $namespace
     * @return boolean
     */
    final public function truncateByNamespace($namespace)
    {
        return $this->truncate(['namespace' => $namespace]);
    }

    /**
     * Truncates the trace by a class name
     *
     * @link https://github.com/axypro/backtrace/blob/master/doc/truncate.md documentation
     * @param string $class
     * @return boolean
     */
    final public function truncateByClass($class)
    {
        return $this->truncate(['class' => $class]);
    }

    /**
     * Truncates the trace by a file name
     *
     * @link https://github.com/axypro/backtrace/blob/master/doc/truncate.md documentation
     * @param string $file
     * @return boolean
     */
    final public function truncateByFile($file)
    {
        return $this->truncate(['file' => $file]);
    }

    /**
     * Truncates the trace by a directory name
     *
     * @link https://github.com/axypro/backtrace/blob/master/doc/truncate.md documentation
     * @param string $dir
     * @return boolean
     */
    final public function truncateByDir($dir)
    {
        return $this->truncate(['dir' => $dir]);
    }

    /**
     * Magic get
     *
     * @param string $key
     * @return mixed
     * @throw \LogicException
     *        a key is not found in the Trace
     */
    final public function __get($key)
    {
        if (!array_key_exists($key, $this->props)) {
            throw new \LogicException('A field "'.$key.'" is not found in a Trace');
        }
        return $this->props[$key];
    }

    /**
     * Magic isset
     *
     * @param string $key
     * @return boolean
     */
    final public function __isset($key)
    {
        return array_key_exists($key, $this->props);
    }

    /**
     * Magic set (forbidden)
     *
     * @param string $key
     * @param mixed $value
     * @throws \LogicException
     */
    final public function __set($key, $value)
    {
        throw new \LogicException('Trace is read-only');
    }

    /**
     * Magic unset (forbidden)
     *
     * @param string $key
     * @throws \LogicException
     */
    final public function __unset($key)
    {
        throw new \LogicException('Trace is read-only');
    }

    /**
     * {@inheritdoc}
     */
    final public function count()
    {
        return count($this->props['items']);
    }

    /**
     * {@inheritdoc}
     */
    final public function getIterator()
    {
        return new \ArrayIterator($this->props['items']);
    }

    /**
     * {@inheritdoc}
     */
    final public function offsetExists($offset)
    {
        return isset($this->props['items'][$offset]);
    }

    /**
     * {@inheritdoc}
     */
    final public function offsetGet($offset)
    {
        if (!isset($this->props['items'][$offset])) {
            throw new \OutOfRangeException('Trace['.$offset.'] is not found');
        }
        return $this->props['items'][$offset];
    }

    /**
     * {@inheritdoc}
     * Forbidden
     * @throws \LogicException
     */
    final public function offsetSet($offset, $value)
    {
        throw new \LogicException('Trace is read-only');
    }

    /**
     * {@inheritdoc}
     * Forbidden
     * @throws \LogicException
     */
    final public function offsetUnset($offset)
    {
        throw new \LogicException('Trace is read-only');
    }

    /**
     * {@inheritdoc}
     */
    final public function __toString()
    {
        return Represent::trace($this->props['items']);
    }

    /**
     * Check a backtrace item for truncate
     *
     * @param array $item
     *        an item of the backtrace
     * @param array $options
     * @return mixed
     *         result as FILTER_* constant
     */
    protected function filterItem(array $item, array $options)
    {
        if ($options['filter']) {
            $f = call_user_func($options['filter'], $item);
            if ($f) {
                return $f;
            }
        }
        if (!empty($item['class'])) {
            $result = $this->filterByClass($item, $options);
            if ($result !== false) {
                return $result;
            }
        }
        if (!empty($item['file'])) {
            return $this->filterByFile($item, $options);
        }
        return self::FILTER_SKIP;
    }

    /**
     * @param array $item
     * @param array $options
     * @return mixed
     */
    private function filterByClass($item, $options)
    {
        if ($options['namespace']) {
            if (strpos($item['class'], $options['namespace'].'\\') === 0) {
                return self::FILTER_LEAVE;
            }
        }
        if ($options['class']) {
            if ($item['class'] === $options['class']) {
                return self::FILTER_LEAVE;
            }
        }
        return false;
    }

    /**
     * @param array $item
     * @param array $options
     * @return mixed
     */
    private function filterByFile($item, $options)
    {
        if ($options['dir']) {
            if (strpos($item['file'], $options['dir']) === 0) {
                return self::FILTER_LEFT;
            }
        }
        if ($options['file']) {
            if ($item['file'] === $options['file']) {
                return self::FILTER_LEFT;
            }
        }
        return false;
    }

    /**
     * The list of all fields of a trace item (with default values)
     *
     * @var array
     */
    protected $defaultItem = [
        'function' => null,
        'line' => null,
        'file' => null,
        'class' => null,
        'object' => null,
        'type' => null,
        'args' => [],
    ];

    /**
     * The list of default options for truncate()
     *
     * @var array
     */
    protected $defaultOptions = [
        'filter' => null,
        'namespace' => null,
        'class' => null,
        'file' => null,
        'dir' => null,
    ];

    /**
     * The current state of the backtrace
     *
     * @var array
     */
    protected $items;

    /**
     * The list of magic properties
     *
     * @var array
     */
    protected $props = [];
}
