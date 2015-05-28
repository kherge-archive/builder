<?php

namespace Box\Component\Builder\Event\Listener;

use Box\Component\Builder\Event\PostAddEmptyDirEvent;
use Box\Component\Builder\Event\PostAddFileEvent;
use Box\Component\Builder\Event\PostAddFromStringEvent;
use Box\Component\Builder\Event\PostBuildFromDirectoryEvent;
use Box\Component\Builder\Event\PostBuildFromIteratorEvent;
use Box\Component\Builder\Event\PreAddEmptyDirEvent;
use Box\Component\Builder\Event\PreAddFileEvent;
use Box\Component\Builder\Event\PreAddFromStringEvent;
use Box\Component\Builder\Event\PreBuildFromDirectoryEvent;
use Box\Component\Builder\Event\PreBuildFromIteratorEvent;
use Box\Component\Builder\Events;
use Box\Component\Builder\Iterator\LoggerIterator;
use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * Logs events and sources that are added to an archive.
 *
 * @author Kevin Herrera <kevin@herrera.io>
 */
class LoggerSubscriber implements EventSubscriberInterface, LoggerAwareInterface
{
    /**
     * The logger.
     *
     * @var LoggerInterface
     */
    private $logger;

    /**
     * Sets the logger.
     *
     * @param LoggerInterface $logger The logger.
     */
    public function __construct(LoggerInterface $logger)
    {
        $this->setLogger($logger);
    }

    /**
     * {@inheritdoc}
     */
    public static function getSubscribedEvents()
    {
        return array(
            Events::POST_ADD_EMPTY_DIR => array('onPostAddEmptyDir', 110),
            Events::POST_ADD_FILE => array('onPostAddFile', 110),
            Events::POST_ADD_FROM_STRING => array('onPostAddFromString', 110),
            Events::POST_BUILD_FROM_DIRECTORY => array('onPostBuildFromDirectory', 110),
            Events::POST_BUILD_FROM_ITERATOR => array('onPostBuildFromIterator', 110),
            Events::POST_SET_STUB => array('onPostSetStub', 110),

            Events::PRE_ADD_EMPTY_DIR => array('onPreAddEmptyDir', -110),
            Events::PRE_ADD_FILE => array('onPreAddFile', -110),
            Events::PRE_ADD_FROM_STRING => array('onPreAddFromString', -110),
            Events::PRE_BUILD_FROM_DIRECTORY => array('onPreBuildFromDirectory', -110),
            Events::PRE_BUILD_FROM_ITERATOR => array('onPreBuildFromIterator', -110),
            Events::PRE_SET_STUB => array('onPreSetStub', -110)
        );
    }

    /**
     * Logs when an empty directory has been added.
     *
     * @param PostAddEmptyDirEvent $event The event arguments.
     */
    public function onPostAddEmptyDir(PostAddEmptyDirEvent $event)
    {
        $this->logger->info(
            sprintf(
                'The empty directory "%s" has been added.',
                basename($event->getLocal())
            ),
            array(
                'local' => $event->getLocal()
            )
        );
    }

    /**
     * Logs when a file is added.
     *
     * @param PostAddFileEvent $event The event arguments.
     */
    public function onPostAddFile(PostAddFileEvent $event)
    {
        $this->logger->info(
            sprintf(
                'The file "%s" has been added as "%s".',
                basename($event->getFile()),
                (null === $event->getLocal())
                    ? basename($event->getFile())
                    : basename($event->getLocal())
            ),
            array(
                'file' => $event->getFile(),
                'local' => $event->getLocal()
            )
        );
    }

    /**
     * Logs when a file is added from a string.
     *
     * @param PostAddFromStringEvent $event The event arguments.
     */
    public function onPostAddFromString(PostAddFromStringEvent $event)
    {
        $this->logger->info(
            sprintf(
                'The string has been added as "%s".',
                basename($event->getLocal())
            ),
            array(
                'local' => $event->getLocal()
            )
        );
    }

