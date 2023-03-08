<?php
/**
 * @package axy\errors
 * @author Oleg Grigoriev <go.vasac@gmail.com>
 */

namespace axy\errors;

/**
 * This action is pointless in the current context
 *
 * @link https://github.com/axypro/errors/blob/master/doc/classes/Pointless.md documentation
 */
class Pointless extends Logic implements Forbidden
{
    /**
     * {@inheritdoc}
     */
    protected $defaultMessage = 'This action is pointless in the current context';
}
