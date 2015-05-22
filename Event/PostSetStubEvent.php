<?php

namespace Box\Component\Builder\Event;

use Box\Component\Builder\Builder;
use Box\Component\Builder\Event\Traits\BuilderTrait;
use Symfony\Component\EventDispatcher\Event;

/**
 * Manages the data for setting a stub.
 *
 * @author Kevin Herrera <kevin@herrera.io>
 */
class PostSetStubEvent extends Event
{
    use BuilderTrait;

    /**
     * The stub.
     *
     * @var string
     */
    protected $stub;

    /**
     * Sets the builder, the stub, and the length.
     *
     * @param Builder $builder The builder.
     * @param string  $stub    The stub.
     */
    public function __construct(Builder $builder, $stub)
    {
        $this->builder = $builder;
        $this->stub = $stub;
    }

    /**
     * Returns the stub.
     *
     * @return string The stub.
     */
    public function getStub()
    {
        return $this->stub;
    }
}
