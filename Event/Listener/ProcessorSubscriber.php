<?php

namespace Box\Component\Builder\Event\Listener;

use Box\Component\Builder\Events;
use Box\Component\Builder\Event\PreAddFromStringEvent;
use Box\Component\Builder\Event\PreBuildFromIteratorEvent;
use Box\Component\Processor\ProcessorInterface;
use Box\Component\Processor\ProcessorIterator;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * Processes relevant archive modification events through a processor.
 *
 * @author Kevin Herrera <kevin@herrera.io>
 */
class ProcessorSubscriber implements EventSubscriberInterface
{
    /**
     * The processor.
     *
     * @var ProcessorInterface
     */
    private $processor;

    /**
     * Sets the processor.
     *
     * @param ProcessorInterface $processor The processor.
     */
    public function __construct(ProcessorInterface $processor)
    {
        $this->processor = $processor;
    }

    /**
     * {@inheritdoc}
     */
    public static function getSubscribedEvents()
    {
        return array(
            Events::PRE_ADD_FROM_STRING => 'onAddFromString',
            Events::PRE_BUILD_FROM_ITERATOR => 'onBuildFromIterator'
        );
    }

    /**
     * Processes a file that is about to be added from a string.
     *
     * @param PreAddFromStringEvent $event The event arguments.
     */
    public function onAddFromString(PreAddFromStringEvent $event)
    {
        if ($this->processor->supports($event->getLocal())) {
            $event->setContents(
                $this->processor->processContents(
                    $event->getLocal(),
                    $event->getContents()
                )
            );
        }
    }

    /**
     * Processes files from an iterator as they are added.
     *
     * @param PreBuildFromIteratorEvent $event The event arguments.
     */
    public function onBuildFromIterator(PreBuildFromIteratorEvent $event)
    {
        $event->setIterator(
            new ProcessorIterator(
                $this->processor,
                $event->getIterator()
            )
        );
    }
}
