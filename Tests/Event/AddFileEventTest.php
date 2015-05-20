<?php

namespace Box\Component\Builder\Tests\Event;

use Box\Component\Builder\Event\PreAddFileEvent;

/**
 * Verifies that the class functions as intended.
 *
 * @author Kevin Herrera <kevin@herrera.io>
 *
 * @covers \Box\Component\Builder\Event\PostAddFileEvent
 * @covers \Box\Component\Builder\Event\PreAddFileEvent
 */
class AddFileEventTest extends AbstractEventTestCase
{
    /**
     * The event.
     *
     * @var PreAddFileEvent
     */
    private $event;

    /**
     * The file path.
     *
     * @var string
     */
    private $file = '/path/to/a.php';

    /**
     * The local path.
     *
     * @var string
     */
    private $local = 'to/a.php';

    /**
     * Verifies that we can set and retrieve the file path.
     */
    public function testFile()
    {
        self::assertEquals($this->file, $this->event->getFile());
        self::assertSame($this->event, $this->event->setFile('/path/to/b.php'));
        self::assertEquals('/path/to/b.php', $this->event->getFile());
    }

    /**
     * Verifies that we can set and retrieve the local path.
     */
    public function testLocal()
    {
        self::assertEquals($this->local, $this->event->getLocal());
        self::assertSame($this->event, $this->event->setLocal('to/b.php'));
        self::assertEquals('to/b.php', $this->event->getLocal());
    }

    /**
     * Creates a new instance of the event.
     */
    protected function setUp()
    {
        parent::setUp();

        $this->event = new PreAddFileEvent(
            $this->builder,
            $this->file,
            $this->local
        );
    }
}
