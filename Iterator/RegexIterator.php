<?php

namespace Box\Component\Builder\Iterator;

use FilterIterator;
use Iterator;

/**
 * A `Phar::buildFromIterator()` compatible regex filter iterator.
 *
 * @author Kevin Herrera <kevin@herrera.io>
 */
class RegexIterator extends FilterIterator
{
    /**
     * The regular expression filter.
     *
     * @var string
     */
    private $filter;

    /**
     * Sets the iterator and regular expression filter.
     *
     * @param Iterator $iterator The iterator.
     * @param string   $filter   The regular expression filter.
     */
    public function __construct(Iterator $iterator, $filter)
    {
        parent::__construct($iterator);

        $this->filter = $filter;
    }

    /**
     * {@inheritdoc}
     */
    public function accept()
    {
        if (preg_match($this->filter, $this->key())) {
            return true;
        }

        return false;
    }
}
