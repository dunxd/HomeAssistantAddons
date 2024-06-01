PHP EPub Meta
=============

## Prerequisites for this fork

- PHP 8.x with DOM, Json, XML, XMLWriter and ZLib support (PHP 8.1 or later recommended)
- Release 2.x.x will only work with PHP >= 8.1 - typical for most source code & docker image installs in 2023 and later. *Note: updating .epub files for metadata or cover requires a 64-bit platform*
- Release 1.x.x still works with PHP 7.4 if necessary - earlier PHP 7.x (or 5.x) versions are *not* supported with this fork

This package is used by [mikespub/seblucas-cops](https://packagist.org/packages/mikespub/seblucas-cops) and [mikespub/epub-loader](https://packagist.org/packages/mikespub/epub-loader) with the same PHP version restrictions for 1.x and 2.x releases

## PHP EPub Meta (original)

This project aims to create a PHP class for reading and writing metadata
included in the EPub ebook format.

It also includes a very basic web interface to edit book metadata.

Please see the issue tracker for what's missing.

Forks and pull requests welcome.


About the EPub Manager Web Interface
------------------------------------

The manager expects your ebooks in a single flat directory (no subfolders). The
location of that directory has to be configured at the top of the index.php file.

All the epubs need to be read- and writable by the webserver.

The manager also makes some assumption on how the files should be named. The
format is: `<Author file-as>-<Title>.epub`. Commas will be replaced by `__` and
spaces are replaced by `_`.

Note that the manager will **RENAME** your files to that form when saving.

Using the "Lookup Book Data" link will open a dialog that searches the book at
Google Books you can use the found data using the "fill in" and "replace"
buttons. The former will only fill empty fields, while the latter will replace
all data. Author filling is missing currently.


Installing via Composer
=======================

You can use this package in your projects with [Composer](https://getcomposer.org/). Just
add these lines to your project's `composer.json`:

```
    "require": {
        "mikespub/php-epub-meta": "dev-main",
    }
```
