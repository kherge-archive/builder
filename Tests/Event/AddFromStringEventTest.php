<?php

namespace Box\Component\Builder\Tests\Event;

use Box\Component\Builder\Event\PreAddFromStringEvent;

/**
 * Verifies that the class functions as intended.
 *
 * @author Kevin Herrera <kevin@herrera.io>
 *
 * @covers \Box\Component\Builder\Event\PostAddFromStringEvent
 * @covers \Box\Component\Builder\Event\PreAddFromStringEvent
 */
class AddFromStringEventTest extends AbstractEventTestCase
{
    /**
     * The contents.
     *
     * @var string
     */
    private $contents = '<?php echo "Hello, world!";';

    /**
     * The event.
     *
     * @var PreAddFromStringEvent
     */
    private $event;

    /**
     * The local path.
     *
     * @var string
     */
    private $local = 'to/a.php';

    /**
     * Verifies that we can set and retrieve the contents.
     */
    public function testFile()
    {
        self::assertEquals($this->contents, $this->event->getContents());
        self::assertSame(
            $this->event,
            $this->event->setContents('<?php echo "Goodbye, world!";')
        );
        self::assertEquals(
            '<?php echo "Goodbye, world!";',
            $this->event->getContents()
        );
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

        $this->event = new PreAddFromStringEvent(
            $this->builder,
            $this->local,
            $this->contents
        );
    }
}
