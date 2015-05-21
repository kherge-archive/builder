<?php

namespace Box\Component\Builder\Tests;

use Box\Component\Builder\Builder;
use KHerGe\File\Utility;
use PHPUnit_Framework_TestCase as TestCase;

/**
 * Sets up an environment for testing related to the builder.
 *
 * @author Kevin Herrera <kevin@herrera.io>
 */
abstract class AbstractBuilderTestCase extends TestCase
{
    /**
     * The builder.
     *
     * @var Builder
     */
    protected $builder;

    /**
     * The builder file.
     *
     * @var string
     */
    protected $builderFile;

    /**
     * The temporary directory.
     *
     * @var string
     */
    protected $dir;

    /**
     * Creates a temporary builder and directory.
     */
    protected function setUp()
    {
        // create the builder file
        $this->builderFile = tempnam(sys_get_temp_dir(), 'box-');

        unlink($this->builderFile);

        $this->builderFile .= '.phar';

        // create the temporary directory
        $this->dir = tempnam(sys_get_temp_dir(), 'box-');

        unlink($this->dir);
        mkdir($this->dir);

        // create the builder
        $this->builder = new Builder($this->builderFile);
    }

    /**
     * Destroys the temporary paths.
     */
    protected function tearDown()
    {
        $this->builder = null;

        if (file_exists($this->builderFile)) {
            Utility::remove($this->builderFile);
        }

        if (file_exists($this->dir)) {
            Utility::remove($this->dir);
        }
    }
}
