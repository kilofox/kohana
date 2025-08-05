# Upgrading from 3.3 to 3.4

## Requirements

Kohana 3.4 supports PHP versions 5.6, 7.0, and 7.1. Compatibility with other PHP versions has not been fully tested, and
certain features may not function as expected.

## Changes

- The global EXT constant has been deprecated. Explicitly specify `.php` or another file extension instead.

### Auth

- The `Auth::hash_password()` method has been removed. Use `Auth::hash()` instead.

### Cache

- Added a new `Memcached` driver.
- The `APC` driver was deprecated. Use `APCu` or other drivers instead.
- The `Memcache` driver was deprecated. Use `Memcached` or other drivers instead.
- The `MemcacheTag` driver was deprecated.

### Core

- The `Kohana::CODENAME` constant was deprecated.

### Database

- The `MySQL` driver has been removed. Use `PDO` or other drivers instead.

### Encrypt

- Now `Encrypt` acts as an interface and a new `OpenSSL` driver for it was added.
- The `Mcrypt` driver was deprecated. Use `OpenSSL` instead.

### Security

- The `Security::strip_image_tags()` method has been removed
  for [security reasons](https://github.com/kohana/kohana/issues/107) as it is not reliable to parse and sanitize HTML
  with regular expressions. You should either encode HTML tags entirely, e.g. with `HTML::chars()`, or use a more robust
  HTML filtering solution such as [HTML Purifier](http://htmlpurifier.org).

### Validation

- The `Validation::as_array()` method has been removed. Use `Validation::data()` instead.
