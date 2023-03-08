# PSR-15 middleware for Whoops

A PSR-15 compatible middleware for [Whoops](https://github.com/filp/whoops), the fantastic pretty error handler for PHP.

## Installation

You can install the library using Composer:

    composer require franzl/whoops-middleware

## Usage

Assuming you are using a PSR-15 compatible middleware dispatcher (such as [zend-stratigility](https://github.com/zendframework/zend-stratigility), [Relay](http://relayphp.com/2.x), or [broker](https://github.com/northwoods/broker)), all you need to do is add the middleware class to your pipeline / broker / dispatcher ...

This might look as follows:

### Stratigility

```php
$pipe->pipe(new \Franzl\Middleware\Whoops\WhoopsMiddleware)
```

### Relay

```php
$queue = [];
// ...
$queue[] = new \Franzl\Middleware\Whoops\WhoopsMiddleware;
$relay = new Relay($queue);
```

### broker

```php
$broker->always(\Franzl\Middleware\Whoops\WhoopsMiddleware::class)
```
