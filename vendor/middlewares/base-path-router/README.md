# middlewares/base-path-router

[![Latest Version on Packagist][ico-version]][link-packagist]
[![Software License][ico-license]](LICENSE)
![Testing][ico-ga]
[![Total Downloads][ico-downloads]][link-downloads]

A middleware dispatching to other middleware stacks, based on different path prefixes.

## Requirements

* PHP >= 7.2
* A [PSR-7 http library](https://github.com/middlewares/awesome-psr15-middlewares#psr-7-implementations)
* A [PSR-15 middleware dispatcher](https://github.com/middlewares/awesome-psr15-middlewares#dispatcher)

## Installation

This package is installable and autoloadable via Composer as [middlewares/base-path-router](https://packagist.org/packages/middlewares/base-path-router).

```sh
composer require middlewares/base-path-router
```

You may also want to install [middlewares/request-handler](https://packagist.org/packages/middlewares/request-handler).

## Example

This example uses [middleware/request-handler](https://github.com/middlewares/request-handler) to execute the route handler:

```php
$dispatcher = new Dispatcher([
    new Middlewares\BasePathRouter([
        '/admin' => $admin,
        '/admin/login' => $adminLogin,
        '/blog' => $blog,
    ]),
    new Middlewares\RequestHandler()
]);

$response = $dispatcher->dispatch(new ServerRequest());
```

**BasePathRouter** allows anything to be defined as the router handler (a closure, callback, action object, controller class, etc). The middleware will store this handler in a request attribute.

## Usage

You have to set an array of paths (as keys) and handlers (as values).

```php
$router = new Middlewares\BasePathRouter([
    '/foo' => $routerFoo,
    '/bar' => $routerBar,
    '/foo/bar' => $routerFooBar,
]);
```

Optionally, you can provide a `Psr\Http\Message\ResponseFactoryInterface` as the second argument, to create the error responses (`404`) if the router is not found. If it's not defined, [Middleware\Utils\Factory](https://github.com/middlewares/utils#factory) will be used to detect it automatically.

```php
$responseFactory = new MyOwnResponseFactory();

$router = new Middlewares\BasePathRouter($paths, $responseFactory);
```

### continueOnError

Set `true` to continue to the next middleware instead return an empty 404 response for non-matching requests (i.e. those that do not have an URI path start with one of the provided prefixes).

### stripPrefix

By default, subsequent middleware will receive a slightly manipulated request object: any matching path prefixes will be stripped from the URI.
This helps when you have a hierarchical setup of routers, where subsequent routers (e.g. one for the API stack mounted under the `/api` endpoint) can ignore the common prefix.

If you want to disable this behavior, use the `stripPrefix` method:

```php
$router = (new Middlewares\BasePathRouter([
        '/prefix1' => $middleware1,
    ]))->stripPrefix(false);
```

### attribute

The attribute name used to store the handler in the server request. The default attribute name is `request-handler`.

```php
$dispatcher = new Dispatcher([
    //Save the route handler in an attribute called "route"
    (new Middlewares\BasePathRouter($paths))->attribute('route'),

    //Execute the route handler
    (new Middlewares\RequestHandler())->attribute('route')
]);
```

---

Please see [CHANGELOG](CHANGELOG.md) for more information about recent changes and [CONTRIBUTING](CONTRIBUTING.md) for contributing details.

The MIT License (MIT). Please see [LICENSE](LICENSE) for more information.

[ico-version]: https://img.shields.io/packagist/v/middlewares/base-path-router.svg?style=flat-square
[ico-license]: https://img.shields.io/badge/license-MIT-brightgreen.svg?style=flat-square
[ico-ga]: https://github.com/middlewares/base-path-router/workflows/testing/badge.svg
[ico-downloads]: https://img.shields.io/packagist/dt/middlewares/base-path-router.svg?style=flat-square

[link-packagist]: https://packagist.org/packages/middlewares/base-path-router
[link-downloads]: https://packagist.org/packages/middlewares/base-path-router
