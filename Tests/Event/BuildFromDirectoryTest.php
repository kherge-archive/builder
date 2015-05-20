<?php

namespace Box\Component\Builder\Tests\Event;

use Box\Component\Builder\Event\PreBuildFromDirectoryEvent;

/**
 * Verifies that the class functions as intended.
 *
 * @author Kevin Herrera <kevin@herrera.io>
 *
 * @covers \Box\Component\Builder\Event\PostBuildFromDirectoryEvent
 * @covers \Box\Component\Builder\Event\PreBuildFromDirectoryEvent
 */
class BuildFromDirectoryEventTest extends AbstractEventTestCase
{
    /**
     * The directory path.
     *
     * @var string
     */
    private $dir = '/path/to/a';

    /**
     * The event.
     *
     * @var PreBuildFromDirectoryEvent
     */
    private $event;

    /**
     * The filter.
     *
     * @var string
     */
    private $filter = '/\.php$/';

    /**
     * Verifies that we can set and retrieve the filter.
     */
    public function testFilter()
    {
        self::assertEquals($this->filter, $this->event->getFilter());
        self::assertSame($this->event, $this->event->setFilter('/\.txt$/'));
        self::assertEquals('/\.txt$/', $this->event->getFilter());
    }

    /**
     * Verifies that we can set and retrieve the directory path.
     */
    public function testPath()
    {
        self::assertEquals($this->dir, $this->event->getPath());
        self::assertSame($this->event, $this->event->setPath('/path/to/b'));
        self::assertEquals('/path/to/b', $this->event->getPath());
    }

    /**
     * Creates a new instance of the event.
     */
    protected function setUp()
    {
        parent::setUp();

        $this->event = new PreBuildFromDirectoryEvent(
            $this->builder,
            $this->dir,
            $this->filter
        );
    }
}
