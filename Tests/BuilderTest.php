<?php

namespace Box\Component\Builder\Tests;

use Box\Component\Builder\Builder;
use Box\Component\Builder\Event\PostAddEmptyDirEvent;
use Box\Component\Builder\Event\PostAddFileEvent;
use Box\Component\Builder\Event\PostAddFromStringEvent;
use Box\Component\Builder\Event\PostBuildFromDirectoryEvent;
use Box\Component\Builder\Event\PostBuildFromIteratorEvent;
use Box\Component\Builder\Event\PostSetStubEvent;
use Box\Component\Builder\Event\PreAddEmptyDirEvent;
use Box\Component\Builder\Event\PreAddFileEvent;
use Box\Component\Builder\Event\PreAddFromStringEvent;
use Box\Component\Builder\Event\PreBuildFromDirectoryEvent;
use Box\Component\Builder\Event\PreBuildFromIteratorEvent;
use Box\Component\Builder\Event\PreSetStubEvent;
use Box\Component\Builder\Events;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use Symfony\Component\EventDispatcher\EventDispatcher;

/**
 * Verifies that the class functions as intended.
 *
 * @author Kevin Herrera <kevin@herrera.io>
 *
 * @covers \Box\Component\Builder\Builder
 */
class BuilderTest extends AbstractBuilderTestCase
{
    /**
     * The event dispatcher.
     *
     * @var EventDispatcher
     */
    private $dispatcher;

    /**
     * Verifies that the pre and post events for `addEmptyDir` are dispatched.
     */
    public function testAddEmptyDir()
    {
        // make sure the events are fired and changes are applied
        $post = null;

        $this->dispatcher->addListener(
            Events::PRE_ADD_EMPTY_DIR,
            function (PreAddEmptyDirEvent $event) {
                $event->setPath('b');
            }
        );

        $this->dispatcher->addListener(
            Events::POST_ADD_EMPTY_DIR,
            function (PostAddEmptyDirEvent $event) use (&$post) {
                $post = $event;
            }
        );

        $this->builder->addEmptyDir('a');

        self::assertArrayNotHasKey('a', $this->builder);
        self::assertArrayHasKey('b', $this->builder);
        self::assertInstanceOf(
            'Box\Component\Builder\Event\PostAddEmptyDirEvent',
            $post
        );

        // make sure the process can be skipped
        $this->removeListeners();

        $this->dispatcher->addListener(
            Events::PRE_ADD_EMPTY_DIR,
            function (PreAddEmptyDirEvent $event) {
                $event->skip();
            }
        );

        $this->builder->addEmptyDir('c');

        self::assertArrayNotHasKey('c', $this->builder);

        // make sure we can skip everything and go straight to `Phar`
        $this->builder->setEventDispatcher(null);

        $post = null;

        $this->builder->addEmptyDir('a');

        self::assertArrayHasKey('a', $this->builder);
        self::assertNull($post);
    }

    /**
     * Verifies that the pre and post events for `addFile` are dispatched.
     */
    public function testAddFile()
    {
        // create a file to add
        file_put_contents(
            $this->dir . '/test.php',
            '<?php echo "Hello, world!\n";'
        );

        // make sure the events are fired and changes are applied
        $post = null;

        $this->dispatcher->addListener(
            Events::PRE_ADD_FILE,
            function (PreAddFileEvent $event) {
                $file = $this->dir . '/other.php';

                file_put_contents(
                    $file,
                    '<?php echo "Goodbye, world!\n";'
                );

                $event->setFile($file);
                $event->setLocal('other.php');
            }
        );

        $this->dispatcher->addListener(
            Events::POST_ADD_FILE,
            function (PostAddFileEvent $event) use (&$post) {
                $post = $event;
            }
        );

        $this->builder->addFile($this->dir . '/test.php', 'test.php');

        self::assertArrayNotHasKey('test.php', $this->builder);
        self::assertArrayHasKey('other.php', $this->builder);
        self::assertInstanceOf(
            'Box\Component\Builder\Event\PostAddFileEvent',
            $post
        );
        self::assertEquals(
            '<?php echo "Goodbye, world!\n";',
            $this->getFileContents($this->builder['other.php'])
        );

        // make sure the process can be skipped
        $this->removeListeners();

        $this->dispatcher->addListener(
            Events::PRE_ADD_FILE,
            function (PreAddFileEvent $event) {
                $event->skip();
            }
        );

        $this->builder->addFile($this->dir . '/test.php', 'test.php');

        self::assertArrayNotHasKey('test.php', $this->builder);

        // make sure we can skip everything and go straight to `Phar`
        $this->builder->setEventDispatcher(null);

        $post = null;

        $this->builder->addFile($this->dir . '/test.php', 'test.php');

        self::assertArrayHasKey('test.php', $this->builder);
        self::assertNull($post);
    }