    /**
     * Logs when a directory is added.
     *
     * @param PostBuildFromDirectoryEvent $event The event arguments.
     */
    public function onPostBuildFromDirectory(PostBuildFromDirectoryEvent $event)
    {
        $this->logger->info(
            sprintf(
                'The directory "%s" has been added.',
                basename($event->getPath())
            ),
            array(
                'filter' => $event->getFilter(),
                'path' => $event->getPath()
            )
        );
    }

    /**
     * Logs when all of the items from an iterator are added.
     *
     * @param PostBuildFromIteratorEvent $event The event arguments.
     */
    public function onPostBuildFromIterator(PostBuildFromIteratorEvent $event)
    {
        $iterator = $event->getIterator();

        // @codeCoverageIgnoreStart
        if ($iterator instanceof LoggerIterator) {
            $class = get_class($iterator->getInnerIterator());
        } else {
            $class = get_class($iterator);
        }
        // @codeCoverageIgnoreEnd

        $base = explode('\\', $class);
        $base = array_pop($base);

        $this->logger->info(
            sprintf(
                'The items from the "%s" iterator have been added.',
                $base
            ),
            array(
                'base' => $event->getBase(),
                'class' => $class
            )
        );
    }

    /**
     * Logs when a stub is set.
     */
    public function onPostSetStub()
    {
        $this->logger->info('The custom stub has been set.');
    }

    /**
     * Logs when an empty directory is about to be added.
     *
     * @param PreAddEmptyDirEvent $event The event arguments.
     */
    public function onPreAddEmptyDir(PreAddEmptyDirEvent $event)
    {
        $this->logger->info(
            sprintf(
                'The empty directory "%s" is about to be added.',
                basename($event->getLocal())
            ),
            array(
                'local' => $event->getLocal()
            )
        );
    }

    /**
     * Logs when a file is about to be added.
     *
     * @param PreAddFileEvent $event The event arguments.
     */
    public function onPreAddFile(PreAddFileEvent $event)
    {
        $this->logger->info(
            sprintf(
                'The file "%s" is about to be added as "%s".',
                basename($event->getFile()),
                (null === $event->getLocal())
                    ? basename($event->getFile())
                    : basename($event->getLocal())
            ),
            array(
                'file' => $event->getFile(),
                'local' => $event->getLocal()
            )
        );
    }

    /**
     * Logs when a file is about to be added from a string.
     *
     * @param PreAddFromStringEvent $event The event arguments.
     */
    public function onPreAddFromString(PreAddFromStringEvent $event)
    {
        $this->logger->info(
            sprintf(
                'The string is about to be added as "%s".',
                basename($event->getLocal())
            ),
            array(
                'local' => $event->getLocal()
            )
        );
    }

    /**
     * Logs when a directory is about to be added.
     *
     * @param PreBuildFromDirectoryEvent $event The event arguments.
     */
    public function onPreBuildFromDirectory(PreBuildFromDirectoryEvent $event)
    {
        $this->logger->info(
            sprintf(
                'The directory "%s" is about to be added.',
                basename($event->getPath())
            ),
            array(
                'filter' => $event->getFilter(),
                'path' => $event->getPath()
            )
        );
    }

    /**
     * Logs when all of the items from an iterator are about to be added.
     *
     * @param PreBuildFromIteratorEvent $event The event arguments.
     */
    public function onPreBuildFromIterator(PreBuildFromIteratorEvent $event)
    {
        $base = explode('\\', get_class($event->getIterator()));
        $base = array_pop($base);

        $this->logger->info(
            sprintf(
                'The items from the "%s" iterator are about to be added.',
                $base
            ),
            array(
                'base' => $event->getBase(),
                'class' => get_class($event->getIterator())
            )
        );

        $event->setIterator(
            new LoggerIterator(
                $event->getIterator(),
                $this->logger
            )
        );
    }

    /**
     * Logs when a stub is about to be set.
     */
    public function onPreSetStub()
    {
        $this->logger->info('The custom stub is about to be set.');
    }

    /**
     * {@inheritdoc}
     */
    public function setLogger(LoggerInterface $logger)
    {
        $this->logger = $logger;
    }
}
