<?php

namespace Box\Component\Builder;

/**
 * Manages the names of the builder events.
 *
 * @author Kevin Herrera <kevin@herrera.io>
 */
final class Events
{
    /**
     * The event after an empty directory has been added.
     *
     * @var string
     */
    const POST_ADD_EMPTY_DIR = 'box.builder.add_empty_dir.after';

    /**
     * The event after a file has been added.
     *
     * @var string
     */
    const POST_ADD_FILE = 'box.builder.add_file.after';

    /**
     * The event after a file has been added from a string.
     *
     * @var string
     */
    const POST_ADD_FROM_STRING = 'box.builder.add_from_string.after';

    /**
     * The event after the archive is built from a directory.
     *
     * @var string
     */
    const POST_BUILD_FROM_DIRECTORY = 'box.builder.build_from_directory.after';

    /**
     * The event after the archive is built from an iterator.
     *
     * @var string
     */
    const POST_BUILD_FROM_ITERATOR = 'box.builder.build_from_iterator.after';

    /**
     * The event after the stub is set.
     *
     * @var string
     */
    const POST_SET_STUB = 'box.builder.set_stub.after';

    /**
     * The event before an empty directory is added.
     *
     * @var string
     */
    const PRE_ADD_EMPTY_DIR = 'box.builder.add_empty_dir.before';

    /**
     * The event before a file is added.
     *
     * @var string
     */
    const PRE_ADD_FILE = 'box.builder.add_file.before';

    /**
     * The event before a file is added from a string.
     *
     * @var string
     */
    const PRE_ADD_FROM_STRING = 'box.builder.add_from_string.before';

    /**
     * The event before the archive is built from a directory.
     *
     * @var string
     */
    const PRE_BUILD_FROM_DIRECTORY = 'box.builder.build_from_directory.before';

    /**
     * The event before the archive is built from an iterator.
     *
     * @var string
     */
    const PRE_BUILD_FROM_ITERATOR = 'box.builder.build_from_iterator.before';

    /**
     * The event before the stub is set.
     *
     * @var string
     */
    const PRE_SET_STUB = 'box.builder.set_stub.before';
}
