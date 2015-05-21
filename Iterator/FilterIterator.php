<?php

namespace Box\Component\Builder\Iterator;

use Box\Component\Builder\Event\Listener\AbstractFilterSubscriber;
use FilterIterator as Base;
use Iterator;
use SplFileInfo;

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
        $dir = $this->isCurrentDir();

        if ($this->subscriber->isAllowed($this->key(), $dir)) {
            if (is_string($this->current())) {
                return $this->subscriber->isAllowed($this->current(), $dir);
            }

            return true;
        }

        return false;
    }

    /**
     * Checks if the current entry is a directory.
     *
     * @return boolean Returns `true` if a directory, `false` if not.
     */
    private function isCurrentDir()
    {
        if (is_dir($this->key())) {
            return true;
        }

        if ($this->current() instanceof SplFileInfo) {
            return $this->current()->isDir();
        }

        return (is_string($this->current()) && is_dir($this->current()));
    }
}
