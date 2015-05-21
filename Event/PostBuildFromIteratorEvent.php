<?php

namespace Box\Component\Builder\Event;

use Box\Component\Builder\Builder;
use Box\Component\Builder\Event\Traits\BuilderTrait;
use Iterator;
use Symfony\Component\EventDispatcher\Event;

/**
 * Manages the data for building from an iterator.
 *
 * @author Kevin Herrera <kevin@herrera.io>
 */
class PostBuildFromIteratorEvent extends Event
{
    use BuilderTrait;

    /**
     * The base directory path.
     *
     * @var null|string
     */
    protected $base;

    /**
     * The iterator.
     *
     * @var Iterator
     */
    protected $iterator;

    /**
     * Sets the builder, iterator, and base directory path.
     *
     * @param Builder     $builder  The builder.
     * @param Iterator    $iterator The iterator.
     * @param null|string $base     The base directory path.
     */
    public function __construct(
        Builder $builder,
        Iterator $iterator,
        $base = null
    ) {
        $this->base = $base;
        $this->builder = $builder;
        $this->iterator = $iterator;
    }

    /**
     * Returns the base directory path.
     *
     * @return null|string The base directory path.
     */
    public function getBase()
    {
        return $this->base;
    }

    /**
     * Returns the iterator.
     *
     * @return Iterator The iterator.
     */
    public function getIterator()
    {
        return $this->iterator;
    }
}
