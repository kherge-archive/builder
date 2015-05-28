<?php

namespace Box\Component\Builder\Iterator;

use IteratorIterator;
use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerInterface;
use SplFileInfo;
use Traversable;

/**
 * Logs the paths returned by a compatible iterator.
 *
 * @author Kevin Herrera <kevin@herrera.io>
 */
class LoggerIterator extends IteratorIterator implements LoggerAwareInterface
{
    /**
     * The logger.
     *
     * @var LoggerInterface
     */
    private $logger;

    /**
     * Sets the inner iterator and logger.
     *
     * @param Traversable     $iterator The inner iterator.
     * @param LoggerInterface $logger   The logger.
     */
    public function __construct(Traversable $iterator, LoggerInterface $logger)
    {
        parent::__construct($iterator);

        $this->logger = $logger;
    }

    /**
     * {@inheritdoc}
     */
    public function current()
    {
        $key = $this->key();
        $current = parent::current();
        $path = $this->getPath($current);

        $this->logger->info(
            sprintf(
                'The path "%s" is about to be added as "%s".',
                basename($path),
                basename($key)
            ),
            array(
                'path' => $path,
                'local' => $key
            )
        );

        return $current;
    }

    /**
     * {@inheritdoc}
     *
     * @codeCoverageIgnore
     */
    public function setLogger(LoggerInterface $logger)
    {
        $this->logger = $logger;
    }

    /**
     * Returns the file path for the given resource.
     *
     * @param mixed $resource The resource.
     *
     * @return string The file path.
     *
     * @codeCoverageIgnore
     */
    private function getPath($resource)
    {
        if ($resource instanceof SplFileInfo) {
            return $resource->getPathname();
        } elseif ((false === strpos($resource, "\n")) && file_exists($resource)) {
            return $resource;
        }

        return '(' . gettype($resource) . ')';
    }
}
