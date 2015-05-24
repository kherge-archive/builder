<?php

namespace Box\Component\Builder\Event;

use Box\Component\Builder\Builder;
use Box\Component\Builder\Event\Traits\BuilderTrait;
use Symfony\Component\EventDispatcher\Event;

/**
 * Manages the data for adding an empty directory.
 *
 * @author Kevin Herrera <kevin@herrera.io>
 */
class PostAddEmptyDirEvent extends Event
{
    use BuilderTrait;

    /**
     * The path to the directory in the archive.
     *
     * @var string
     */
    protected $local;

    /**
     * Sets the path to the directory in the archive.
     *
     * @param Builder $builder The builder.
     * @param string  $path    The path to the directory.
     */
    public function __construct(Builder $builder, $path)
    {
        $this->builder = $builder;
        $this->local = $path;
    }

    /**
     * Returns the path to the directory in the archive.
     *
     * @return string The path to the directory in the archive.
     */
    public function getLocal()
    {
        return $this->local;
    }
}
