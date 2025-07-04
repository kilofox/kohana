## [3.4.3](https://github.com/kilofox/kohana/compare/v3.4.2...v3.4.3) (2025-06-16)


### Bug Fixes

* **public:** add lang attribute to specify English document language ([d2fea1c](https://github.com/kilofox/kohana/commit/d2fea1c4fb6dbcd51610f65450fe7927b037fcb7))
* **Core:** correct parentheses for proper assignment ([e9e56a2](https://github.com/kilofox/kohana/commit/e9e56a29bc58f5dd741bb603cb9531b79a0b2f8f))
* **Core:** optimize Debug output by removing nested pre and code tags ([3b3f13a](https://github.com/kilofox/kohana/commit/3b3f13afc71d2874eba153c640c38e1a3399d59f))
* **Unittest:** fix wrong return types ([d4515f9](https://github.com/kilofox/kohana/commit/d4515f91199ce6a92436f50d061e724fd11dbc62))
* **Userguide:** add alt text to Kohana logo for accessibility ([9e7d3d8](https://github.com/kilofox/kohana/commit/9e7d3d841d0939000f5e8eabaae11ff5b0c50ab9))
* **Userguide:** improve accessibility by linking label to input field ([00bfc9f](https://github.com/kilofox/kohana/commit/00bfc9f7a996f10a27f58dbf9ed2f43ca7a5c3c2))
* **Userguide:** remove obsolete IE9 polyfill script ([e3a539a](https://github.com/kilofox/kohana/commit/e3a539af31700a36cfcdc46094c5cc0857841d9e))


### Performance Improvements

* **Core:** use foreach instead of the "each()" function for better performance ([af20ac8](https://github.com/kilofox/kohana/commit/af20ac86f4dd7e675e698f462fb51a6dd9c14670))



## [3.4.2](https://github.com/kilofox/kohana/compare/v3.4.1...v3.4.2) (2023-08-13)


### Bug Fixes

* **Database:** add order direction check ([7b3e91f](https://github.com/kilofox/kohana/commit/7b3e91f4f82a55fbe80628a62bc9d25d55df404e))



## [3.4.1](https://github.com/kilofox/kohana/compare/v3.4.0...v3.4.1) (2022-11-26)


### Bug Fixes

* **public:** fix "No input file specified" error ([369bb8c](https://github.com/kilofox/kohana/commit/369bb8c353334594e30498b4ae26b8f4c20ae6cd))


### Features

* **public:** simplify the definition of directories ([1b0465e](https://github.com/kilofox/kohana/commit/1b0465e573bca4cebf1408b576b3884d8bec0ea4))



# [3.4.0](https://github.com/kilofox/kohana/compare/v3.3.6...v3.4.0) (2018-12-25)


### Bug Fixes

* fix incompatible exception handling with PHP 7 ([24cbefa](https://github.com/kilofox/kohana/commit/24cbefac1e1c1526cc25f28105cdecf998f7b909))
* **Userguide:** change the data type of Kohana_Kodoc_Markdown::$_toc to array ([58f7aef](https://github.com/kilofox/kohana/commit/58f7aefed9e624b409540d07f7a23e9ee8487f47))


### Features

* **Auth:** remove hash_password() method ([2c974aa](https://github.com/kilofox/kohana/commit/2c974aa00b4b1d00e2dc6d12611bbd7162f113c2))
* **Cache:** add Memcached driver ([b7287ed](https://github.com/kilofox/kohana/commit/b7287ed5946a46efe6c357100b599a47e3cf5d33))
* **Cache:** deprecate the APC and Memcache drivers ([55c0690](https://github.com/kilofox/kohana/commit/55c06901e97780aead55659a3df26496b94e8220))
* **Cache:** deprecate the MemcacheTag driver ([4cfb5f5](https://github.com/kilofox/kohana/commit/4cfb5f5eda41c438e4b9c56d4e5327e6f1867035))
* **Core:** deprecate Kohana::CODENAME constant ([8e07900](https://github.com/kilofox/kohana/commit/8e07900588c58f619d669f153c3a47f5df826a01))
* **Database:** remove MySQL driver ([428ec22](https://github.com/kilofox/kohana/commit/428ec223bd913a32713d748312a5551213e3b560))
* **Encrypt:** deprecate the Mcrypt driver ([a5f5456](https://github.com/kilofox/kohana/commit/a5f54566730823585f9604f3c6bc48acef0052dc))
* **Encrypt:** separate Mcrypt from Encrypt as a driver and implement OpenSSL driver ([bee97eb](https://github.com/kilofox/kohana/commit/bee97ebb162f58c048c01895f1b17d2623ff057e))
* isolate the visible part of an application inside a public directory ([9b794b9](https://github.com/kilofox/kohana/commit/9b794b9ecbaff4683cdb35264178ea6f6d9bbbb5))
* **Security:** remove strip_image_tags() method ([9846cde](https://github.com/kilofox/kohana/commit/9846cde1322b94fb7d6addf7eb0afd7c45b682c0))
* **Validation:** remove as_array() method ([92964d3](https://github.com/kilofox/kohana/commit/92964d38dc5a62f27dcb203b564cdba99fe5baa9))



## [3.3.6](https://github.com/kilofox/kohana/compare/b045d16354375d7b7472734439aefc9ae05e4eb7...v3.3.6) (2018-03-31)


### Reverts

* Revert "Remove auth module" ([0d7cb4c](https://github.com/kilofox/kohana/commit/0d7cb4cdab10552d0c191e2374105e73c28f7ab9))
* Revert "try php 5.4 again for travis" ([6659f64](https://github.com/kilofox/kohana/commit/6659f643dbee8a2eef9e3424803efec6d65e7d62))
* Revert "Updated system tracking to latest kohana/core3.2/develop" ([b045d16](https://github.com/kilofox/kohana/commit/b045d16354375d7b7472734439aefc9ae05e4eb7))



