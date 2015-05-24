<?php

namespace Box\Component\Builder;

use InvalidArgumentException;
use RuntimeException;

/**
 * Manages the process of extracting an archive file.
 *
 * @author Kevin Herrera <kevin@herrera.io>
 */
class Extract
{
    /**
     * The bzip2 compression flag.
     *
     * @var integer
     */
    const BZ2 = 0x2000;

    /**
     * The gzip compression flag.
     *
     * @var integer
     */
    const GZ = 0x1000;

    /**
     * The manifest flags mask.
     *
     * @var integer
     */
    const MASK = 0x3000;

    /**
     * Returns the offset where the end of the stub is located.
     *
     * @param string $file    The path to the archive file.
     * @param string $pattern The end-of-stub pattern.
     *
     * @return integer The offset in the file.
     *
     * @throws InvalidArgumentException If the pattern could not be found.
     */
    public static function findEndOfStub($file, $pattern = null)
    {
        if (null === $pattern) {
            $pattern = self::getDefaultPattern();
        }

        $pattern = sprintf($pattern, '__HALT_COMPILER');
        $handle = self::open($file);
        $offset = 0;
        $chars = str_split($pattern);
        $count = count($chars);
        $tell = null;

        while (null !== ($char = self::readChar($handle))) {
            if ($chars[$offset] === $char) {
                $offset++;

                if ($offset >= $count) {
                    $tell = ftell($handle);

                    break;
                }
            } else {
                $offset = 0;
            }
        }

        fclose($handle);

        // @codeCoverageIgnoreStart
        if (null === $tell) {
            throw new InvalidArgumentException(
                sprintf(
                    'The end-of-stub pattern "%s" could not be found in the file "%s".',
                    preg_replace('/\n/', '\n', $pattern),
                    $file
                )
            );
        }
        // @codeCoverageIgnoreEnd

        return $tell;
    }

    /**
     * Returns the default end-of-stub pattern.
     *
     * @return string The default pattern.
     */
    public static function getDefaultPattern()
    {
        return '%s(); ?>';
    }

    /**
     * Returns the class source code for embedding within a stub.
     *
     * @return string The embeddable source code.
     */
    public static function getEmbedCode()
    {
        // @codeCoverageIgnoreStart
        if (false === ($handle = fopen(__FILE__, 'r'))) {
            throw new RuntimeException(
                sprintf(
                    'The class file "%s" could not be opened for reading.',
                    __FILE__
                )
            );
        }
        // @codeCoverageIgnoreEnd

        // read the class file
        $lines = array();

        do {
            $lines[] = fgets($handle);
        } while (!feof($handle));

        fclose($handle);

        // only keep the class
        $lines = array_slice($lines, 4);

        // remove root "use"
        foreach ($lines as $i => $line) {
            if (preg_match('/^use\s*\w+;/', $line)) {
                unset($lines[$i]);
            }
        }

        return implode('', $lines);
    }

    /**
     * Returns the open end-of-stub pattern.
     *
     * @return string The open pattern.
     */
    public static function getOpenPattern()
    {
        return "%s(); ?>\r\n";
    }

    /**
     * Extracts an archive file to a directory.
     *
     * If an `$offset` is not provided, it will be automatically
     * discovered by finding the end-of-stub `$pattern` in the file contents.
     * If `$pattern` is not provided, the default end-of-stub pattern will be
     * used.
     *
     * @param string  $file    The path to the archive file.
     * @param string  $dir     The path to the output directory.
     * @param integer $offset  The archive data offset.
     * @param string  $pattern The end-of-stub pattern.
     *
     * @return string The path to the extracted archive directory.
     */
    public static function to(
        $file,
        $dir = null,
        $offset = null,
        $pattern = null
    ) {
        if (null === $offset) {
            $offset = self::findEndOfStub($file, $pattern);
        }

        if (null === $dir) {
            $dir = sprintf(
                '%s%spharextract%s%s',
                sys_get_temp_dir(),
                DIRECTORY_SEPARATOR,
                DIRECTORY_SEPARATOR,
                basename($file)
            );
        }

        $check = $dir . DIRECTORY_SEPARATOR . self::md5($file);

        if (file_exists($check)) {
            return $dir;
        }

        if (!is_dir($dir)) {
            self::createDir($dir);
        }

        $handle = self::open($file);

        self::seek($handle, $offset);

        $manifest = self::readManifest($handle);

        self::checkFlags($manifest['flags']);
        self::remove($dir);
        self::createFile($check);
        self::extractFiles($handle, $dir, $manifest['files']);

        fclose($handle);

        return $dir;
    }

