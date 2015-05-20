<?php

namespace Box\Component\Builder\Tests\Event\Traits;

use Box\Component\Builder\Event\Traits\BuilderTrait;
use PHPUnit_Framework_TestCase as TestCase;

/**
 * Verifies that the trait functions as intended.
 *
 * @author Kevin Herrera <kevin@herrera.io>
 *
 * @coversDefaultClass \Box\Component\Builder\Event\Traits\BuilderTrait
 */
class BuilderTraitTest extends TestCase
{
    use BuilderTrait;

    /**
     * Verifies that we can set and retrieve a builder instance.
     *
     * @covers ::getBuilder
     */
    public function testGetBuilder()
    {
        $this->builder = $this;

        self::assertSame($this, $this->getBuilder());
    }
}
