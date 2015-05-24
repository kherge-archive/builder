<?php

namespace Box\Component\Builder\Tests;

use Box\Component\Builder\Builder;
use Box\Component\Builder\Extract;
use KHerGe\File\Utility;
use PHPUnit_Framework_TestCase as TestCase;

/**
 * Verifies that the class functions as intended.
 *
 * @author Kevin Herrera <kevin@herrera.io>
 *
 * @covers \Box\Component\Builder\Extract
 */
class ExtractTest extends TestCase
{
    /**
     * The test paths to delete
     *
     * @var array
     */
    private $paths = array();

    /**
     * Verifies that we can find the end of the default stub.
     */
    public function testFindEndOfStub()
    {
        self::assertEquals(
            6683,
            Extract::findEndOfStub(
                $this->buildArchive()
            )
        );
    }

    /**
     * Verifies that we can find the end of a custom stub.
     */
    public function testFindEndOfStubCustom()
    {
        self::assertEquals(
            53,
            Extract::findEndOfStub(
                $this->buildArchive(
                    function (Builder $builder) {
                        $builder->setStub(
                            '<?php echo "Hello, world!\n"; __HALT_COMPILER(); ?>'
                        );
                    }
                ),
                Extract::getOpenPattern()
            )
        );
    }

    /**
     * Verifies that we can get an embeddable class source code.
     */
    public function testGetEmbedCode()
    {
        $code = Extract::getEmbedCode();

        self::assertContains("class Extract\n{", $code);;
        self::assertStringEndsWith("}\n", $code);
    }

    /**
     * Verifies that we can extract files from an archive.
     */
    public function testTo()
    {
        $file = $this->buildArchive();

        $this->paths[] = $dir = Extract::to($file);

        self::assertFileExists("$dir/a");
        self::assertFileExists("$dir/b/b");
        self::assertFileExists("$dir/c/c/c");
        self::assertFileExists("$dir/d/d/d/d");

        self::assertEquals('a', file_get_contents("$dir/a"));
        self::assertEquals('', file_get_contents("$dir/b/b"));
        self::assertEquals('c', file_get_contents("$dir/c/c/c"));

        // make sure the cache is preserved
        file_put_contents("$dir/b/b", 'x');

        Extract::to($file, $dir);

        self::assertEquals('x', file_get_contents("$dir/b/b"));

        // make sure the cache is destroyed
        $builder = new Builder($file);
        $builder->addFromString('e/e/e/e/e', 'e');
        $builder = null;

        Extract::to($file, $dir);

        self::assertEquals('e', file_get_contents("$dir/e/e/e/e/e"));
    }

    /**
     * Verifies that we can extract files from an bzip2 compressed archive.
     */
    public function testToBzip2()
    {
        $this->paths[] = $dir = tempnam(sys_get_temp_dir(), 'box-');

        unlink($dir);

        $file = $this->buildArchive(
            function (Builder $builder) {
                $builder->compressFiles(Builder::BZ2);
            }
        );

        Extract::to($file, $dir);

        self::assertFileExists("$dir/a");
        self::assertFileExists("$dir/b/b");
        self::assertFileExists("$dir/c/c/c");
        self::assertFileExists("$dir/d/d/d/d");

        self::assertEquals('a', file_get_contents("$dir/a"));
        self::assertEquals('', file_get_contents("$dir/b/b"));
        self::assertEquals('c', file_get_contents("$dir/c/c/c"));
    }

    /**
     * Verifies that we can extract files from an bzip2 compressed archive.
     */
    public function testToZlib()
    {
        $this->paths[] = $dir = tempnam(sys_get_temp_dir(), 'box-');

        unlink($dir);

        $file = $this->buildArchive(
            function (Builder $builder) {
                $builder->compressFiles(Builder::GZ);
            }
        );

        Extract::to($file, $dir);

        self::assertFileExists("$dir/a");
        self::assertFileExists("$dir/b/b");
        self::assertFileExists("$dir/c/c/c");
        self::assertFileExists("$dir/d/d/d/d");

        self::assertEquals('a', file_get_contents("$dir/a"));
        self::assertEquals('', file_get_contents("$dir/b/b"));
        self::assertEquals('c', file_get_contents("$dir/c/c/c"));
    }

    /**
     * Creates a new archive file for testing.
     *
     * @param callable $tweak Make tweaks to the archive.
     *
     * @return string The path the new archive file.
     */
    protected function buildArchive(callable $tweak = null)
    {
        $file = tempnam(sys_get_temp_dir(), 'box-');

        unlink($file);

        $this->paths[] = $file .= '.phar';

        $builder = new Builder($file, 0, basename($file));
        $builder->addFromString('a', 'a');
        $builder->addFromString('b/b', '');
        $builder->addFromString('c/c/c', 'c');
        $builder->addEmptyDir('d/d/d/d');

        if (null !== $tweak) {
            $tweak($builder);
        }

        $builder = null;

        return $file;
    }

    /**
     * Destroys the test archive file.
     */
    protected function tearDown()
    {
        foreach ($this->paths as $path) {
            if (file_exists($path)) {
                Utility::remove($path);
            }
        }
    }
}
