<?php

namespace Box\Component\Builder\Tests\Iterator;

use ArrayIterator;
use Box\Component\Builder\Event\Listener\DeltaUpdateSubscriber;
use Box\Component\Builder\Iterator\DeltaUpdateIterator;
use Box\Component\Builder\Tests\AbstractBuilderTestCase;
use SplFileInfo;

/**
 * Verifies that the class functions as intended.
 *
 * @author Kevin Herrera <kevin@herrera.io>
 *
 * @covers \Box\Component\Builder\Iterator\DeltaUpdateIterator
 */
class DeltaUpdateIteratorTest extends AbstractBuilderTestCase
{
    /**
     * The delta update iterator.
     *
     * @var DeltaUpdateIterator
     */
    private $delta;

    /**
     * The test file.
     *
     * @var string
     */
    private $file;

    /**
     * The iterator.
     *
     * @var ArrayIterator
     */
    private $iterator;

    /**
     * The event subscriber.
     *
     * @var DeltaUpdateSubscriber
     */
    private $subscriber;

    /**
     * Verifies that new files are returned.
     */
    public function testAcceptNew()
    {
        $files = iterator_to_array($this->delta);

        self::assertArrayHasKey($this->file, $files);
    }

    /**
     * Verifies that updated files are returned.
     */
    public function testAcceptUpdated()
    {
        touch($this->file, time() + 100);

        $this->builder->addFromString('test.php');

        $files = iterator_to_array($this->delta);

        self::assertArrayHasKey($this->file, $files);
    }

    /**
     * Verifies that unchanged files are not returned.
     */
    public function testAcceptUnchanged()
    {
        $this->builder->addFromString('test.php');

        $files = iterator_to_array($this->delta);

        self::assertArrayNotHasKey($this->file, $files);
    }

    /**
     * Creates a new delta update subscriber.
     */
    protected function setUp()
    {
        parent::setUp();

        $this->file = $this->dir . '/test.php';

        touch($this->file);

        $this->iterator = new ArrayIterator(
            array(
                $this->file => new SplFileInfo($this->file)
            )
        );

        $this->subscriber = new DeltaUpdateSubscriber();

        $this->delta = new DeltaUpdateIterator(
            $this->iterator,
            $this->subscriber,
            $this->builder,
            $this->dir . '/'
        );
    }
}