    /**
     * Checks if the archive file can be extracted based on the manifest flags.
     *
     * @param integer $flags The manifest flags.
     *
     * @throws RuntimeException If the archive cannot be extracted.
     *
     * @codeCoverageIgnore
     */
    private static function checkFlags($flags)
    {
        if (($flags & self::BZ2) && !function_exists('bzdecompress')) {
            throw new RuntimeException(
                'The bz2 extension is required to extract this archive.'
            );
        }

        if (($flags & self::GZ) && !function_exists('gzinflate')) {
            throw new RuntimeException(
                'The zlib extension is required to extract this archive.'
            );
        }
    }

    /**
     * Attempts to create a new directory.
     *
     * @param string $dir        The path to the directory.
     * @param integer $chmod     The file permissions.
     * @param boolean $recursive Recursively create the path?
     *
     * @throws RuntimeException If the directory could not be created.
     *
     * @codeCoverageIgnore
     */
    private static function createDir($dir, $chmod = 0755, $recursive = true)
    {
        if (!mkdir($dir, $chmod, $recursive)) {
            throw new RuntimeException(
                sprintf(
                    'The directory "%s" could not be created.',
                    $dir
                )
            );
        }
    }

    /**
     * Attempts to create a new file.
     *
     * @param string  $file     The path to the file.
     * @param string  $contents The contents of the file.
     * @param integer $chmod    The file permissions.
     *
     * @throws RuntimeException If the file could not be created or modified.
     *
     * @codeCoverageIgnore
     */
    private static function createFile($file, $contents = null, $chmod = 0644)
    {
        $dir = dirname($file);

        if (!file_exists($dir)) {
            self::createDir($dir);
        }

        if (null === $contents) {
            if (!touch($file)) {
                throw new RuntimeException(
                    sprintf(
                        'The file "%s" could not be created.',
                        $file
                    )
                );
            }
        } else {
            if (false === file_put_contents($file, $contents)) {
                throw new RuntimeException(
                    sprintf(
                        'The file "%s" could not be created or written to.',
                        $file
                    )
                );
            }
        }

        if ((null !== $chmod) && (!chmod($file, $chmod))) {
            throw new RuntimeException(
                sprintf(
                    'The permissions on "%s" could not be set.',
                    $file
                )
            );
        }
    }

    /**
     * Extracts the files from the archive to the directory.
     *
     * @param resource $handle The file handle.
     * @param string   $dir    The path to the output directory.
     * @param array    $files  The list of file data.
     */
    private static function extractFiles($handle, $dir, array $files)
    {
        foreach ($files as $file) {
            $path = $dir . DIRECTORY_SEPARATOR . $file['path'];

            if (preg_match('{/$}', $file['path'])) {
                self::createDir($path);
            } else {
                self::createFile(
                    $path,
                    self::readFile($handle, $file)
                );
            }
        }
    }

    /**
     * Returns the MD5 checksum for a file.
     *
     * @param string $file The path to the file.
     *
     * @return string The MD5 checksum.
     *
     * @throws RuntimeException If the checksum could not be generated.
     *
     * @codeCoverageIgnore
     */
    private static function md5($file)
    {
        if (false === ($checksum = md5_file($file))) {
            throw new RuntimeException(
                sprintf(
                    'The MD5 checksum for "%s" could not be generated.',
                    $file
                )
            );
        }

        return $checksum;
    }

    /**
     * Attempts to open a file.
     *
     * @param string $path The path to the file.
     * @param string $mode The open mode.
     *
     * @return resource The file handle.
     *
     * @throws RuntimeException If the file could not be opened.
     *
     * @codeCoverageIgnore
     */
    private static function open($path, $mode = 'rb')
    {
        if (false === ($handle = fopen($path, $mode))) {
            throw new RuntimeException(
                sprintf(
                    'The file "%s" could not be opened (%s).',
                    $path,
                    $mode
                )
            );
        }

        return $handle;
    }

    /**
     * Attempts to read a number of bytes from the file.
     *
     * @param resource $handle The file handle.
     * @param integer  $bytes  The number of bytes.
     *
     * @return string The bytes read.
     *
     * @throws RuntimeException If the file could not be read.
     */
    private static function read($handle, $bytes)
    {
        $read = '';

        for ($i = 1; $i <= $bytes; $i++) {
            // @codeCoverageIgnoreStart
            if (null === ($char = self::readChar($handle))) {
                throw new RuntimeException(
                    sprintf(
                        'Was only able to read %d out of %d bytes.',
                        $i,
                        $bytes
                    )
                );
            }
            // @codeCoverageIgnoreEnd

            $read .= $char;
        }

        return $read;
    }

    /**
     * Attempts to read a character from a file.
     *
     * @param resource $handle The file handle.
     *
     * @return null|string The character.
     *
     * @throws RuntimeException If the file could not be read.
     *
     * @codeCoverageIgnore
     */
    private static function readChar($handle)
    {
        if (false === ($char = fgetc($handle))) {
            if (feof($handle)) {
                return null;
            }

            throw new RuntimeException(
                'A character could not be read from the file.'
            );
        }

        return $char;
    }

