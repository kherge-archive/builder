<?php

namespace Box\Component\Builder\Event;

use Box\Component\Builder\Event\Traits\SkippableTrait;

/**
 * Manages the data for adding a file from a string.
 *
 * @author Kevin Herrera <kevin@herrera.io>
 */
class PreAddFromStringEvent extends PostAddFromStringEvent
{
    use SkippableTrait;

    /**
     * Sets the contents of the file.
     *
     * @param null|string $contents The contents of the file.
     *
     * @return PreAddFromStringEvent For method chaining.
     */
    public function setContents($contents)
    {
        $this->contents = $contents;

        return $this;
    }

    /**
     * Sets the path to the archive in the file.
     *
     * @param string $local The path to the archive in the file.
     *
     * @return PreAddFromStringEvent For method chaining.
     */
    public function setLocal($local)
    {
        $this->local = $local;

        return $this;
    }
}
