[![Build Status][]](https://travis-ci.org/box-project/builder)
[![Latest Stable Version][]](https://packagist.org/packages/box-project/builder)
[![Latest Unstable Version][]](https://packagist.org/packages/box-project/builder)
[![Total Downloads][]](https://packagist.org/packages/box-project/builder)

Builder
=======

    composer require box-project/builder

Builder leverages the [`phar` extension][] to provide a customizable workflow
for creating new and managing existing PHP archives (`.phar`). The library
provides an extension to the existing [`Phar` class][] and integrates an event
dispatcher that allows for more advanced features such as file content
processing and incremental updates of existing archives.

```php
use Box\Component\Builder\Builder;
use Box\Component\Builder\Event\Listener\ProcessorSubscriber;
use Box\Component\Processor\Processor\PHP\CompactProcessor;
use Symfony\Component\EventDispatcher\EventDispatcher;

// compact php scripts as they are added
$dispatcher = new EventDispatcher();
$dispatcher->addSubscriber(
    new ProcessorSubscriber(
        new CompactProcessor()
    )
);

// create a new archive using a directory
$builder = new Builder('example.phar');
$builder->setEventDispatcher($dispatcher);
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

Before we get started, you need to be familiar with creating and registering
event listeners and subscribers using Symfony's [EventDispatcher component][].
The EventDispatcher component is responsible for providing the customizable
workflows found in **Builder**.

```php
use Box\Component\Builder\Builder;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
```

To create a new archive (or modify an existing one), you will first need to
create an instance of `Builder`. The `Builder` class is an extension of the
existing `Phar` class with some of the methods overloaded so that custom
workflows can be integrated.

```php
$builder = new Builder('example.phar');
```

> You will be able to use the existing `Phar` class documentation on the PHP
> website when using the `Builder` class. Only the class methods that have
> been modified will be documented here.

On its own, the `Builder` instance behaves identically to that of an instance
of the `Phar` class. To leverage the strength of the changes made in `Builder`,
you will need to register an instance of `EventDispatcherInterface` with your
`Builder` instance.

```php
use Symfony\Component\EventDispatcher\EventDispatcher;

$dispatcher = new EventDispatcher();

$builder->setEventDispatcher($dispatcher);
```

The event dispatcher will be used when one of the following methods is called:

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

The following diagram models the workflow for archive building methods that
are affected by the event dispatcher. The specific methods that are affected
are documented below.

<center>
  <img alt="Event Dispatching" src="https://raw.githubusercontent.com/box-project/builder/master/Resources/images/dispatching.png"/>
</center>

> A complete list of events can be found in the `Events` class.

Each affected method has two events.

- A `Events::PRE_*` event that is dispatched before the method does its work.
- A `Events::POST_*` event that is dispatched after the method is done working.

The `Events::PRE_*` event allows listeners to change the values of the arguments
that were passed to the method. It also allows listeners to abort altogether,
preventing the method for performing any of its work. If an abort is triggered
by calling the event object's `skip()` method, the remaining listeners will not
be dispatched and the method will abort.

For example, if a listener for `addFile()` calls the event object's `skip()`
method, the `addFile()` method will not add the file. Alternatively, the
listener can change the file that will be added, or the location of where
it will be added in the archive. 

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

This method will add the contents of the file, `$path`, from the file system to
the archive. By default, the full path to the file is stored in the archive. You
may specify your own path in the archive by using the `$local` parameter.

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

Adds the given `$contents` to the `$local` file in the archive.

#### Event Dispatcher

| Event                          | Event Class                                          |
|:-------------------------------|:-----------------------------------------------------|
| `Events::PRE_ADD_FROM_STRING`  | `Box\Component\Builder\Event\PreAddFromStringEvent`  |
| `Events::POST_ADD_FROM_STRING` | `Box\Component\Builder\Event\PostAddFromStringEvent` |

### `buildFromDirectory()`

    buildFromDirectory($path, $filter = null)

Recursively adds the contents of a directory (`$path`) to the archive. If the
regular expression `$filter` is provided, it will be used to whitelist the paths
that are found in the directory. If a path does not match the regular expression
it will not be added to the archive.

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
compressed, an exception will be thrown containing additional information about
the failure.

> There is a known bug with the `phar` extension that prevents archives with a
> large number of files from being compressed. The exception that is thrown will
> attempt to get the build working by giving you additional information on how
> to workaround this bug.

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

Stub Generator
--------------

**Builder** provides a class that will manage the process of generating a stub
for your archive. It attempts to eliminate the need of writing stub source code
when additional functionality could simply be added by making a method call.

```php
use Box\Component\Builder\Stub;

$stub = new Stub();
```

### Methods

#### `interceptFileFuncs()`

    interceptFileFuncs($intercept = true) : Stub

Toggles the call to `Phar::interceptFileFuncs()`.

#### `loadPhar()`

    loadPhar($path, $alias = null)

Adds a call to `Phar::loadPhar()`.

#### `mapPhar()`

    mapPhar($alias) : Stub

Calls `Phar::mapPhar()` with the stream alias for the archive.

#### `mount()`

    mount($file, $local) : Stub

Adds a call to `Phar::mount()`.

#### `mungServer()`

Calls `Phar::mungServer()` with the list of `$_SERVER` variables.

#### `selfExtract()`

    selfExtract($enable = true, $force = false) : Stub

Enables support for self-extraction if the `phar` extension is not available.
If `$force` is set to `true`, self-extraction will be forced even if the `phar`
extension is available.

> It may be important to note that self-extraction only occurs once in order
> for the extracted files to serve as a cache. The cache will expire if the
> archive has been changed.

#### `setBanner()`

    setBanner($comment) : Stub

Sets the banner comment that is shown at the beginning of the stub. If `$null`
is given as the comment, the banner comment will be removed from the stub.

##### Default

```php
/**
 * Generated by Box
 *
 * @link http://box-project.org/
 */
```

#### `setCode()`

    setCode($code) : Stub

Sets the code that must be executed inside the stub.

> It is important to note that this code is executed after any self-extraction
> that is needed has taken place, and all of the calls to `Phar::*()` have been
> made.

#### `setShebang()`

    setShebang($line) : Stub

Sets the "shebang" line. If `null`, the line will be removed.

##### Default

    #!/usr/bin/env php

#### `webPhar()`

    webPhar(
        $alias = null,
        $index = 'index.php',
        $notFound = null,
        array $mime = null,
        $rewrite = null
    ): Stub

Calls `Phar::webPhar()`.

Included Event Listeners
------------------------

**Builder** component bundles some event listeners to simplify certain common
tasks. You may use these listeners as they are, or extend them to customize
their functionality to better suit your needs.

### DeltaUpdateSubscriber

```php
use Box\Component\Builder\Event\Listener\DeltaUpdateSubscriber;

$dispatcher->addSubscriber(new DeltaUpdateSubscriber());
```

The `DeltaUpdateSubscriber` intercepts files and directories that are added to
the archive. It performs a check to see if the file or directory already exists
in the archive and if it is newer than the one already in the archive. If the
subscriber detects that the file or directory is as old or older than the one
in the archive, the `skip()` event method is called.

### ProcessorSubscriber

```php
use Box\Component\Builder\Event\Listener\ProcessorSubscriber;
use Box\Component\Processor\Processor\PHP\CompactProcessor;

$dispatcher->addSubscriber(
    new ProcessorSubscriber(
        new CompactProcessor()
    )
);
```

The `ProcessorSubscriber` intercepts files that are added to the archive. If
a supported file is intercepted, the contents will be modified before they are
added to the archive.

### RegexBlacklistSubscriber

```php
use Box\Component\Builder\Event\Listener\RegexBlacklistSubscriber;

$dispatcher->addSubscriber(
    new RegexBlacklistSubscriber(
        
        // file blacklist
        array(
            '/\.exe$/'
        ),
    
        // directory blacklist
        array(
            '/\/[Tt]ests\//'
        )
    )
);
```

The `RegexBlacklistSubscriber` intercepts files and directories before they
are added to the archive. If the file or directory path matches the regular
expression blacklist, the `skip()` event method is called preventing the file
or directory from being added to the archive.

### RegexWhitelistSubscriber

```php
use Box\Component\Builder\Event\Listener\RegexWhitelistSubscriber;

$dispatcher->addSubscriber(
    new RegexWhitelistSubscriber(
        
        // file blacklist
        array(
            '/\.php/'
        ),
    
        // directory blacklist
        array(
            '/\/src\//'
        )
    )
);
```

The `RegexWhitelistSubscriber` intercepts files and directories before they
are added to the archive. If the file or directory path does not match the
regular expression whitelist, the `skip()` event method is called preventing
the file or directory from being added to the archive.

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
