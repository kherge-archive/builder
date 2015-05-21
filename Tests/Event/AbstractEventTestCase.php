<?php

namespace Box\Component\Builder\Tests\Event;

use Box\Component\Builder\Builder;
use KHerGe\File\Utility;
use PHPUnit_Framework_MockObject_MockObject as MockObject;
use PHPUnit_Framework_TestCase as TestCase;

/**
 * Manages shared characteristics of event classes.
 *
 * @author Kevin Herrera <kevin@herrera.io>
 */
class AbstractEventTestCase extends TestCase
{
    /**
     * The builder.
     *
     * @var Builder|MockObject
     */
    protected $builder;

    /**
     * The builder output file.
     *
     * @var string
     */
    private $builderFile;

    /**
     * Creates a new mock builder.
     */
    protected function setUp()
    {
        $this->builderFile = tempnam(sys_get_temp_dir(), 'box-');

        unlink($this->builderFile);

        $this->builderFile .= '.phar';

        $this->builder = new Builder($this->builderFile);
    }

    /**
     * Destroys the builder file.
     */
    protected function tearDown()
    {
        if (file_exists($this->builderFile)) {
            Utility::remove($this->builderFile);
        }
    }
}
