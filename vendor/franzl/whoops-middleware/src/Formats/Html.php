<?php

namespace Franzl\Middleware\Whoops\Formats;

use Whoops\Handler\HandlerInterface;
use Whoops\Handler\PrettyPageHandler;

class Html implements Format
{
    const MIMES = ['text/html', 'application/xhtml+xml'];

    public function getHandler(): HandlerInterface
    {
        return new PrettyPageHandler;
    }

    public function getPreferredContentType()
    {
        return 'text/html';
    }
}
