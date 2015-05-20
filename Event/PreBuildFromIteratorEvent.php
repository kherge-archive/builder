<?php

namespace Box\Component\Builder\Event;

use Box\Component\Builder\Event\Traits\SkippableTrait;
use Iterator;

/**
 * Manages the data for building from an iterator.
 *
 * @author Kevin Herrera <kevin@herrera.io>
 */
class PreBuildFromIteratorEvent extends PostBuildFromIteratorEvent
{
    use SkippableTrait;

    /**
     * Sets the base directory path.
     *
     * @param null|string $base The base directory path.
     *
     * @return PreBuildFromIteratorEvent For method chaining.
     */
    public function setBase($base)
    {
        $this->base = $base;

        return $this;
    }

    /**
     * Sets the iterator.
     *
     * @param Iterator $iterator The iterator.
     *
     * @return PreBuildFromIteratorEvent For method chaining.
     */
    public function setIterator(Iterator $iterator)
    {
        $this->iterator = $iterator;

        return $this;
    }
}
