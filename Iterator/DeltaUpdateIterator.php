<?php

namespace Box\Component\Builder\Iterator;

use Box\Component\Builder\Builder;
use Box\Component\Builder\Event\Listener\DeltaUpdateSubscriber;
use Iterator;

/**
 * Filters an iterator using a `DeltaUpdateSubscriber` event subscriber.
 *
 * @author Kevin Herrera <kevin@herrera.io>
 */
class DeltaUpdateIterator extends \FilterIterator
{
    /**
     * The base directory path.
     *
     * @var null|string
     */
    private $base;

    /**
     * The builder.
     *
     * @var Builder
     */
    private $builder;

    /**
     * The event subscriber.
     *
     * @var DeltaUpdateSubscriber
     */
    private $subscriber;

    /**
     * Sets the iterator, event subscriber, and archive builder.
     *
     * @param Iterator              $iterator   The iterator.
     * @param DeltaUpdateSubscriber $subscriber The event subscriber.
     * @param Builder               $builder    The archive builder.
     * @param null|string           $base       The base directory path.
     */
    public function __construct(
        Iterator $iterator,
        DeltaUpdateSubscriber $subscriber,
        Builder $builder,
        $base = null
    ) {
        parent::__construct($iterator);

        if (null !== $base) {
            $base = '/^' . preg_quote($base, '/') . '/';
        }

        $this->base = $base;
        $this->builder = $builder;
        $this->subscriber = $subscriber;
    }

    /**
     * {@inheritdoc}
     */
    public function accept()
    {
        $local = $this->key();

        if (null !== $this->base) {
            $local = preg_replace($this->base, '', $local);
        }

        return $this->subscriber->isAllowed(
            $this->builder,
            $this->key(),
            $local
        );
    }
}
