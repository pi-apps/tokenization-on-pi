<?php

namespace Franzl\Middleware\Whoops\Formats;

use Whoops\Handler\HandlerInterface;
use Whoops\Handler\JsonResponseHandler;

class Json implements Format
{
    const MIMES = ['application/json', 'text/json', 'application/x-json'];

    public function getHandler(): HandlerInterface
    {
        $handler = new JsonResponseHandler;
        $handler->addTraceToOutput(true);

        return $handler;
    }

    public function getPreferredContentType()
    {
        return 'application/json';
    }
}
