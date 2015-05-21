<?php

namespace Box\Component\Builder\Event\Listener;

use Box\Component\Builder\Event\PreAddEmptyDirEvent;
use Box\Component\Builder\Event\PreAddFileEvent;
use Box\Component\Builder\Event\PreAddFromStringEvent;
use Box\Component\Builder\Event\PreBuildFromDirectoryEvent;
use Box\Component\Builder\Event\PreBuildFromIteratorEvent;
use Box\Component\Builder\Events;
use Box\Component\Builder\Iterator\FilterIterator;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * Provides a basis for creating a filtering event subscriber.
 *
 * @author Kevin Herrera <kevin@herrera.io>
 */
abstract class AbstractFilterSubscriber implements EventSubscriberInterface
{
    /**
     * The priority.
     *
     * @var integer
     */
    protected static $priority = 100;

    /**
     * {@inheritdoc}
     */
    public static function getSubscribedEvents()
    {
        return array(
            Events::PRE_ADD_EMPTY_DIR => array(
                'onAddEmptyDir',
                static::$priority
            ),
            Events::PRE_ADD_FILE => array(
                'onAddFile',
                static::$priority
            ),
            Events::PRE_ADD_FROM_STRING => array(
                'onAddFromString',
                static::$priority
            ),
            Events::PRE_BUILD_FROM_DIRECTORY => array(
                'onBuildFromDirectory',
                static::$priority
            ),
            Events::PRE_BUILD_FROM_ITERATOR => array(
                'onBuildFromIterator',
                static::$priority
            )
        );
    }

    /**
     * Checks if a file or directory is allowed to be added to the archive.
     *
     * The `$path` may be a path in the file system, or a path to the file
     * or directory inside the archive. It is up to the implementing class
     * to determine how this difference should be handled.
     *
     * @param string $path The path to the file.
     *
     * @return boolean Returns `true` if allowed, `false` if not.
     */
    abstract public function isAllowed($path);

    /**
     * Filters empty directories that are about to be added.
     *
     * @param PreAddEmptyDirEvent $event The event arguments.
     */
    public function onAddEmptyDir(PreAddEmptyDirEvent $event)
    {
        if (!$this->isAllowed($event->getPath())) {
            $event->skip();
        }
    }

    /**
     * Filters files that are about to be added.
     *
     * @param PreAddFileEvent $event The event arguments.
     */
    public function onAddFile(PreAddFileEvent $event)
    {
        if (!$this->isAllowed($event->getFile())
            || ((null !== $event->getLocal())
                && !$this->isAllowed($event->getLocal()))) {
            $event->skip();
        }
    }

    /**
     * Filters file contents that are about to be added.
     *
     * @param PreAddFromStringEvent $event The event arguments.
     */
    public function onAddFromString(PreAddFromStringEvent $event)
    {
        if (!$this->isAllowed($event->getLocal())) {
            $event->skip();
        }
    }

    /**
     * Filters directories that are about to be added.
     *
     * Internally, `buildFromDirectory()` calls `buildFromIterator()`. This
     * means that this event will only perform a quick check against the path
     * of the directory itself, while `buildFromIterator()` will perform a more
     * thorough check against each entry returned by the iterator.
     *
     * @param PreBuildFromDirectoryEvent $event The event arguments.
     */
    public function onBuildFromDirectory(PreBuildFromDirectoryEvent $event)
    {
        if (!$this->isAllowed($event->getPath())) {
            $event->skip();
        }
    }

    /**
     * Filters files and directories from iterators that are about to be added.
     *
     * @param PreBuildFromIteratorEvent $event The event arguments.
     */
    public function onBuildFromIterator(PreBuildFromIteratorEvent $event)
    {
        $event->setIterator(
            new FilterIterator(
                $event->getIterator(),
                $this
            )
        );
    }
}
