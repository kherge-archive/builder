[![Build Status][]](https://travis-ci.org/box-project/builder)
[![Latest Stable Version][]](https://packagist.org/packages/box-project/builder)
[![Latest Unstable Version][]](https://packagist.org/packages/box-project/builder)
[![Total Downloads][]](https://packagist.org/packages/box-project/builder)

Builder
=======

    composer require box-project/builder

Builder builds on the [`Phar` class][] provided by the [`phar` extension][] to
create customizable workflow for building PHP archives (`*.phar`). Builder also
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

### `addEmptyDir()`

    addEmptyDir($local = null)

### `addFile()`

    addFile($path, $local = null)

### `addFromString()`

    addFromString($local, $contents = null);

### `buildFromDirectory()`

    buildFromDirectory($path, $filter = null)

### `buildFromIterator()`

    buildFromIterator(Iterator $iterator, $base = null)

### `compressFiles()`

    compressFiles($algorithm)

### `setEventDispatcher()`

    setEventDispatcher(EventDispatcherInterface $dispatcher = null)

### `resolvePath()`

    resolvePath($local)

### `setStub()`

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