    /**
     * Verifies that the pre and post events for `addFromString` are dispatched.
     */
    public function testAddFromString()
    {
        // make sure the events are fired and changes are applied
        $post = null;

        $this->dispatcher->addListener(
            Events::PRE_ADD_FROM_STRING,
            function (PreAddFromStringEvent $event) {
                $event->setContents('<?php echo "Goodbye, world!\n";');
                $event->setLocal('other.php');
            }
        );

        $this->dispatcher->addListener(
            Events::POST_ADD_FROM_STRING,
            function (PostAddFromStringEvent $event) use (&$post) {
                $post = $event;
            }
        );

        $this->builder->addFromString(
            'test.php',
            '<?php echo "Hello, world\n!";'
        );

        self::assertArrayNotHasKey('test.php', $this->builder);
        self::assertArrayHasKey('other.php', $this->builder);
        self::assertInstanceOf(
            'Box\Component\Builder\Event\PostAddFromStringEvent',
            $post
        );
        self::assertEquals(
            '<?php echo "Goodbye, world!\n";',
            $this->getFileContents($this->builder['other.php'])
        );

        // make sure the process can be skipped
        $this->removeListeners();

        $this->dispatcher->addListener(
            Events::PRE_ADD_FROM_STRING,
            function (PreAddFromStringEvent $event) {
                $event->skip();
            }
        );

        $this->builder->addFromString(
            'test.php',
            '<?php echo "Hello, world\n!";'
        );

        self::assertArrayNotHasKey('test.php', $this->builder);

        // make sure we can skip everything and go straight to `Phar`
        $this->builder->setEventDispatcher(null);

        $post = null;

        $this->builder->addFromString(
            'test.php',
            '<?php echo "Hello, world\n!";'
        );

        self::assertArrayHasKey('test.php', $this->builder);
        self::assertEquals(
            '<?php echo "Hello, world\n!";',
            $this->getFileContents($this->builder['test.php'])
        );
        self::assertNull($post);
    }

    /**
     * Verifies that the pre and post events for `buildFromDirectory` are dispatched.
     */
    public function testBuildFromDirectory()
    {
        // create a test source directory
        mkdir($this->dir . '/src/a/sub', 0755, true);

        file_put_contents(
            $this->dir . '/src/a/sub/test.php',
            '<?php echo "Hello, world!\n";'
        );

        touch($this->dir . '/src/a/test.jpg');

        // make sure the events are fired and changes are applied
        $post = null;

        $this->dispatcher->addListener(
            Events::PRE_BUILD_FROM_DIRECTORY,
            function (PreBuildFromDirectoryEvent $event) {
                // create another test source directory
                mkdir($this->dir . '/src/b/sub', 0755, true);

                file_put_contents(
                    $this->dir . '/src/b/sub/other.php',
                    '<?php echo "Goodbye, world!\n";'
                );

                touch($this->dir . '/src/b/test.gif');

                $event->setFilter('/\.gif$/');
                $event->setPath($this->dir . '/src/b/');
            }
        );

        $this->dispatcher->addListener(
            Events::POST_BUILD_FROM_DIRECTORY,
            function (PostBuildFromDirectoryEvent $event) use (&$post) {
                $post = $event;
            }
        );

        $this->builder->buildFromDirectory(
            $this->dir . '/src/a',
            '/\.php$/'
        );

        self::assertArrayNotHasKey('sub/test.php', $this->builder);
        self::assertArrayNotHasKey('sub/other.php', $this->builder);
        self::assertArrayHasKey('test.gif', $this->builder);
        self::assertArrayNotHasKey('test.jpg', $this->builder);
        self::assertInstanceOf(
            'Box\Component\Builder\Event\PostBuildFromDirectoryEvent',
            $post
        );

        // make sure the process can be skipped
        $this->removeListeners();

        $this->dispatcher->addListener(
            Events::PRE_BUILD_FROM_DIRECTORY,
            function (PreBuildFromDirectoryEvent $event) {
                $event->skip();
            }
        );

        $this->builder->buildFromDirectory($this->dir . '/src/a');

        self::assertArrayNotHasKey('sub/test.php', $this->builder);
        self::assertArrayNotHasKey('test.jpg', $this->builder);

        // make sure we can skip everything and go straight to `Phar`
        $this->builder->setEventDispatcher(null);

        $post = null;

        $this->builder->buildFromDirectory(
            $this->dir . '/src/a',
            '/\.php$/'
        );

        self::assertArrayHasKey('sub/test.php', $this->builder);
        self::assertEquals(
            '<?php echo "Hello, world!\n";',
            $this->getFileContents($this->builder['sub/test.php'])
        );
        self::assertArrayNotHasKey('test.jpg', $this->builder);
        self::assertNull($post);
    }

