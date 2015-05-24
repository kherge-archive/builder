<?php

namespace Box\Component\Builder\Event;

use Box\Component\Builder\Event\Traits\SkippableTrait;

/**
 * Manages the data for adding an empty directory.
 *
 * @author Kevin Herrera <kevin@herrera.io>
 */
class PreAddEmptyDirEvent extends PostAddEmptyDirEvent
{
    use SkippableTrait;

    /**
     * Sets the path to the directory in the archive.
     *
     * @param string $local The path to the directory in the archive.
     *
     * @return PreAddEmptyDirEvent For method chaining.
     */
    public function setLocal($local)
    {
        $this->local = $local;

        return $this;
    }
}
