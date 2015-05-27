<?php

namespace Box\Component\Builder\Event\Traits;

use Box\Component\Builder\Builder;

/**
 * Manages the builder instance.
 *
 * @author Kevin Herrera <kevin@herrera.io>
 */
trait BuilderTrait
{
    /**
     * The builder.
     *
     * @var Builder
     */
    protected $builder;

    /**
     * Returns the builder.
     *
     * @return Builder The builder.
     */
    public function getBuilder()
    {
        return $this->builder;
    }
}