    /**
     * Verifies that the pre and post events for `buildFromIterator` are dispatched.
     */
    public function testBuildFromIterator()
    {
        // create a test source directory
        mkdir($this->dir . '/src/a/sub', 0755, true);

        file_put_contents(
            $this->dir . '/src/a/sub/test.php',
            '<?php echo "Hello, world!\n";'
        );

        touch($this->dir . '/src/a/test.jpg');

        // make sure the events are fired and changes are applied
        $post = null;

        $this->dispatcher->addListener(
            Events::PRE_BUILD_FROM_ITERATOR,
            function (PreBuildFromIteratorEvent $event) {
                // create another test source directory
                mkdir($this->dir . '/src/b/sub', 0755, true);

                file_put_contents(
                    $this->dir . '/src/b/sub/other.php',
                    '<?php echo "Goodbye, world!\n";'
                );

                touch($this->dir . '/src/b/test.gif');

                $event->setBase($this->dir . '/src/b/');
                $event->setIterator(
                    new RecursiveIteratorIterator(
                        new RecursiveDirectoryIterator(
                            $this->dir . '/src/b',
                            RecursiveDirectoryIterator::CURRENT_AS_FILEINFO
                                | RecursiveDirectoryIterator::KEY_AS_PATHNAME
                                | RecursiveDirectoryIterator::SKIP_DOTS
                        )
                    )
                );
            }
        );

        $this->dispatcher->addListener(
            Events::POST_BUILD_FROM_ITERATOR,
            function (PostBuildFromIteratorEvent $event) use (&$post) {
                $post = $event;
            }
        );

        $this->builder->buildFromIterator(
            new RecursiveIteratorIterator(
                new RecursiveDirectoryIterator(
                    $this->dir . '/src/a',
                    RecursiveDirectoryIterator::CURRENT_AS_FILEINFO
                        | RecursiveDirectoryIterator::KEY_AS_PATHNAME
                        | RecursiveDirectoryIterator::SKIP_DOTS
                )
            ),
            $this->dir . '/src/a/'
        );

        self::assertArrayNotHasKey('sub/test.php', $this->builder);
        self::assertArrayNotHasKey('test.jpg', $this->builder);
        self::assertArrayHasKey('sub/other.php', $this->builder);
        self::assertEquals(
            '<?php echo "Goodbye, world!\n";',
            $this->getFileContents($this->builder['sub/other.php'])
        );
        self::assertArrayHasKey('test.gif', $this->builder);
        self::assertInstanceOf(
            'Box\Component\Builder\Event\PostBuildFromIteratorEvent',
            $post
        );

        // make sure the process can be skipped
        $this->removeListeners();

        $this->dispatcher->addListener(
            Events::PRE_BUILD_FROM_ITERATOR,
            function (PreBuildFromIteratorEvent $event) {
                $event->skip();
            }
        );

        $this->builder->buildFromIterator(
            new RecursiveIteratorIterator(
                new RecursiveDirectoryIterator(
                    $this->dir . '/src/a',
                    RecursiveDirectoryIterator::CURRENT_AS_FILEINFO
                        | RecursiveDirectoryIterator::KEY_AS_PATHNAME
                        | RecursiveDirectoryIterator::SKIP_DOTS
                )
            ),
            $this->dir . '/src/a/'
        );

        self::assertArrayNotHasKey('sub/test.php', $this->builder);
        self::assertArrayNotHasKey('test.jpg', $this->builder);

        // make sure we can skip everything and go straight to `Phar`
        $this->builder->setEventDispatcher(null);

        $post = null;

        $this->builder->buildFromIterator(
            new RecursiveIteratorIterator(
                new RecursiveDirectoryIterator(
                    $this->dir . '/src/a',
                    RecursiveDirectoryIterator::CURRENT_AS_FILEINFO
                        | RecursiveDirectoryIterator::KEY_AS_PATHNAME
                        | RecursiveDirectoryIterator::SKIP_DOTS
                )
            ),
            $this->dir . '/src/a/'
        );

        self::assertArrayHasKey('sub/test.php', $this->builder);
        self::assertEquals(
            '<?php echo "Hello, world!\n";',
            $this->getFileContents($this->builder['sub/test.php'])
        );
        self::assertArrayHasKey('test.jpg', $this->builder);
        self::assertNull($post);
    }

