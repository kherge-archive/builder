<?php

namespace Box\Component\Builder\Tests\Event;

use ArrayIterator;
use Box\Component\Builder\Event\PreBuildFromIteratorEvent;
use Box\Component\Builder\Tests\AbstractBuilderTestCase;

/**
 * Verifies that the class functions as intended.
 *
 * @author Kevin Herrera <kevin@herrera.io>
 *
 * @covers \Box\Component\Builder\Event\PostBuildFromIteratorEvent
 * @covers \Box\Component\Builder\Event\PreBuildFromIteratorEvent
 */
class BuildFromIteratorEventTest extends AbstractBuilderTestCase
{
    /**
     * The base directory path.
     *
     * @var string
     */
    private $base = '/path/to/a';

    /**
     * The event.
     *
     * @var PreBuildFromIteratorEvent
     */
    private $event;

    /**
     * The iterator.
     *
     * @var ArrayIterator
     */
    private $iterator;

    /**
     * Verifies that we can set and retrieve the base directory path.
     */
    public function testBase()
    {
        self::assertEquals($this->base, $this->event->getBase());
        self::assertSame($this->event, $this->event->setBase('/path/to/b'));
        self::assertEquals('/path/to/b', $this->event->getBase());
    }

    /**
     * Verifies that we can set and retrieve the iterator.
     */
    public function testIterator()
    {
        $iterator = new ArrayIterator(array());

        self::assertSame($this->iterator, $this->event->getIterator());
        self::assertSame($this->event, $this->event->setIterator($iterator));
        self::assertSame($iterator, $this->event->getIterator());
    }

    /**
     * Creates a new instance of the event.
     */
    protected function setUp()
    {
        parent::setUp();

        $this->iterator = new ArrayIterator(array());
        $this->event = new PreBuildFromIteratorEvent(
            $this->builder,
            $this->iterator,
            $this->base
        );
    }
}
