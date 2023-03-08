<?php
/**
 * @package axy\errors
 * @author Oleg Grigoriev <go.vasac@gmail.com>
 */

namespace axy\errors;

/**
 * Trying to change a readonly value
 *
 * @link https://github.com/axypro/errors/blob/master/doc/errors.md documentation
 */
interface ReadOnly extends Forbidden
{
}
