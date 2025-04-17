PHP EPub Meta
=============

## Prerequisites for this fork

- PHP 8.x with DOM, Json, XML, Zip and ZLib support (PHP 8.2 or later recommended)
- Release 3.x.x will only work with PHP >= 8.2 - typical for most source code & docker image installs in 2024 and later. *Note: updating .epub files for metadata or cover requires a 64-bit platform*
- Release 2.x.x will only work with PHP >= 8.1 - typical for most source code & docker image installs in 2023 and later. *Note: updating .epub files for metadata or cover requires a 64-bit platform*
- Release 1.x.x still works with PHP 7.4 if necessary - earlier PHP 7.x (or 5.x) versions are *not* supported with this fork

This package is used by [mikespub/seblucas-cops](https://packagist.org/packages/mikespub/seblucas-cops) and [mikespub/epub-loader](https://packagist.org/packages/mikespub/epub-loader) with the same PHP version restrictions for 1.x, 2.x and 3.x releases

## Installation

You can use this package in your projects with [Composer](https://getcomposer.org/).

```sh
$ composer require mikespub/php-epub-meta
```

## Using PHP EPub Meta

This package provides the `SebLucas\EPubMeta\EPub` class to read and write metadata
for your EPub files.

```php
use SebLucas\EPubMeta\EPub;

$file = 'test/data/test.epub';
$epub = new EPub($file);

$title = $epub->getTitle();
// ...
```

The web interface is **disabled** by default when mikespub/php-epub-meta is included
as vendor package. You can integrate it in your own application by having a look at
the minimal code in [app/index.php](https://github.com/mikespub-org/php-epub-meta/blob/main/app/index.php)

## PHP EPub Meta (original)

This project aims to create a PHP class for reading and writing metadata
included in the EPub ebook format.

It also includes a very basic web interface to edit book metadata.

Please see the issue tracker for what's missing.

Forks and pull requests welcome.


About the EPub Metadata Web Interface
-------------------------------------

The web app expects your ebooks in a single flat directory or in subfolders. The
location of that directory has to be configured at the top of the `app/index.php`
file.

All the epubs need to be read- and writable by the webserver.

The web app also makes some assumption on how the files should be named. The
format is: `<Author file-as>-<Title>.epub`. Commas will be replaced by `__` and
spaces are replaced by `_`.

Note that the web app can **RENAME** your files to that form when saving too.

Using the "Lookup Book Data" link will open a dialog that searches the book at
Google Books you can use the found data using the "fill in" and "replace"
buttons. The former will only fill empty fields, while the latter will replace
all data. Author filling is missing currently.
