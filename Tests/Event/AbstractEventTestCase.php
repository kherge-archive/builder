<?php

namespace Box\Component\Builder\Tests\Event;

use Box\Component\Builder\Builder;
use PHPUnit_Framework_MockObject_MockObject as MockObject;
use PHPUnit_Framework_TestCase as TestCase;
use ReflectionClass;

/**
 * Manages shared characteristics of event classes.
 *
 * @author Kevin Herrera <kevin@herrera.io>
 */
class AbstractEventTestCase extends TestCase
{
    /**
     * The mock builder.
     *
     * @var Builder|MockObject
     */
    protected $builder;

    /**
     * Creates a new mock builder.
     */
    protected function setUp()
    {
        $reflection = new ReflectionClass('Box\Component\Builder\Builder');
        $this->builder = $reflection->newInstanceWithoutConstructor();
    }
}
