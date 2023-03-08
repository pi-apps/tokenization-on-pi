<?php
/**
 * @package axy\errors
 * @author Oleg Grigoriev <go.vasac@gmail.com>
 */

namespace axy\errors\helpers;

use axy\backtrace\ExceptionTrace;
use axy\errors\Opts;

/**
 * Truncate the exception trace
 *
 * @link https://github.com/axypro/errors/blob/master/doc/backtrace.md documentation
 */
trait TraceTruncate
{
    /**
     * How to truncate a trace?
     *
     * array - the list options for the Trace::truncate()
     * string - namespace
     * true - go up one level namespace
     * false - do not truncate
     *
     * @var mixed
     */
    protected $howTruncateTrace = null;

    /**
     * Do truncate the native trace?
     *
     * @var null
     */
    protected $truncateNativeTrace = null;

    /**
     * Returns the filename of the original exception point
     *
     * @return int
     */
    final public function getOriginalFile()
    {
        return $this->truncatedTrace->originalFile;
    }

    /**
     * Returns the code line of the original exception point
     *
     * @return int
     */
    final public function getOriginalLine()
    {
        return $this->truncatedTrace->originalLine;
    }

    /**
     * Returns the truncated trace
     *
     * @return \axy\backtrace\ExceptionTrace
     */
    final public function getTruncatedTrace()
    {
        return $this->truncatedTrace;
    }

    /**
     * Truncates the trace by howTruncateTrace options
     *
     * @param mixed $thrower
     */
    private function truncateTrace($thrower)
    {
        /** @noinspection PhpUndefinedFieldInspection */
        /** @noinspection PhpUndefinedMethodInspection */
        $this->truncatedTrace = new ExceptionTrace($this->getTrace(), $this->file, $this->line);
        $options = $this->createOptionsForTruncateTrace($thrower);
        if (!$options) {
            return;
        }
        $this->truncatedTrace->truncate($options);
        /** @noinspection PhpUndefinedFieldInspection */
        $this->file = $this->truncatedTrace->file;
        /** @noinspection PhpUndefinedFieldInspection */
        $this->line = $this->truncatedTrace->line;
        $truncateNativeTrace = $this->truncateNativeTrace;
        if ($truncateNativeTrace === null) {
            $truncateNativeTrace = Opts::getTruncateNativeTrace();
        }
        if ($truncateNativeTrace) {
            /** @noinspection PhpParamsInspection */
            SetterTrace::setTrace($this, $this->truncatedTrace->items);
        }
    }

    /**
     * Creates an options list for truncate
     *
     * @param mixed $thrower
     * @return array
     */
    private function createOptionsForTruncateTrace($thrower)
    {
        if ($thrower) {
            if (is_string($thrower)) {
                return [
                    'namespace' => $thrower,
                ];
            }
            if (is_object($thrower)) {
                return [
                    'namespace' => preg_replace('/(\\\\[^\\\\]+)$/s', '', get_class($thrower)),
                ];
            }
            return null;
        }
        $options = $this->howTruncateTrace;
        if ($options === null) {
            $options = Opts::getHowTruncateTrace();
        }
        if (!$options) {
            return null;
        }
        if ($options === true) {
            $ns = preg_replace('/(\\\\[^\\\\]+)$/s', '', get_class($this));
            /* True: use the current namespace for errors.
             * It is nested namespace for the target namespace.
             * Truncate it.
             */
            if ($ns === 'axy\errors') {
                /* Trimming for axy\errors will result "axy". And it spreads to all axy-libs.
                 * Therefore, do not trim it.
                 * Return because axy\errors do not throws any exception.
                 * And return requires for TraceTruncateTest::testAxyNS()
                 */
                return null;
            }
            $ns = preg_replace('/(\\\\[^\\\\]+)$/s', '', $ns);
            $options = [
                'namespace' => $ns,
            ];
        } elseif (!is_array($options)) {
            $options = [
                'namespace' => $options,
            ];
        }
        return $options;
    }

    /**
     * The truncated trace
     *
     * @var \axy\backtrace\ExceptionTrace
     */
    private $truncatedTrace;
}
