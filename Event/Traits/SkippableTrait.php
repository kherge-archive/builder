<?php

namespace Box\Component\Builder\Event\Traits;

/**
 * Manages the process of skipping actions that would normally follow an event.
 *
 * @author Kevin Herrera <kevin@herrera.io>
 */
trait SkippableTrait
{
    /**
     * The skip flag.
     *
     * @var boolean
     */
    private $skipped = false;

    /**
     * Checks if further actions should be skipped.
     *
     * @return boolean Returns `true` if skipping is needed, `false` if not.
     */
    public function isSkipped()
    {
        return $this->skipped;
    }

    /**
     * Skips the actions that would normally follow the event.
     *
     * Calling this method will signal the code that dispatched the event that
     * no further action should be taken. Event propagation will also case once
     * this method is called.
     */
    public function skip()
    {
        $this->stopPropagation();

        $this->skipped = true;
    }

    /**
     * Stops the propagation of the event to further event listeners.
     */
    abstract public function stopPropagation();
}