    /**
     * Verifies that we can resolve paths.
     */
    public function testResolvePath()
    {
        // create a test source directory
        mkdir($this->dir . '/src/a/sub', 0755, true);

        file_put_contents(
            $this->dir . '/src/a/sub/test.php',
            '<?php echo "Hello, world!\n";'
        );

        touch($this->dir . '/src/a/test.jpg');

        // add the source to the builder
        $this->builder->buildFromDirectory($this->dir . '/');

        // verify that we can resolve paths
        self::assertNull($this->builder->resolvePath('nope'));
        self::assertNull($this->builder->resolvePath('src/nope'));
        self::assertNull($this->builder->resolvePath('src/a/test.jpg/nope'));
        self::assertInstanceOf(
            'PharFileInfo',
            $this->builder->resolvePath('src/a/test.jpg')
        );
        self::assertInstanceOf(
            'PharFileInfo',
            $this->builder->resolvePath('src/a/sub/test.php')
        );
    }

    /**
     * Verifies that the pre and post events for `setStub` are dispatched.
     */
    public function testSetStub()
    {
        // make sure the events are fired and changes are applied
        $hello = '<?php echo "Hello, world!\n"; __HALT_COMPILER(); ?>';
        $goodbye = '<?php echo "Goodbye, world!\n"; __HALT_COMPILER(); ?>';
        $post = null;

        $this->dispatcher->addListener(
            Events::PRE_SET_STUB,
            function (PreSetStubEvent $event) use ($goodbye) {
                $event->setStub($goodbye);
            }
        );

        $this->dispatcher->addListener(
            Events::POST_SET_STUB,
            function (PostSetStubEvent $event) use (&$post) {
                $post = $event;
            }
        );

        $this->builder->setStub($hello);

        self::assertEquals($goodbye, trim($this->builder->getStub()));
        self::assertInstanceOf(
            'Box\Component\Builder\Event\PostSetStubEvent',
            $post
        );

        // make sure the process can be skipped
        $this->removeListeners();

        $this->builder->setStub(Builder::createDefaultStub());

        $this->dispatcher->addListener(
            Events::PRE_SET_STUB,
            function (PreSetStubEvent $event) {
                $event->skip();
            }
        );

        $this->builder->setStub($hello);

        self::assertEquals(
            trim(Builder::createDefaultStub()),
            trim($this->builder->getStub())
        );

        // make sure we can skip everything and go straight to `Phar`
        $this->builder->setEventDispatcher(null);

        $post = null;

        $this->builder->setStub($hello);

        self::assertEquals($hello, trim($this->builder->getStub()));
        self::assertNull($post);
    }

    /**
     * Creates temporary paths and sets up the builder.
     */
    protected function setUp()
    {
        parent::setUp();

        $this->dispatcher = new EventDispatcher();

        $this->builder->setEventDispatcher($this->dispatcher);
    }

    /**
     * Cleans up the temporary paths.
     */
    protected function tearDown()
    {
        $this->dispatcher = null;

        parent::tearDown();
    }

    /**
     * Removes all registered event listeners.
     */
    private function removeListeners()
    {
        $listeners = $this->dispatcher->getListeners();

        foreach ($listeners as $event => $list) {
            foreach ($list as $listener) {
                $this->dispatcher->removeListener($event, $listener);
            }
        }
    }
}
