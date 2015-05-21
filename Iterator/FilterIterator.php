<?php

namespace Box\Component\Builder\Iterator;

use Box\Component\Builder\Event\Listener\AbstractFilterSubscriber;
use FilterIterator as Base;
use Iterator;

/**
 * Filters an iterator using a `AbstractFilterSubscriber` event subscriber.
 *
 * @author Kevin Herrera <kevin@herrera.io>
 */
class FilterIterator extends Base
{
    /**
     * The event subscriber.
     *
     * @var AbstractFilterSubscriber
     */
    private $subscriber;

    /**
     * Sets the iterator and event subscriber.
     *
     * @param Iterator                 $iterator   The iterator.
     * @param AbstractFilterSubscriber $subscriber The event subscriber.
     */
    public function __construct(
        Iterator $iterator,
        AbstractFilterSubscriber $subscriber
    ) {
        parent::__construct($iterator);

        $this->subscriber = $subscriber;
    }

    /**
     * {@inheritdoc}
     */
    public function accept()
    {
        if ($this->subscriber->isAllowed($this->key())) {
            if (is_string($this->current())) {
                return $this->subscriber->isAllowed($this->current());
            }

            return true;
        }

        return false;
    }
}
