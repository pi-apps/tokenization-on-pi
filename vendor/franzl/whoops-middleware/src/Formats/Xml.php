<?php

namespace Franzl\Middleware\Whoops\Formats;

use Whoops\Handler\HandlerInterface;
use Whoops\Handler\XmlResponseHandler;

class Xml implements Format
{
    const MIMES = ['text/xml', 'application/xml', 'application/x-xml'];

    public function getHandler(): HandlerInterface
    {
        $handler = new XmlResponseHandler;
        $handler->addTraceToOutput(true);

        return $handler;
    }

    public function getPreferredContentType()
    {
        return 'text/xml';
    }
}
