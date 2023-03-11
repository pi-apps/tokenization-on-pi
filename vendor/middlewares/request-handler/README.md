# middlewares/request-handler

[![Latest Version on Packagist][ico-version]][link-packagist]
[![Software License][ico-license]](LICENSE)
![Testing][ico-ga]
[![Total Downloads][ico-downloads]][link-downloads]

Middleware to execute request handlers discovered by a router.

## Requirements

* PHP >= 7.2
* A [PSR-7 http library](https://github.com/middlewares/awesome-psr15-middlewares#psr-7-implementations)
* A [PSR-15 middleware dispatcher](https://github.com/middlewares/awesome-psr15-middlewares#dispatcher)
* Optionally, a [PSR-11](https://github.com/php-fig/container) container to resolve the route handlers

## Installation

This package is installable and autoloadable via Composer as [middlewares/request-handler](https://packagist.org/packages/middlewares/request-handler).

```sh
composer require middlewares/request-handler
```

You may also want to install any route middleware like [middlewares/fast-route](https://packagist.org/packages/middlewares/fast-route) or [middlewares/aura-router](https://packagist.org/packages/middlewares/aura-router) for routing.

## Purpose

There are two completely separate steps when it comes to route handling:

1. Determining if the request is valid and can be resolved by the application.
2. Handling the request inside the application.

The first step usually resolves into a route callback, while the product of the second one is usually the result of executing that callback.

Multiple things that can happen between the first and second steps: input validation, authentication, authorization, etc.
and in some scenarios we may not want to continue processing the request (e.g. auth, accessing DB resources, etc.) if that would ultimately fail to resolve e.g. procuding an *HTTP 400* error.

Splitting routing from request handling allows us to use any middleware between these two steps. It also makes the `request-handler` middleware able to be used with any routing component.

## Example

A routing middleware needs to be called before the request can be handled. In this example, we will use `fast-route` middleware.

```php
// Create the routing dispatcher
$fastRouteDispatcher = FastRoute\simpleDispatcher(function (FastRoute\RouteCollector $r) {
    $r->get('/hello/{name}', HelloWorldController::class);
});

$dispatcher = new Dispatcher([
    new Middlewares\FastRoute($fastRouteDispatcher),
    // ...
    new Middlewares\RequestHandler(),
]);

$response = $dispatcher->dispatch(new ServerRequest('/hello/world'));
```

When the request handler is invoked, it expects a request attribute to be defined that contains a reference to the handler. The handler must be a string, a callable or an object implementing `MiddlewareInterface` or `RequestHandlerInterface`. If it's a string, a `ContainerInterface` will be used to resolve it and get the `MiddlewareInterface` or `RequestHandlerInterface` to use. If it's a callable, it will be converted automatically to `MiddlewareInterface` using the [`Middlewares\Utils\CallableHandler`](https://github.com/middlewares/utils#callablehandler)

```php
// Use a PSR-11 container to create the intances of the request handlers
$container = new RequestHandlerContainer();

$dispatcher = new Dispatcher([
    // ...
    new Middlewares\RequestHandler($container),
]);
```

## Usage

Define the container used to resolve the handlers if they are provided as string (or an array with 2 strings). By default will use [`Middlewares\Utils\RequestHandlerContainer`](https://github.com/middlewares/utils/blob/master/src/RequestHandlerContainer.php).

```php
// Use the default PSR-11 container to create the intances of the request handlers
$handler = new Middlewares\RequestHandler();

// Use a custom PSR-11 container
$container = new RequestHandlerContainer();

$handler = new Middlewares\RequestHandler($container);
```

### handlerAttribute

Configures the attribute name used to get the handler reference in the server request. The default is `request-handler`.

```php
Dispatcher::run([
    (new Middlewares\RequestHandler())->handlerAttribute('route'),
]);
```

### continueOnEmpty

If the server request attribute is empty or does not exists, an exception is throwed. This function changes this behavior to continue with the next middleware.

```php
Dispatcher::run([
    //Try this, and if it's empty, continue
    (new Middlewares\RequestHandler())->continueOnEmpty(),

    //So we can try that
    (new Middlewares\RequestHandler())->handlerAttribute('other'),
]);
```
---

Please see [CHANGELOG](CHANGELOG.md) for more information about recent changes and [CONTRIBUTING](CONTRIBUTING.md) for contributing details.

The MIT License (MIT). Please see [LICENSE](LICENSE) for more information.

[ico-version]: https://img.shields.io/packagist/v/middlewares/request-handler.svg?style=flat-square
[ico-license]: https://img.shields.io/badge/license-MIT-brightgreen.svg?style=flat-square
[ico-ga]: https://github.com/middlewares/request-handler/workflows/testing/badge.svg
[ico-downloads]: https://img.shields.io/packagist/dt/middlewares/request-handler.svg?style=flat-square

[link-packagist]: https://packagist.org/packages/middlewares/request-handler
[link-downloads]: https://packagist.org/packages/middlewares/request-handler
