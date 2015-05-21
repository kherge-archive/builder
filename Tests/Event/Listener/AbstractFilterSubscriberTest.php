<?php

namespace Box\Component\Builder\Tests\Event\Listener;

use ArrayIterator;
use Box\Component\Builder\Event\Listener\AbstractFilterSubscriber;
use Box\Component\Builder\Event\PreAddEmptyDirEvent;
use Box\Component\Builder\Event\PreAddFileEvent;
use Box\Component\Builder\Event\PreAddFromStringEvent;
use Box\Component\Builder\Event\PreBuildFromDirectoryEvent;
use Box\Component\Builder\Event\PreBuildFromIteratorEvent;
use Box\Component\Builder\Events;
use Box\Component\Builder\Tests\AbstractBuilderTestCase;
use PHPUnit_Framework_MockObject_MockObject as MockObject;
use SplFileInfo;

/**
 * Verifies that the class functions as intended.
 *
 * @author Kevin Herrera <kevin@herrera.io>
 *
 * @covers \Box\Component\Builder\Event\Listener\AbstractFilterSubscriber
 */
class AbstractFilterSubscriberTest extends AbstractBuilderTestCase
{
    /**
     * The mock filter subscriber.
     *
     * @var AbstractFilterSubscriber|MockObject
     */
    private $subscriber;

    /**
     * Verifies that the event and priority are specified.
     */
    public function testGetSubscribedEvents()
    {
        self::assertEquals(
            array(
                Events::PRE_ADD_EMPTY_DIR => array(
                    'onAddEmptyDir',
                    100
                ),
                Events::PRE_ADD_FILE => array(
                    'onAddFile',
                    100
                ),
                Events::PRE_ADD_FROM_STRING => array(
                    'onAddFromString',
                    100
                ),
                Events::PRE_BUILD_FROM_DIRECTORY => array(
                    'onBuildFromDirectory',
                    100
                ),
                Events::PRE_BUILD_FROM_ITERATOR => array(
                    'onBuildFromIterator',
                    100
                )
            ),
            AbstractFilterSubscriber::getSubscribedEvents()
        );
    }

    /**
     * Verifies that we can filter empty directories.
     */
    public function testOnAddEmptyDir()
    {
        // only allow one path
        $this
            ->subscriber
            ->expects(self::exactly(2))
            ->method('isAllowed')
            ->with(self::anything(), true)
            ->willReturnCallback(
                function ($path) {
                    return ('/path/to/a' === $path);
                }
            )
        ;

        // make sure the allowed path is... allowed
        $event = new PreAddEmptyDirEvent($this->builder, '/path/to/a');

        $this->subscriber->onAddEmptyDir($event);

        self::assertFalse($event->isSkipped());

        // now make sure that disallowed paths are disallowed
        $event = new PreAddEmptyDirEvent($this->builder, '/path/to/b');

        $this->subscriber->onAddEmptyDir($event);

        self::assertTrue($event->isSkipped());
    }

    /**
     * Verifies that we can filter files.
     */
    public function testOnAddFileDir()
    {
        // only allow one path
        $this
            ->subscriber
            ->expects(self::exactly(4))
            ->method('isAllowed')
            ->with(self::anything(), false)
            ->willReturnCallback(
                function ($path) {
                    return ('/path/to/a' === $path);
                }
            )
        ;

        // make sure the allowed path is... allowed
        $event = new PreAddFileEvent(
            $this->builder,
            '/path/to/a'
        );

        $this->subscriber->onAddFile($event);

        self::assertFalse($event->isSkipped());

        // now make sure that disallowed paths are disallowed
        $event = new PreAddFileEvent($this->builder, '/path/to/b');

        $this->subscriber->onAddFile($event);

        self::assertTrue($event->isSkipped());

        // local paths count too
        $event = new PreAddFileEvent(
            $this->builder,
            '/path/to/a',
            '/path/to/b'
        );

        $this->subscriber->onAddFile($event);

        self::assertTrue($event->isSkipped());
    }

    /**
     * Verifies that we can filter files from string.
     */
    public function testOnAddFromString()
    {
        // only allow one path
        $this
            ->subscriber
            ->expects(self::exactly(2))
            ->method('isAllowed')
            ->with(self::anything(), false)
            ->willReturnCallback(
                function ($path) {
                    return ('to/a' === $path);
                }
            )
        ;

        // make sure the allowed path is... allowed
        $event = new PreAddFromStringEvent($this->builder, 'to/a');

        $this->subscriber->onAddFromString($event);

        self::assertFalse($event->isSkipped());

        // now make sure that disallowed paths are disallowed
        $event = new PreAddFromStringEvent($this->builder, 'to/b');

        $this->subscriber->onAddFromString($event);

        self::assertTrue($event->isSkipped());
    }

    /**
     * Verifies that we can filter directories.
     */
    public function testOnBuildFromDirectory()
    {
        // only allow one path
        $this
            ->subscriber
            ->expects(self::exactly(2))
            ->method('isAllowed')
            ->with(self::anything(), true)
            ->willReturnCallback(
                function ($path) {
                    return ('/path/to/a' === $path);
                }
            )
        ;

        // make sure the allowed path is... allowed
        $event = new PreBuildFromDirectoryEvent(
            $this->builder,
            '/path/to/a'
        );

        $this->subscriber->onBuildFromDirectory($event);

        self::assertFalse($event->isSkipped());

        // now make sure that disallowed paths are disallowed
        $event = new PreBuildFromDirectoryEvent(
            $this->builder,
            '/path/to/b'
        );

        $this->subscriber->onBuildFromDirectory($event);

        self::assertTrue($event->isSkipped());
    }

    /**
     * Verifies that we can filter entries in an iterator.
     *
     * @covers \Box\Component\Builder\Iterator\FilterIterator
     */
    public function testOnBuildFromIterator()
    {
        // only allow one path
        $this
            ->subscriber
            ->expects(self::exactly(5))
            ->method('isAllowed')
            ->with(self::anything(), true)
            ->willReturnCallback(
                function ($path) {
                    return (false !== strpos($path, 'to/a'));
                }
            )
        ;

        // make sure we only see allowed paths
        mkdir($this->dir . '/to/a', 0755, true);
        mkdir($this->dir . '/to/b', 0755, true);

        $iterator = new ArrayIterator(
            array(
                'to/a' => $this->dir . '/to/a',
                'to/b' => $this->dir . '/to/b'
            )
        );

        $event = new PreBuildFromIteratorEvent(
            $this->builder,
            $iterator
        );

        $this->subscriber->onBuildFromIterator($event);

        foreach ($event->getIterator() as $key => $value) {
            self::assertEquals('to/a', $key);
        }

        // make sure we only see allowed local paths
        $iterator = new ArrayIterator(
            array(
                $this->dir . '/to/a' => 123, // pretend it's SplFileObject
                'to/b' => new SplFileInfo($this->dir . '/to/b')
            )
        );

        $event = new PreBuildFromIteratorEvent(
            $this->builder,
            $iterator
        );

        $this->subscriber->onBuildFromIterator($event);

        foreach ($event->getIterator() as $key => $value) {
            self::assertEquals($this->dir . '/to/a', $key);
        }
    }

    /**
     * Creates a new mock filter subscriber.
     */
    protected function setUp()
    {
        parent::setUp();

        $this->subscriber = $this
            ->getMockBuilder('Box\Component\Builder\Event\Listener\AbstractFilterSubscriber')
            ->setMethods(array('isAllowed'))
            ->getMockForAbstractClass()
        ;
    }
}
