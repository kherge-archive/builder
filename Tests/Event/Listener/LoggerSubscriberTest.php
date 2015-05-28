<?php

namespace Box\Component\Builder\Tests\Event\Listener;

use ArrayIterator;
use Box\Component\Builder\Event\Listener\LoggerSubscriber;
use Box\Component\Builder\Tests\AbstractBuilderTestCase;
use Monolog\Handler\TestHandler;
use Monolog\Logger;
use Symfony\Component\EventDispatcher\EventDispatcher;

/**
 * Verifies that the class functions as intended.
 *
 * @author Kevin Herrera <kevin@herrera.io>
 */
class LoggerSubscriberTest extends AbstractBuilderTestCase
{
    /**
     * Verifies that all events are logged.
     */
    public function testListen()
    {
        // create the test sources
        mkdir($this->dir . '/src/a', 0755, true);
        mkdir($this->dir . '/src/b', 0755, true);
        mkdir($this->dir . '/src/c', 0755, true);

        file_put_contents($this->dir . '/src/a/a', 'a');
        file_put_contents($this->dir . '/src/b/b', 'b');
        file_put_contents($this->dir . '/src/c/c', 'c');

        // create the logger
        $handler = new TestHandler();

        $logger = new Logger('test');
        $logger->pushHandler($handler);

        // create the event dispatcher and register the subscriber
        $dispatcher = new EventDispatcher();
        $dispatcher->addSubscriber(
            new LoggerSubscriber($logger)
        );

        // register the dispatcher with the builder
        $this->builder->setEventDispatcher($dispatcher);

        // build the archive
        $this->builder->addEmptyDir('path/to/d');
        $this->builder->addFile($this->dir . '/src/a/a', 'path/to/e');
        $this->builder->addFromString('path/to/f', 'f');
        $this->builder->buildFromDirectory($this->dir . '/src/b', '/(^test)/');
        $this->builder->buildFromIterator(
            new ArrayIterator(
                array(
                    'c/c' => $this->dir . '/src/c/c'
                )
            ),
            $this->dir . '/src/'
        );
        $this->builder->setStub('<?php __HALT_COMPILER();');

        // make sure the log is what we expected
        $records = $handler->getRecords();
        $expected = array(
            array(
                'message' => 'The empty directory "d" is about to be added.',
                'context' => array(
                    'local' => 'path/to/d'
                )
            ),
            array(
                'message' => 'The empty directory "d" has been added.',
                'context' => array(
                    'local' => 'path/to/d'
                )
            ),
            array(
                'message' => 'The file "a" is about to be added as "e".',
                'context' => array(
                    'file' => $this->dir . '/src/a/a',
                    'local' => 'path/to/e'
                )
            ),
            array(
                'message' => 'The string is about to be added as "e".',
                'context' => array(
                    'local' => 'path/to/e'
                )
            ),
            array(
                'message' => 'The string has been added as "e".',
                'context' => array(
                    'local' => 'path/to/e'
                )
            ),
            array(
                'message' => 'The file "a" has been added as "e".',
                'context' => array(
                    'file' => $this->dir . '/src/a/a',
                    'local' => 'path/to/e'
                )
            ),
            array(
                'message' => 'The string is about to be added as "f".',
                'context' => array(
                    'local' => 'path/to/f'
                )
            ),
            array(
                'message' => 'The string has been added as "f".',
                'context' => array(
                    'local' => 'path/to/f'
                )
            ),
            array(
                'message' => 'The directory "b" is about to be added.',
                'context' => array(
                    'filter' => '/(^test)/',
                    'path' => $this->dir . '/src/b'
                )
            ),
            array(
                'message' => 'The items from the "RegexIterator" iterator are about to be added.',
                'context' => array(
                    'base' => $this->dir . '/src/b',
                    'class' => 'Box\Component\Builder\Iterator\RegexIterator'
                )
            ),
            array(
                'message' => 'The items from the "RegexIterator" iterator have been added.',
                'context' => array(
                    'base' => $this->dir . '/src/b',
                    'class' => 'Box\Component\Builder\Iterator\RegexIterator'
                )
            ),
            array(
                'message' => 'The directory "b" has been added.',
                'context' => array(
                    'filter' => '/(^test)/',
                    'path' => $this->dir . '/src/b'
                )
            ),
            array(
                'message' => 'The items from the "ArrayIterator" iterator are about to be added.',
                'context' => array(
                    'base' => $this->dir . '/src/',
                    'class' => 'ArrayIterator'
                )
            ),
            array(
                'message' => 'The path "c" is about to be added as "c".',
                'context' => array(
                    'path' => $this->dir . '/src/c/c',
                    'local' => 'c/c'
                )
            ),
            array(
                'message' => 'The items from the "ArrayIterator" iterator have been added.',
                'context' => array(
                    'base' => $this->dir . '/src/',
                    'class' => 'ArrayIterator'
                )
            ),
            array(
                'message' => 'The custom stub is about to be set.',
                'context' => array()
            ),
            array(
                'message' => 'The custom stub has been set.',
                'context' => array()
            )
        );

        foreach ($expected as $i => $e) {
            self::assertEquals($e['message'], $records[$i]['message']);
            self::assertEquals($e['context'], $records[$i]['context']);
        }
    }
}