    /**
     * Attempts to read a file from the archive.
     *
     * @param resource $handle The file handle.
     * @param array    $file   The file information.
     *
     * @return string The file contents.
     *
     * @throws RuntimeException If the file could not be read.
     */
    private static function readFile($handle, array $file)
    {
        $contents = self::read($handle, $file['compressed_size']);

        if ($file['flags'] & self::BZ2) {
            // @codeCoverageIgnoreStart
            if (!is_string($contents = bzdecompress($contents))) {
                throw new RuntimeException(
                    sprintf(
                        'The archive file "%s" could not be decompressed (bzip2: %d).',
                        $file['path'],
                        $contents
                    )
                );
            }
            // @codeCoverageIgnoreEnd
        } elseif ($file['flags'] & self::GZ) {
            // @codeCoverageIgnoreStart
            if (false === ($contents = gzinflate($contents))) {
                throw new RuntimeException(
                    sprintf(
                        'The archive file "%s" could not be decompressed (zlib).',
                        $file['path']
                    )
                );
            }
            // @codeCoverageIgnoreEnd
        }

        // @codeCoverageIgnoreStart
        if (sprintf('%u', crc32($contents) & 0xffffffff) !== $file['crc32']) {
            throw new RuntimeException(
                sprintf(
                    'The contents of the file "%s" have been corrupted.',
                    $file['path']
                )
            );
        }
        // @codeCoverageIgnoreEnd

        return $contents;
    }

    /**
     * Reads and parses the manifest from the archive file.
     *
     * @param resource $handle The file handle.
     *
     * @return array The manifest.
     */
    private static function readManifest($handle)
    {
        $size = unpack('V', self::read($handle, 4));
        $size = $size[1];

        $raw = self::read($handle, $size);

        // ++ start skip: API version, global flags, alias, and metadata
        $count = unpack('V', substr($raw, 0, 4));
        $count = $count[1];

        $aliasSize = unpack('V', substr($raw, 10, 4));
        $aliasSize = $aliasSize[1];
        $raw = substr($raw, 14 + $aliasSize);

        $metaSize = unpack('V', substr($raw, 0, 4));
        $metaSize = $metaSize[1];

        $offset = 0;
        $start = 4 + $metaSize;
        // -- end skip

        $manifest = array(
            'files' => array(),
            'flags' => 0
        );

        for ($i = 0; $i < $count; $i++) {
            $length = unpack('V', substr($raw, $start, 4));
            $length = $length[1];
            $start += 4;

            $path = substr($raw, $start, $length);
            $start += $length;

            $file = unpack(
                'Vsize/Vtimestamp/Vcompressed_size/Vcrc32/Vflags/Vmetadata_length',
                substr($raw, $start, 24)
            );

            $file['path'] = $path;
            $file['crc32'] = sprintf('%u', $file['crc32'] & 0xffffffff);
            $file['offset'] = $offset;

            $offset += $file['compressed_size'];
            $start += 24 + $file['metadata_length'];

            $manifest['flags'] |= $file['flags'] & self::MASK;

            $manifest['files'][] = $file;
        }

        return $manifest;
    }

    /**
     * Recursively deletes the path.
     *
     * @param string $path The path to delete.
     *
     * @throws RuntimeException If the path could not be deleted.
     */
    private static function remove($path)
    {
        if (is_dir($path)) {
            foreach (scandir($path) as $item) {
                if (('.' === $item) || ('..' === $item)) {
                    continue;
                }

                self::remove($path . DIRECTORY_SEPARATOR . $item);
            }

            // @codeCoverageIgnoreStart
            if (!rmdir($path)) {
                throw new RuntimeException(
                    sprintf(
                        'The directory "%s" could not be deleted.',
                        $path
                    )
                );
            }
            // @codeCoverageIgnoreEnd
        } else {
            // @codeCoverageIgnoreStart
            if (!unlink($path)) {
                throw new RuntimeException(
                    sprintf(
                        'The file "%s" could not be deleted.',
                        $path
                    )
                );
            }
            // @codeCoverageIgnoreEnd
        }
    }

    /**
     * Attempts to seek a file.
     *
     * @param resource $handle   The file handle.
     * @param integer  $position The position to seek to.
     *
     * @throws RuntimeException The position could not be seek'd to.
     *
     * @codeCoverageIgnore
     */
    private static function seek($handle, $position)
    {
        if (-1 === fseek($handle, $position)) {
            throw new RuntimeException(
                sprintf(
                    'The file could not be seek\'d to position %d.',
                    $position
                )
            );
        }
    }
}
