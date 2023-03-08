<?php

namespace Franzl\Middleware\Whoops\Formats;

use Whoops\Handler\HandlerInterface;
use Whoops\Handler\PlainTextHandler;

class PlainText implements Format
{
    const MIMES = ['text/plain'];

    public function getHandler(): HandlerInterface
    {
        $handler = new PlainTextHandler;
        $handler->addTraceToOutput(true);

        return $handler;
    }

    public function getPreferredContentType()
    {
        return 'text/plain';
    }
}
