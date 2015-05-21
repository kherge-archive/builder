<?php

namespace Box\Component\Builder\Event;

use Box\Component\Builder\Builder;
use Box\Component\Builder\Event\Traits\BuilderTrait;
use Symfony\Component\EventDispatcher\Event;

/**
 * Manages the data for adding a file from a string.
 *
 * @author Kevin Herrera <kevin@herrera.io>
 */
class PostAddFromStringEvent extends Event
{
    use BuilderTrait;

    /**
     * The contents of the file.
     *
     * @var null|string
     */
    protected $contents;

    /**
     * The path to the archive in the file.
     *
     * @var string
     */
    protected $local;

    /**
     * Sets the builder, file path in the archive, and file contents.
     *
     * @param Builder     $builder  The builder.
     * @param string      $local    The path to the file in the archive.
     * @param null|string $contents The contents of the file.
     */
    public function __construct(Builder $builder, $local, $contents = null)
    {
        $this->builder = $builder;
        $this->contents = $contents;
        $this->local = $local;
    }

    /**
     * Returns the contents of the file.
     *
     * @return null|string The contents of the file.
     */
    public function getContents()
    {
        return $this->contents;
    }

    /**
     * Returns the path to the archive in the file.
     *
     * @return string The path to the archive in the file.
     */
    public function getLocal()
    {
        return $this->local;
    }
}
