<?php

namespace Franzl\Middleware\Whoops;

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface as Handler;

/**
 * Middleware class for using Whoops with a PSR-15 middleware stack
 */
class WhoopsMiddleware implements MiddlewareInterface
{
    /**
     * Process an incoming server request and return a response, optionally
     * delegating response creation to a handler.
     */
    public function process(Request $request, Handler $handler): Response
    {
        try {
            return $handler->handle($request);
        } catch (\Exception $e) {
            return WhoopsRunner::handle($e, $request);
        }
    }
}
