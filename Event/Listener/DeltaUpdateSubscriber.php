<?php

namespace Box\Component\Builder\Event\Listener;

use Box\Component\Builder\Builder;
use Box\Component\Builder\Event\PreAddEmptyDirEvent;
use Box\Component\Builder\Event\PreAddFileEvent;
use Box\Component\Builder\Event\PreBuildFromIteratorEvent;
use Box\Component\Builder\Events;
use Box\Component\Builder\Exception\DeltaException;
use Box\Component\Builder\Iterator\DeltaUpdateIterator;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * Prevents unchanged files from being added to the archive.
 *
 * Delta updates can only be applied to files, and only to files exist on
 * the file system. Files that are added by strings do not have a timestamp
 * and cannot be changed for age.
 *
 * @author Kevin Herrera <kevin@herrera.io>
 */
class DeltaUpdateSubscriber implements EventSubscriberInterface
{
    /**
     * {@inheritdoc}
     */
    public static function getSubscribedEvents()
    {
        return array(
            Events::PRE_ADD_EMPTY_DIR => array('onAddEmptyDir', 50),
            Events::PRE_ADD_FILE => array('onAddFile', 50),
            Events::PRE_BUILD_FROM_ITERATOR => array('onBuildFromIterator', 50)
        );
    }

    /**
     * Checks if the file is newer than the file in the archive.
     *
     * @param Builder $builder The archive builder.
     * @param string $file     The path to the file.
     * @param string $local    The path to the file in the archive.
     *
     * @return boolean Returns `true` if `$file` is newer, `false` if not.
     */
    public function isAllowed(Builder $builder, $file, $local)
    {
        if (null === ($info = $builder->resolvePath($local))) {
            return true;
        }

        if (is_dir($file) || $info->isDir()) {
            return true;
        }

        if (false === ($fileTime = filemtime($file))) {
            throw DeltaException::cannotGetTimestamp($file); // @codeCoverageIgnore
        }

        if (false === ($localTime = $info->getMTime())) {
            throw DeltaException::cannotGetTimestamp($local); // @codeCoverageIgnore
        }

        return ($fileTime > $localTime);
    }

    /**
     * Skips empty directories if they already exist.
     *
     * @param PreAddEmptyDirEvent $event The event arguments.
     */
    public function onAddEmptyDir(PreAddEmptyDirEvent $event)
    {
        if ($event->getBuilder()->offsetExists($event->getLocal())
            && $event->getBuilder()->resolvePath($event->getLocal())->isDir()) {
            $event->skip();
        }
    }

    /**
     * Skips files that are unchanged.
     *
     * @param PreAddFileEvent $event The event arguments.
     */
    public function onAddFile(PreAddFileEvent $event)
    {
        if (null === ($local = $event->getLocal())) {
            $local = $event->getFile();
        }

        if (!$this->isAllowed($event->getBuilder(), $event->getFile(), $local)) {
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
            new DeltaUpdateIterator(
                $event->getIterator(),
                $this,
                $event->getBuilder(),
                $event->getBase()
            )
        );
    }
}
