<?php

namespace Box\Component\Builder\Event;

use Box\Component\Builder\Builder;
use Box\Component\Builder\Event\Traits\BuilderTrait;
use Symfony\Component\EventDispatcher\Event;

/**
 * Manages the data for adding a file.
 *
 * @author Kevin Herrera <kevin@herrera.io>
 */
class PostAddFileEvent extends Event
{
    use BuilderTrait;

    /**
     * The path to the file.
     *
     * @var string
     */
    protected $file;

    /**
     * The path to the file in the archive.
     *
     * @var null|string
     */
    protected $local;

    /**
     * Sets the builder, the path to the file, and the archive file path.
     *
     * @param Builder     $builder The builder.
     * @param string      $file    The path to the file.
     * @param null|string $local   The path to the file in the archive.
     */
    public function __construct(Builder $builder, $file, $local = null)
    {
        $this->builder = $builder;
        $this->file = $file;
        $this->local = $local;
    }

    /**
     * Returns the path to the file.
     *
     * @return string The path to the file.
     */
    public function getFile()
    {
        return $this->file;
    }

    /**
     * Returns the path to the file in the archive.
     *
     * @return null|string The path to the file in the archive.
     */
    public function getLocal()
    {
        return $this->local;
    }
}
