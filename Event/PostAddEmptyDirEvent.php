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
     * The path to the directory.
     *
     * @var string
     */
    protected $path;

    /**
     * Sets the path to the directory.
     *
     * @param Builder $builder The builder.
     * @param string  $path    The path to the directory.
     */
    public function __construct(Builder $builder, $path)
    {
        $this->builder = $builder;
        $this->path = $path;
    }

    /**
     * Returns the path to the directory.
     *
     * @return string The path to the directory.
     */
    public function getPath()
    {
        return $this->path;
    }
}
