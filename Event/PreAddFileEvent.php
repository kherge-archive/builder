<?php

namespace Box\Component\Builder\Event;

use Box\Component\Builder\Event\Traits\SkippableTrait;

/**
 * Manages the data for adding a file.
 *
 * @author Kevin Herrera <kevin@herrera.io>
 */
class PreAddFileEvent extends PostAddFileEvent
{
    use SkippableTrait;

    /**
     * Sets the path to the file.
     *
     * @param string $file The path to the file.
     *
     * @return PreAddFileEvent For method chaining.
     */
    public function setFile($file)
    {
        $this->file = $file;

        return $this;
    }

    /**
     * Sets the path to the file in the archive.
     *
     * @param null|string $local The path to the file in the archive.
     *
     * @return PreAddFileEvent For method chaining.
     */
    public function setLocal($local)
    {
        $this->local = $local;

        return $this;
    }
}
