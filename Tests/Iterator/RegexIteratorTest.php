<?php

namespace Box\Component\Builder\Tests\Iterator;

use ArrayIterator;
use Box\Component\Builder\Iterator\RegexIterator;
use PHPUnit_Framework_TestCase as TestCase;

/**
 * Verifies that the class functions as intended.
 *
 * @author Kevin Herrera <kevin@herrera.io>
 *
 * @coversDefaultClass \Box\Component\Builder\Iterator\RegexIterator
 */
class RegexIteratorTest extends TestCase
{
    /**
     * Verifies that we can filter an iterator using a regular expression.
     *
     * @covers ::__construct
     * @covers ::accept
     */
    public function testAccept()
    {
        $iterator = new RegexIterator(
            new ArrayIterator(
                array(
                    '/path/to/test.php' => 123,
                    '/path/to/test.jpg' => 456
                )
            ),
            '/\.php$/'
        );

        foreach ($iterator as $key => $value) {
            self::assertEquals('/path/to/test.php', $key);
            self::assertEquals(123, $value);
        }
    }
}
