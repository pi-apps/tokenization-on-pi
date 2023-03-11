# Change Log

All notable changes to this project will be documented in this file.

The format is based on [Keep a Changelog](http://keepachangelog.com/)
and this project adheres to [Semantic Versioning](http://semver.org/).

## [2.0.1] - 2020-12-02
### Added
- Support for PHP 8

## [2.0.0] - 2019-12-03
### Added
- A second argument in the constructor to pass a `ResponseFactoryInterface`

### Removed
- Support for PHP 7.0 and 7.1

## [1.0.0] - 2018-09-23
### Added
- [#3] `continueOnError()` option

### Removed
- [#3] `defaultHandler()` option

## [0.2.1] - 2018-08-22
### Changed
- Empty paths are always returned as `/`.
- A `/` prefix works properly as well.

## [0.2.0] - 2018-08-22
### Changed
- Update `middlewares/utils` library for compatibility with `middlewares/request-handler`.

## 0.1.0 - 2018-08-22
First version

[#3]: https://github.com/middlewares/base-path-router/issues/3

[2.0.1]: https://github.com/middlewares/base-path-router/compare/v2.0.0...v2.0.1
[2.0.0]: https://github.com/middlewares/base-path-router/compare/v1.0.0...v2.0.0
[1.0.0]: https://github.com/middlewares/base-path-router/compare/v0.2.1...v1.0.0
[0.2.1]: https://github.com/middlewares/base-path-router/compare/v0.2.0...v0.2.1
[0.2.0]: https://github.com/middlewares/base-path-router/compare/v0.1.0...v0.2.0
