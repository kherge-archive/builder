<?php

namespace Box\Component\Builder;

use BadMethodCallException;
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
use Box\Component\Builder\Exception\BuilderException;
use Box\Component\Builder\Iterator\RegexIterator;
use Iterator;
use KHerGe\File\File;
use Phar;
use PharFileInfo;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

/**
 * Creates and modifies PHP archives with support for event dispatching.
 *
 * When an event dispatcher is registered with the builder, a series of events
 * become observable. These events are all listed in the `Events` class and can
 * be used by listeners and subscribers for registration. If an event dispatcher
 * is not set, the builder's behavior is identical to that of `Phar`.
 *
 * @author Kevin Herrera <kevin@herrera.io>
 */
class Builder extends Phar
{
    /**
     * The event dispatcher.
     *
     * @var EventDispatcherInterface
     */
    private $dispatcher;

    /**
     * Adds an empty directory to the archive.
     *
     * @param string $path The path to the directory.
     */
    public function addEmptyDir($path = null)
    {
        if (null === $this->dispatcher) {
            parent::addEmptyDir($path);

            return;
        }

        $event = new PreAddEmptyDirEvent($this, $path);

        $this->dispatcher->dispatch(
            Events::PRE_ADD_EMPTY_DIR,
            $event
        );

        if (!$event->isSkipped()) {
            parent::addEmptyDir($event->getPath());

            $event = new PostAddEmptyDirEvent($this, $event->getPath());

            $this->dispatcher->dispatch(
                Events::POST_ADD_EMPTY_DIR,
                $event
            );
        }
    }

    /**
     * Adds a file to the archive.
     *
     * @param string $file  The path to the file.
     * @param string $local The path to the file in the archive.
     */
    public function addFile($file, $local = null)
    {
        if (null === $this->dispatcher) {
            // @codeCoverageIgnoreStart
            if (null === $local) {
                parent::addFile($file);
            } else {
                parent::addFile($file, $local);
            }
            // @codeCoverageIgnoreEnd

            return;
        }

        $event = new PreAddFileEvent($this, $file, $local);

        $this->dispatcher->dispatch(
            Events::PRE_ADD_FILE,
            $event
        );

        if (!$event->isSkipped()) {
            /*
             * By using `Phar::addFile()` directly, it eliminates any
             * possibility of making changes to the contents of the file
             * before it is added to the archive. To work around this issue,
             * `Builder::addFromString()` is used to add the file after its
             * contents have been read.
             */
            $this->addFromString(
                (null === $event->getLocal())
                    ? $event->getFile()
                    : $event->getLocal(),
                $this->getFileContents($event->getFile())
            );

            $event = new PostAddFileEvent(
                $this,
                $event->getFile(),
                $event->getLocal()
            );

            $this->dispatcher->dispatch(
                Events::POST_ADD_FILE,
                $event
            );
        }
    }

    /**
     * Adds a file from a string to the archive.
     *
     * @param string $local    The path to the file in the archive.
     * @param string $contents The contents of the file.
     */
    public function addFromString($local, $contents = null)
    {
        if (null === $this->dispatcher) {
            parent::addFromString($local, $contents);

            return;
        }

        $event = new PreAddFromStringEvent($this, $local, $contents);

        $this->dispatcher->dispatch(
            Events::PRE_ADD_FROM_STRING,
            $event
        );

        if (!$event->isSkipped()) {
            parent::addFromString($event->getLocal(), $event->getContents());

            $event = new PostAddFromStringEvent(
                $this,
                $event->getLocal(),
                $event->getContents()
            );

            $this->dispatcher->dispatch(
                Events::POST_ADD_FROM_STRING,
                $event
            );
        }
    }

