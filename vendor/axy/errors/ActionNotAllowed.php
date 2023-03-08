<?php
/**
 * @package axy\errors
 * @author Oleg Grigoriev <go.vasac@gmail.com>
 */

namespace axy\errors;

/**
 * This action is not allowed for this object
 *
 * @link https://github.com/axypro/errors/blob/master/doc/classes/ActionNotAllowed.md documentation
 */
class ActionNotAllowed extends Logic
{
    /**
     * {@inheritdoc}
     */
    protected $defaultMessage = 'Action "{{ action }}" is not allowed for {{ object }} ({{ reason }})';

    /**
     * The constructor
     *
     * @param string $action
     *        the action name
     * @param object|string $object
     *        the object (or the object name)
     * @param string $reason [optional]
     *        a reason
     * @param \Exception $previous
     * @param mixed $thrower
     */
    public function __construct($action, $object, $reason = null, \Exception $previous = null, $thrower = null)
    {
        $this->action = $action;
        $this->object = $object;
        $this->reason = $reason;
        $message = [
            'action' => $action,
            'object' => $object,
            'reason' => $reason,
        ];
        parent::__construct($message, 0, $previous, $thrower);
    }

    /**
     * @return string
     */
    final public function getAction()
    {
        return $this->action;
    }

    /**
     * @return object|string
     */
    final public function getObject()
    {
        return $this->object;
    }

    /**
     * @return string
     */
    final public function getReason()
    {
        return $this->reason;
    }

    /**
     * @var string
     */
    protected $action;

    /**
     * @var object|string
     */
    protected $object;

    /**
     * @var string
     */
    protected $reason;
}
