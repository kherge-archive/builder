[![Build Status][]](https://travis-ci.org/box-project/builder)
[![Latest Stable Version][]](https://packagist.org/packages/box-project/builder)
[![Latest Unstable Version][]](https://packagist.org/packages/box-project/builder)
[![Total Downloads][]](https://packagist.org/packages/box-project/builder)

Builder
=======

    composer require box-project/builder

Builder builds on the [`Phar` class][] provided by the [`phar` extension][] to
create customizable workflows for building PHP archives (`*.phar`). Builder also
provides features such as integration with the [`Processor` component][] as well
as delta updates to existing archives.

```php
use Box\Component\Builder\Builder;

$builder = new Builder('example.phar');
$builder->buildFromDirectory('/path/to/source');
```

Requirements
------------

- `phar` extension
- `kherge/file` ~1.3

### Suggested

- `box-project/processor` ~0.1
- `symfony/event-dispatcher` ~2.5

Getting Started
---------------

Before we begin, it is recommended that you become very familiar with Symfony's
[EventDispatcher component][]. The heart of the **Builder** component relies on
its availability to perform all of the customized functionality that is built on
the `Phar` class. The main points of interest in the documentation are on how to
create and register event listeners and subscribers.

```php
use Box\Component\Builder\Builder;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
```

To start, you will first need to create an instance of `Builder`. The `Builder`
class is an extension of the existing `Phar` class but with methods overloaded
so that various processes can be customized.

```php
$builder = new Builder('example.phar');
```

> You will be able to use the existing `Phar` class documentation on the PHP
> website when using the `Builder` class. Only the class methods that have been
> modified will be documented here.

On its own, the `Builder` instance behaves identically to that of those from
the `Phar` class. To leverage the strength of the changes made in `Builder`,
you will need to register an instance of `EventDispatcherInterface` with the
`Builder` instance.

```php
use Symfony\Component\EventDispatcher\EventDispatcher;

$dispatcher = new EventDispatcher();

$builder->setEventDispatcher($dispatcher);
```

The event dispatcher will be used when one of the following methods are called:

- `addEmptyDir()`
- `addFile()`
- `addFromString()`
- `buildFromDirectory()`
- `buildFromIterator()`
- `setStub()`

Builder Methods
---------------

```php
use Box\Component\Builder\Events;
```

All events that can be observed are listed in the `Events` class. There is a
pattern with all of the events that are dispatched for each of the methods that
are listed below. Each `Events::PRE_*` event that is dispatched will allow an
observer to abort the operation.

If `addFile()` is called, for example, a registered listener can abort the
addition of the file by calling the `->skip()` method on the event object
that is passed to each listener. This will prevent all remaining listeners
from being dispatched, and it will cause the `addFile()` method to abort the
addition of the file.

Each `Events::PRE_*` listener will receive an instance of the events respective
`Box\Component\Builder\Event\Pre*Event` class. Each instance allows a listener
to alter the arguments that are ultimately passed to the `Phar::*()` method that
is being overloaded.

### `addEmptyDir()`

    addEmptyDir($local = null)

This method will create a new empty directory in the archive. The directory
will be located at the path specified by `$local`.

#### Event Dispatcher

| Event                        | Event Class                                        |
|:-----------------------------|:---------------------------------------------------|
| `Events::PRE_ADD_EMPTY_DIR`  | `Box\Component\Builder\Event\PreAddEmptyDirEvent`  |
| `Events::POST_ADD_EMPTY_DIR` | `Box\Component\Builder\Event\PostAddEmptyDirEvent` |

### `addFile()`

    addFile($path, $local = null)

This method will add the contents of a file on the file system, specified by
`$path`, to the archive. By default, the full path to the file is stored in
the archive. You may specify your own path in the archive by using the `$local`
parameter.

It is important to note that this particular method never calls the
`Phar::addFile()` method. The contents of the file are read and the arguments,
along with the file contents, are passed to `Builder::addFromString()`. This
allows for the file contents to be modified before it is added to the archive.

#### Event Dispatcher

| Event                   | Event Class                                    |
|:------------------------|:-----------------------------------------------|
| `Events::PRE_ADD_FILE`  | `Box\Component\Builder\Event\PreAddFileEvent`  |
| `Events::POST_ADD_FILE` | `Box\Component\Builder\Event\PostAddFileEvent` |

### `addFromString()`

    addFromString($local, $contents = null);

Adds a file to the archive using the given contents at the specified path in
the archive.

#### Event Dispatcher

| Event                          | Event Class                                          |
|:-------------------------------|:-----------------------------------------------------|
| `Events::PRE_ADD_FROM_STRING`  | `Box\Component\Builder\Event\PreAddFromStringEvent`  |
| `Events::POST_ADD_FROM_STRING` | `Box\Component\Builder\Event\PostAddFromStringEvent` |

### `buildFromDirectory()`

    buildFromDirectory($path, $filter = null)

Recursively adds the files and directories from the specified directory. If a
`$filter` is given, all path names that do not match the regular expression will
not be added to the archive.

It is important to note that this particular method never calls the 
`Phar::buildFromDirectory()` method. An iterator that emulates the expected
behavior is passed to `Builder::buildFromIterator()`, which will allow the
names and contents of each file and directory to be modified before they are
added to the archive.

#### Event Dispatcher

| Event                               | Event Class                                               |
|:------------------------------------|:----------------------------------------------------------|
| `Events::PRE_BUILD_FROM_DIRECTORY`  | `Box\Component\Builder\Event\PreBuildFromDirectoryEvent`  |
| `Events::POST_BUILD_FROM_DIRECTORY` | `Box\Component\Builder\Event\PostBuildFromDirectoryEvent` |

### `buildFromIterator()`

    buildFromIterator(Iterator $iterator, $base = null)

Adds the files returned by the iterator to the archive. If a `$base` directory
path is specified, all paths that begin with `$base` will have that portion of
the path removed. It is important to note that trailing slashes matter.

There are a few of key/value combinations that are supported:

| Key             | Value                  |
|:----------------|:-----------------------|
| `/path/to/file` | `/path/to/file`        |
| `/path/to/file` | file stream resource   |
| `/path/to/file` | `SplFileInfo` instance |
| `to/file`       | `/path/to/file`        |
| `to/file`       | file stream resource   |
| `to/file`       | `SplFileInfo` instance |

#### Event Dispatcher

| Event                              | Event Class                                              |
|:-----------------------------------|:---------------------------------------------------------|
| `Events::PRE_BUILD_FROM_ITERATOR`  | `Box\Component\Builder\Event\PreBuildFromIteratorEvent`  |
| `Events::POST_BUILD_FROM_ITERATOR` | `Box\Component\Builder\Event\PostBuildFromIteratorEvent` |

### `compressFiles()`

    compressFiles($algorithm)

This method will compress the files in the archive using the specified algorithm
(e.g. `Builder::BZ2`, `Builder::GZ`). If the files in the archive could not be
compressed, an exception is thrown with a little more information that what is
normally provided by `Phar`.

> There is a known bug with `Phar` that prevents archives with a large number
> of files from being compressed. The exception that is thrown will attempt to
> get the build working by giving you information on how to workaround this bug.

### `setEventDispatcher()`

    setEventDispatcher(EventDispatcherInterface $dispatcher = null)

Sets or unsets the event dispatcher.

### `resolvePath()`

    resolvePath($local)

This method will return the `PharFileInfo` object for a file or directory that
is located somewhere in the archive. Unlike the array access that is provided
by the `Phar` class, this works with sub directories as well.

### `setStub()`

    setStub($stub)

Sets the code that will be run by PHP when the archive file is executed.

#### Event Dispatcher

| Event                   | Event Class                                    |
|:------------------------|:-----------------------------------------------|
| `Events::PRE_SET_STUB`  | `Box\Component\Builder\Event\PreSetStubEvent`  |
| `Events::POST_SET_STUB` | `Box\Component\Builder\Event\PostSetStubEvent` |

License
-------

This software is released under the MIT license.

[Build Status]: https://travis-ci.org/box-project/builder.png?branch=master
[Latest Stable Version]: https://poser.pugx.org/box-project/builder/v/stable.png
[Latest Unstable Version]: https://poser.pugx.org/box-project/builder/v/unstable.png
[Total Downloads]: https://poser.pugx.org/box-project/builder/downloads.png

[EventDispatcher component]: http://symfony.com/doc/current/components/event_dispatcher/index.html
[`Phar` class]: http://php.net/manual/en/class.phar.php
[`phar` extension]: http://php.net/manual/en/book.phar.php
[`Processor` component]: https://github.com/box-project/processor
