<?php

namespace Box\Component\Builder\Tests\Event\Listener;

use ArrayIterator;
use Box\Component\Builder\Builder;
use Box\Component\Builder\Event\Listener\DeltaUpdateSubscriber;
use Box\Component\Builder\Event\PreAddEmptyDirEvent;
use Box\Component\Builder\Event\PreAddFileEvent;
use Box\Component\Builder\Event\PreBuildFromIteratorEvent;
use Box\Component\Builder\Events;
use Box\Component\Builder\Tests\AbstractBuilderTestCase;

/**
 * Verifies that the class functions as intended.
 *
 * @author Kevin Herrera <kevin@herrera.io>
 *
 * @covers \Box\Component\Builder\Event\Listener\DeltaUpdateSubscriber
 * @covers \Box\Component\Builder\Iterator\DeltaUpdateIterator
 */
class DeltaUpdateSubscriberTest extends AbstractBuilderTestCase
{
    /**
     * The subscriber.
     *
     * @var DeltaUpdateSubscriber
     */
    private $subscriber;

    /**
     * Verifies that the events and priority are specified.
     */
    public function testGetSubscribedEvents()
    {
        self::assertEquals(
            array(
                Events::PRE_ADD_EMPTY_DIR => array('onAddEmptyDir', 50),
                Events::PRE_ADD_FILE => array('onAddFile', 50),
                Events::PRE_BUILD_FROM_ITERATOR => array('onBuildFromIterator', 50)
            ),
            DeltaUpdateSubscriber::getSubscribedEvents()
        );
    }

    /**
     * Verifies that existing empty directories are skipped.
     */
    public function testOnAddEmptyDir()
    {
        $event = new PreAddEmptyDirEvent(
            $this->builder,
            'test'
        );

        // when directory not exist
        $this->subscriber->onAddEmptyDir($event);

        self::assertFalse($event->isSkipped());

        // when directory exists
        $this->builder->addEmptyDir('test');

        $this->subscriber->onAddEmptyDir($event);

        self::assertTrue($event->isSkipped());
    }

    /**
     * Verifies that existing files are skipped.
     */
    public function testOnAddFile()
    {
        touch($this->dir . '/test.php');

        $event = new PreAddFileEvent(
            $this->builder,
            $this->dir . '/test.php'
        );

        // when the file does not exist
        $this->subscriber->onAddFile($event);

        self::assertFalse($event->isSkipped());

        // when the file is newer
        touch($this->dir . '/test.php', time() + 100);

        $this->builder->addFile($this->dir . '/test.php');

        $event = null;
        $this->builder = null;
        $this->builder = new Builder($this->builderFile);
        $event = new PreAddFileEvent(
            $this->builder,
            $this->dir . '/test.php'
        );

        $this->subscriber->onAddFile($event);

        self::assertFalse($event->isSkipped());

        // when the file is actually a directory
        unlink($this->builder->resolvePath($this->dir . '/test.php')->getPathname());

        $this->builder->addEmptyDir($this->dir . '/test.php');

        $this->subscriber->onAddFile($event);

        self::assertFalse($event->isSkipped());

        // when the file is older or the same
        rmdir($this->builder->resolvePath($this->dir . '/test.php')->getPathname());

        $this->builder->addFile($this->dir . '/test.php');

        touch($this->dir . '/test.php', time() - 100);

        $this->subscriber->onAddFile($event);

        self::assertTrue($event->isSkipped());
    }

    /**
     * Verifies that iterator is wrapped with another iterator.
     */
    public function testOnBuildFromIterator()
    {
        $event = new PreBuildFromIteratorEvent(
            $this->builder,
            new ArrayIterator(array())
        );

        $this->subscriber->onBuildFromIterator($event);

        self::assertInstanceOf(
            'Box\Component\Builder\Iterator\DeltaUpdateIterator',
            $event->getIterator()
        );
    }

    /**
     * Creates a new instance of the subscriber for testing.
     */
    protected function setUp()
    {
        parent::setUp();

        $this->subscriber = new DeltaUpdateSubscriber();
    }
}
