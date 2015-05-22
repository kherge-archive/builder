<?php

namespace Box\Component\Builder\Tests\Event;

use Box\Component\Builder\Event\PreSetStubEvent;
use Box\Component\Builder\Tests\AbstractBuilderTestCase;

/**
 * Verifies that the class functions as intended.
 *
 * @author Kevin Herrera <kevin@herrera.io>
 *
 * @covers \Box\Component\Builder\Event\PostSetStubEvent
 * @covers \Box\Component\Builder\Event\PreSetStubEvent
 */
class SetStubTest extends AbstractBuilderTestCase
{
    /**
     * The event instance being tested.
     *
     * @var PreSetStubEvent
     */
    private $event;

    /**
     * The test stub.
     *
     * @var string
     */
    private $stub = '<?php echo "Hello, world!\n";';

    /**
     * Verifies that we can set and retrieve the stub.
     */
    public function testStub()
    {
        self::assertEquals(
            '<?php echo "Hello, world!\n";',
            $this->event->getStub()
        );

        self::assertSame(
            $this->event,
            $this->event->setStub('<?php echo "Goodbye, world!\n";')
        );

        self::assertEquals(
            '<?php echo "Goodbye, world!\n";',
            $this->event->getStub()
        );
    }

    /**
     * Creates a new event instance for testing.
     */
    protected function setUp()
    {
        parent::setUp();

        $this->event = new PreSetStubEvent(
            $this->builder,
            $this->stub
        );
    }
}
