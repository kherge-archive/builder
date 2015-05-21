<?php

namespace Box\Component\Builder\Tests\Event\Listener;

use ArrayIterator;
use Box\Component\Builder\Event\Listener\ProcessorSubscriber;
use Box\Component\Builder\Event\PreAddFromStringEvent;
use Box\Component\Builder\Event\PreBuildFromIteratorEvent;
use Box\Component\Builder\Events;
use Box\Component\Builder\Tests\AbstractBuilderTestCase;
use Box\Component\Processor\CallbackProcessor;

/**
 * Verifies that the class functions as intended.
 *
 * @author Kevin Herrera <kevin@herrera.io>
 *
 * @coversDefaultClass \Box\Component\Builder\Event\Listener\ProcessorSubscriber
 *
 * @covers ::__construct
 */
class ProcessorSubscriberTest extends AbstractBuilderTestCase
{
    /**
     * The test processor.
     *
     * @var CallbackProcessor
     */
    private $processor;

    /**
     * The subscriber instance being tested.
     *
     * @var ProcessorSubscriber
     */
    private $subscriber;

    /**
     * Verifies that we are subscribed to the relevant events.
     *
     * @covers ::getSubscribedEvents
     */
    public function testGetSubscribedEvents()
    {
        $events = $this->subscriber->getSubscribedEvents();

        self::assertEquals(
            array('onAddFromString', -100),
            $events[Events::PRE_ADD_FROM_STRING]
        );

        self::assertEquals(
            array('onBuildFromIterator', -100),
            $events[Events::PRE_BUILD_FROM_ITERATOR]
        );
    }

    /**
     * Verifies that we can process file contents.
     *
     * @covers ::onAddFromString
     */
    public function testOnAddFromString()
    {
        $event = new PreAddFromStringEvent(
            $this->builder,
            'test.php',
            "<?php\necho \"Hello, world!\n\";"
        );

        $this->subscriber->onAddFromString($event);

        self::assertEquals(
            <<<CONTENTS
<?php
echo "Hello, world!\n";

echo "Hello, test.php!\n";
CONTENTS
            ,
            $event->getContents()
        );
    }

    /**
     * Verifies that we can process iterators.
     *
     * @covers ::onBuildFromIterator
     */
    public function testOnBuildFromIterator()
    {
        file_put_contents(
            $this->dir . '/a.php',
            '<?php echo "Hello, world!\n";'
        );

        $event = new PreBuildFromIteratorEvent(
            $this->builder,
            new ArrayIterator(
                array(
                    $this->dir . '/a.php' => new \SplFileInfo(
                        $this->dir . '/a.php'
                    )
                )
            ),
            $this->dir . '/'
        );

        $this->subscriber->onBuildFromIterator($event);

        self::assertInstanceOf(
            'Box\Component\Processor\ProcessorIterator',
            $event->getIterator()
        );

        foreach ($event->getIterator() as $key => $value) {
            self::assertEquals('a.php', $key);
        }
    }

    /**
     * Creates a new test processor and subscriber instance.
     */
    protected function setUp()
    {
        parent::setUp();

        $this->processor = new CallbackProcessor(
            function ($file) {
                return (bool) preg_match('/\.php$/', $file);
            },
            function ($file, $contents) {
                return $contents . "\n\necho \"Hello, $file!\n\";";
            }
        );

        $this->subscriber = new ProcessorSubscriber($this->processor);
    }
}
