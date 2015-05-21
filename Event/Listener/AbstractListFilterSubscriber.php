<?php

namespace Box\Component\Builder\Event\Listener;

/**
 * Manages the lists used for a list-based filter subscriber.
 *
 * @author Kevin Herrera <kevin@herrera.io>
 */
abstract class AbstractListFilterSubscriber extends AbstractFilterSubscriber
{
    /**
     * The list for directories.
     *
     * @var array
     */
    protected $directories;

    /**
     * The list for files.
     *
     * @var array
     */
    protected $files;

    /**
     * Sets the filter lists for files and directories.
     *
     * @param array $files       The file filters.
     * @param array $directories The directory filters.
     */
    public function __construct(array $files, array $directories)
    {
        $this->directories = $directories;
        $this->files = $files;
    }
}
