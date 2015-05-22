<?php

namespace Box\Component\Builder\Tests;

use Box\Component\Builder\Stub;
use PHPUnit_Framework_TestCase as TestCase;

/**
 * Verifies that the class functions as intended.
 *
 * @author Kevin Herrera <kevin@herrera.io>
 *
 * @covers \Box\Component\Builder\Stub
 */
class StubTest extends TestCase
{
    /**
     * Verifies that we can generate a stub.
     */
    public function testToString()
    {
        self::assertEquals(
            <<<STUB
#!/usr/bin/php
<?php

/**
 * This is the modified banner comment.
 */

if (class_exists('Phar')) {
    Phar::mapPhar('map.phar');
    Phar::interceptFileFuncs();
    Phar::mungServer(array (
  0 => 'PHP_SELF',
  1 => 'REQUEST_URI',
));
    Phar::loadPhar('/path/to/a.phar', 'a.phar');
    Phar::loadPhar('/path/to/b.phar', 'b.phar');
    Phar::mount('/path/to/c.phar', 'external/c');
    Phar::mount('/path/to/d.phar', 'external/d');
    Phar::webPhar('web.phar', 'f.php', 'g.php', array (
  'phps' => 1,
  'c' => 'text/plain',
  'cc' => 'text/plain',
  'cpp' => 'text/plain',
  'c++' => 'text/plain',
  'dtd' => 'text/plain',
  'h' => 'text/plain',
  'log' => 'text/plain',
  'rng' => 'text/plain',
  'txt' => 'text/plain',
  'xsd' => 'text/plain',
  'php' => 0,
  'inc' => 0,
  'avi' => 'video/avi',
  'bmp' => 'image/bmp',
  'css' => 'text/css',
  'gif' => 'image/gif',
  'htm' => 'text/html',
  'html' => 'text/html',
  'htmls' => 'text/html',
  'ico' => 'image/x-ico',
  'jpe' => 'image/jpeg',
  'jpg' => 'image/jpeg',
  'jpeg' => 'image/jpeg',
  'js' => 'application/x-javascript',
  'midi' => 'audio/midi',
  'mid' => 'audio/midi',
  'mod' => 'audio/mod',
  'mov' => 'movie/quicktime',
  'mp3' => 'audio/mp3',
  'mpg' => 'video/mpeg',
  'mpeg' => 'video/mpeg',
  'pdf' => 'application/pdf',
  'png' => 'image/png',
  'swf' => 'application/shockwave-flash',
  'tif' => 'image/tiff',
  'tiff' => 'image/tiff',
  'wav' => 'audio/wav',
  'xbm' => 'image/xbm',
  'xml' => 'text/xml',
), 'rewrite_uri');
} else {
    throw new RuntimeException('The phar extension is not installed.');
}

require 'phar://map.phar/e.php';

__HALT_COMPILER(); ?>
STUB
            ,
            (string) (new Stub())
                ->interceptFileFuncs()
                ->loadPhar('/path/to/a.phar', 'a.phar')
                ->loadPhar('/path/to/b.phar', 'b.phar')
                ->mapPhar('map.phar')
                ->mount('/path/to/c.phar', 'external/c')
                ->mount('/path/to/d.phar', 'external/d')
                ->mungServer(
                    array(
                        'PHP_SELF',
                        'REQUEST_URI'
                    )
                )
                ->setBanner(
                    <<<BANNER
/**
 * This is the modified banner comment.
 */
BANNER
                )
                ->setCode('require \'phar://map.phar/e.php\';')
                ->setShebang('#!/usr/bin/php')
                ->webPhar(
                    'web.phar',
                    'f.php',
                    'g.php',
                    null,
                    'rewrite_uri'
                )
        );
    }

    /**
     * Verifies that we can generate a minimal stub.
     */
    public function testGetToStringMinimal()
    {
        self::assertEquals(
            <<<STUB
<?php

require __FILE__ . "/test.php"

__HALT_COMPILER(); ?>
STUB
            ,
            (string) (new Stub())
                ->setBanner(null)
                ->setCode('require __FILE__ . "/test.php"')
                ->setShebang(null)
        );
    }
}
