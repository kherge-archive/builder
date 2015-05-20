<?php

namespace Box\Component\Builder\Tests\Event\Traits;

use Box\Component\Builder\Event\Traits\SkippableTrait;
use PHPUnit_Framework_TestCase as TestCase;

/**
 * Verifies that the trait functions as intended.
 *
 * @author Kevin Herrera <kevin@herrera.io>
 *
 * @coversDefaultClass \Box\Component\Builder\Event\Traits\SkippableTrait
 */
class SkippableTraitTest extends TestCase
{
    use SkippableTrait;

    /**
     * The propagation stopped flag.
     *
     * @var boolean
     */
    private $propagationStopped = false;

    /**
     * Verifies that we can skip actions.
     *
     * @covers ::isSkipped
     * @covers ::skip
     */
    public function testGetBuilder()
    {
        self::assertFalse($this->isSkipped());

        $this->skip();

        self::assertTrue($this->isSkipped());
        self::assertTrue($this->propagationStopped);
    }

    /**
     * Pretends to stop event propagation.
     */
    public function stopPropagation()
    {
        $this->propagationStopped = true;
    }
}
