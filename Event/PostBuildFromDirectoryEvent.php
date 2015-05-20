<?php

namespace Box\Component\Builder\Event;

use Box\Component\Builder\Builder;
use Box\Component\Builder\Event\Traits\BuilderTrait;
use Symfony\Component\EventDispatcher\Event;

/**
 * Manages the data for building from a directory.
 *
 * @author Kevin Herrera <kevin@herrera.io>
 */
class PostBuildFromDirectoryEvent extends Event
{
    use BuilderTrait;

    /**
     * The regular expression filter.
     *
     * @var null|string
     */
    protected $filter;

    /**
     * The path to the directory.
     *
     * @var string
     */
    protected $path;

    /**
     * Sets the builder, path to the directory, and filter.
     *
     * @param Builder     $builder The builder.
     * @param string      $path    The path to the directory.
     * @param null|string $filter  The regular expression filter.
     */
    public function __construct(Builder $builder, $path, $filter)
    {
        $this->builder = $builder;
        $this->filter = $filter;
        $this->path = $path;
    }

    /**
     * Returns the regular expression filter.
     *
     * @return null|string The regular expression filter.
     */
    public function getFilter()
    {
        return $this->filter;
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
