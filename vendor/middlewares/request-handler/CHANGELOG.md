# Change Log

All notable changes to this project will be documented in this file.

The format is based on [Keep a Changelog](http://keepachangelog.com/)
and this project adheres to [Semantic Versioning](http://semver.org/).

## [2.0.1] - 2020-12-04
### Added
- Support for PHP 8.0

## [2.0.0] - 2019-11-29
### Removed
- Support for PHP 7.0 and 7.1

## [1.4.0] - 2018-10-26
### Added
- Support for arrays with 2 strings as request handler

### Fixed
- Use `phpstan` as a dev dependency to detect bugs

## [1.3.0] - 2018-08-24
### Added
- New option `continueOnEmpty()` to continue with the next middleware if the request attribute is empty or does not exists.

## [1.2.0] - 2018-08-04
### Added
- PSR-17 support

## [1.1.0] - 2018-02-07
### Added
- Support for any callable, not only `Closure`.

## [1.0.1] - 2018-01-25
### Fixed
- Fixed the suggested package in composer.json

## [1.0.0] - 2018-01-24
### Added
- Improved testing and added code coverage reporting
- Added tests for PHP 7.2
- Added support for `Closure` handlers

### Changed
- Upgraded to the final version of PSR-15 `psr/http-server-middleware`

### Fixed
- Updated license year

## [0.5.0] - 2017-12-16
### Added
- Support for handlers implementing `MiddlewareInterface`.
- Support for handlers implementing `RequestHandlerInterface`.

### Changed
- Changed the constructor signature: Instead a `Middlewares\Utils\CallableResolverInterface`, now only `Psr\Container\ContainerInterface` is accepted.

### Removed
- Removed `arguments()` option
- Removed support for `callable` handlers. They must implement `RequestHandlerInterface` or `MiddlewareInterface`.

## [0.4.0] - 2017-11-13
### Changed
- Replaced `http-interop/http-middleware` with  `http-interop/http-server-middleware`.

### Removed
- Removed support for PHP 5.x.

## [0.3.0] - 2017-09-21
### Changed
- Append `.dist` suffix to phpcs.xml and phpunit.xml files
- Changed the configuration of phpcs and php_cs
- Upgraded phpunit to the latest version and improved its config file
- Updated to `http-interop/http-middleware#0.5`

## [0.2.0] - 2017-04-20
### Changed
- Renamed `attribute()` to `handlerAttribute()`

### Fixed
- Improved docs and examples in README

## 0.1.0 - 2017-04-19
First version

[2.0.1]: https://github.com/middlewares/request-handler/compare/v2.0.0...v2.0.1
[2.0.0]: https://github.com/middlewares/request-handler/compare/v1.4.0...v2.0.0
[1.4.0]: https://github.com/middlewares/request-handler/compare/v1.3.0...v1.4.0
[1.3.0]: https://github.com/middlewares/request-handler/compare/v1.2.0...v1.3.0
[1.2.0]: https://github.com/middlewares/request-handler/compare/v1.1.0...v1.2.0
[1.1.0]: https://github.com/middlewares/request-handler/compare/v1.0.1...v1.1.0
[1.0.1]: https://github.com/middlewares/request-handler/compare/v1.0.0...v1.0.1
[1.0.0]: https://github.com/middlewares/request-handler/compare/v0.5.0...v1.0.0
[0.5.0]: https://github.com/middlewares/request-handler/compare/v0.4.0...v0.5.0
[0.4.0]: https://github.com/middlewares/request-handler/compare/v0.3.0...v0.4.0
[0.3.0]: https://github.com/middlewares/request-handler/compare/v0.2.0...v0.3.0
[0.2.0]: https://github.com/middlewares/request-handler/compare/v0.1.0...v0.2.0
