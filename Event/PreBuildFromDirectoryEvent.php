<?php

namespace Box\Component\Builder\Event;

use Box\Component\Builder\Event\Traits\SkippableTrait;

/**
 * Manages the data for building from a directory.
 *
 * @author Kevin Herrera <kevin@herrera.io>
 */
class PreBuildFromDirectoryEvent extends PostBuildFromDirectoryEvent
{
    use SkippableTrait;

    /**
     * Sets the regular expression filter.
     *
     * @param null|string $filter The regular expression filter.
     *
     * @return PreBuildFromDirectoryEvent For method chaining.
     */
    public function setFilter($filter)
    {
        $this->filter = $filter;

        return $this;
    }

    /**
     * Sets the path to the directory.
     *
     * @param string $path The path to the directory.
     *
     * @return PreBuildFromDirectoryEvent For method chaining.
     */
    public function setPath($path)
    {
        $this->path = $path;

        return $this;
    }
}
