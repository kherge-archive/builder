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
     * Sets the path to the directory.
     *
     * @param string $path The path to the directory.
     *
     * @return PreAddEmptyDirEvent For method chaining.
     */
    public function setPath($path)
    {
        $this->path = $path;

        return $this;
    }
}