    /**
     * Recursively adds files from a directory to the archive.
     *
     * The `$regex` is used as a whitelist filter. Any path in the directory
     * that matches the regular expression will be added to the archive. Any
     * non-matching paths will be excluded.
     *
     * @param string $path   The path to the directory.
     * @param string $filter The regular expression filter.
     *
     * @return array An associative array mapping archive paths to file system paths.
     */
    public function buildFromDirectory($path, $filter = null)
    {
        if (null === $this->dispatcher) {
            return parent::buildFromDirectory($path, $filter);
        }

        $event = new PreBuildFromDirectoryEvent($this, $path, $filter);

        $this->dispatcher->dispatch(
            Events::PRE_BUILD_FROM_DIRECTORY,
            $event
        );

        $map = array();

        if (!$event->isSkipped()) {
            /*
             * By using `Phar::buildFromDirectory()` directly, it eliminates
             * any possibility of making changes to the files as they are
             * passed to the archive. To work around this issue, the data is
             * used to create an iterator achieving the same purpose, but using
             * `Builder::buildFromIterator()` instead.
             */
            $iterator = new RecursiveIteratorIterator(
                new RecursiveDirectoryIterator(
                    $event->getPath(),
                    RecursiveDirectoryIterator::CURRENT_AS_FILEINFO
                        | RecursiveDirectoryIterator::KEY_AS_PATHNAME
                        | RecursiveDirectoryIterator::SKIP_DOTS
                )
            );

            if (null !== $filter) {
                $iterator = new RegexIterator($iterator, $event->getFilter());
            }

            $map = $this->buildFromIterator($iterator, $event->getPath());

            $event = new PostBuildFromDirectoryEvent(
                $this,
                $event->getPath(),
                $event->getFilter()
            );

            $this->dispatcher->dispatch(
                Events::POST_BUILD_FROM_DIRECTORY,
                $event
            );
        }

        return $map;
    }

    /**
     * Adds files returned by an iterator.
     *
     * The base directory path is used to trim convert absolute paths into
     * relative file inside the archive. The path `/path/to/my/file.php`
     * would then become `my/file.php` if `/path/to/` is provided as the
     * base path. It is important to note that the trailing slash in the base
     * directory path is necessary.
     *
     * @param Iterator $iterator The iterator.
     * @param string   $base     The base directory path.
     *
     * @return array An associative array mapping archive paths to file system paths.
     */
    public function buildFromIterator($iterator, $base = null)
    {
        if (null === $this->dispatcher) {
            return parent::buildFromIterator($iterator, $base);
        }

        $event = new PreBuildFromIteratorEvent($this, $iterator, $base);

        $this->dispatcher->dispatch(
            Events::PRE_BUILD_FROM_ITERATOR,
            $event
        );

        $map = array();

        if (!$event->isSkipped()) {
            $map = parent::buildFromIterator(
                $event->getIterator(),
                $event->getBase()
            );

            $event = new PostBuildFromIteratorEvent(
                $this,
                $event->getIterator(),
                $event->getBase()
            );

            $this->dispatcher->dispatch(
                Events::POST_BUILD_FROM_ITERATOR,
                $event
            );
        }

        return $map;
    }

    /**
     * {@inheritdoc}
     *
     * @throws BuilderException If the archive could not be compressed.
     *
     * @codeCoverageIgnore
     */
    public function compressFiles($algorithm)
    {
        try {
            parent::compressFiles($algorithm);
        } catch (BadMethodCallException $exception) {
            if ('unable to create temporary file' !== $exception->getMessage()) {
                throw $exception;
            }

            throw new BuilderException(
                preg_replace(
                    '/\n+/',
                    ' ',
                <<<MESSAGE
There is a known bug in the phar extension with the compression of archives that
contain a large number of files. It is recommended that you increase the allowed
maximum number of open files and try again. On Linux, the command you need to
use is `ulimit`.
MESSAGE
                )
            );
        }
    }

    /**
     * Sets the event dispatcher.
     *
     * @param EventDispatcherInterface $dispatcher The event dispatcher.
     */
    public function setEventDispatcher(
        EventDispatcherInterface $dispatcher = null
    ) {
        $this->dispatcher = $dispatcher;
    }

    /**
     * Resolves the path to its `PharFileInfo` object.
     *
     * @param string $path The path to the file or directory.
     *
     * @return PharFileInfo The path object.
     */
    public function resolvePath($path)
    {
        $path = sprintf(
            'phar://%s/%s',
            $this->getAlias(),
            ltrim($path, '\\/')
        );

        if (file_exists($path)) {
            return new PharFileInfo($path);
        }

        return null;
    }

    /**
     * Returns the contents of a file.
     *
     * @param string $path The path to the file.
     *
     * @return string The contents of the file.
     */
    private function getFileContents($path)
    {
        $contents = '';
        $file = File::create($path);

        do {
            $contents .= $file->fgets();
        } while (!$file->eof());

        return $contents;
    }
}
