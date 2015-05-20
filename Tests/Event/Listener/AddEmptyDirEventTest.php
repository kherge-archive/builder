<?php

namespace Box\Component\Builder\Tests\Event\Listener;

use Box\Component\Builder\Event\PreAddEmptyDirEvent;
use Box\Component\Builder\Tests\Event\AbstractEventTestCase;

/**
 * Verifies that the class functions as intended.
 *
 * @author Kevin Herrera <kevin@herrera.io>
 */
class AddEmptyDirEventTest extends AbstractEventTestCase
{
    /**
     * Verifies that we can set and retrieve the path.
     *
     * @covers \Box\Component\Builder\Event\PostAddEmptyDirEvent
     * @covers \Box\Component\Builder\Event\PreAddEmptyDirEvent
     */
    public function testPath()
    {
        $event = new PreAddEmptyDirEvent(
            $this->builder,
            '/path/to/a'
        );

        self::assertEquals('/path/to/a', $event->getPath());
        self::assertSame($event, $event->setPath('/path/to/b'));
        self::assertEquals('/path/to/b', $event->getPath());
    }
}
