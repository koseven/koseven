# Changelog 4.0
## General
 * Bug Fixes and Performance / Security Improvments.
 * New pre-defined constant `PUBPATH` which points to the Koseven `public` directory
 * Error handlers strict compliance with PHP7+ (Replaced `Exception` with `Throwable`)
 * New `KO7_Error_Exception` class which extends PHP internal `ErrorException`
 * Translation, Inflector updates.
## Core
 * Class/Framework rename to `KO7`. Introducing the compatibility module which ensures all classes will still work.
## Configuration
 * Added support for multiple configuration files (`php`, `json`, `yaml`)
## Encryption
 * Deprecated Mcrypt Class (deprecated since PHP 7.1 - removed in PHP 7.2).
 * Add Support for Libsodium.
 * OpenSSL is now default engine.
 * `var_dump`ing an Engine won't display the Encryption Key anymore.
## ORM
 * `ORM->changed()` had unexpected behavior (returns value). Added function `ORM->has_changed()` which returns bool.
 * Added Support for non auto-increment Primary Keys
### UUID
 * Added `UUID` class fot generating RFC 4122 v3, v4, v5 uuids
 * Imporved Performanced by possibility to turn of uuid database checks
## Database
 * Added Support for `stdClass` attributes
 * Added JSON field type to `MySQLi` Driver
## Cache
 * Removed `memcache` and `apc` driver since both are removed wit PHP 7.0 (use `memcached` and `apcu` instead).
 * Also removed `MemcacheTag` Class as it depended on `memcache`
## Unittests
 * Removed `phpunit/dbunit` as it is no longer maintained and not compatible with phpunit 8
 * Updated package `phpunit` to version 8 (released Feb. 2019)
 * Added Enviroment variable `TRAVIS_TEST` which can be used to overwrite configurations for automated tests.
 * Added the following services which can be used for unittesting: redis, memcached, mysql
 * Added the following PHP-Extensions which can be used for unittesting: memcached, redis, imagick, apcu
 * And of course: Added more Unittests to improve Framework Code Coverage
## Userguide
 * Added Support for namespaced classes
## I18n
 * Using `I18n::get()` in `SYSPATH` and `MODPATH`. Only using `__()` inside `APPATH`.

