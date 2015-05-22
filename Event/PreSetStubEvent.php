<?php

namespace Box\Component\Builder\Event;

use Box\Component\Builder\Event\Traits\SkippableTrait;

/**
 * Manages the data for building from an iterator.
 *
 * @author Kevin Herrera <kevin@herrera.io>
 */
class PreSetStubEvent extends PostSetStubEvent
{
    use SkippableTrait;

    /**
     * Sets the stub.
     *
     * @param string $stub The stub.
     *
     * @return PreSetStubEvent For method chaining.
     */
    public function setStub($stub)
    {
        $this->stub = $stub;

        return $this;
    }
}
