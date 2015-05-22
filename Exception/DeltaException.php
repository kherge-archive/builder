<?php

namespace Box\Component\Builder\Exception;

use RuntimeException;

/**
 * Throw for delta update related issues.
 *
 * @author Kevin Herrera <kevin@herrera.io>
 *
 * @codeCoverageIgnore
 */
class DeltaException extends RuntimeException
{
    /**
     * Creates a new exception for when a timestamp could not be retrieved.
     *
     * @param string $file The path to the file.
     *
     * @return DeltaException The new exception.
     */
    public static function cannotGetTimestamp($file)
    {
        return new self(
            "The modified timestamp for \"$file\" could not be retrieved."
        );
    }
}
