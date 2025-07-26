# Upgrading from 3.4 to 3.5

## Requirements

Kohana 3.5 supports PHP versions 7.1, 7.2, and 7.3. Compatibility with other PHP versions has not been fully tested, and
certain features may not function as expected.

## Changes

### Arr

- The `Arr::callback()` method now ensures that the second element of the returned array (`$params`) is always an array,
  even when no parameters are provided. This means you can safely remove any null checks for `$params` in your code.

### Cache

- The `Apc` driver has been removed. Use the `Apcu` driver or others instead.
- The `MemcacheTag` driver has been removed due to its dependency on the unmaintained `memcached-tags` PHP extension. If
  you were using this driver for tag-based caching, consider using the `Sqlite` driver.
- The `Wincache` driver was deprecated. Use the `Apcu` driver or others instead.

### Core

- The `Kohana::CODENAME` constant has been removed.

- The static property `Kohana::$magic_quotes` was deprecated.

### Encrypt

- The `Mcrypt` driver has been removed. Use the `OpenSSL` driver instead.

### Image

- The static property `Image::$default_driver` has been removed. To configure the default driver, refer to
  the [Image driver configuration](../../guide/image/#drivers).

### Request

- The `Request::accept_encoding()` method has been removed. Use `Request::headers()->accepts_encoding_at_quality()`
  instead.

- The `Request::accept_lang()` method has been removed. Use `Request::headers()->accepts_language_at_quality()` instead.

- The `Request::accept_type()` method has been removed. Use `Request::headers()->accepts_at_quality()` instead.
