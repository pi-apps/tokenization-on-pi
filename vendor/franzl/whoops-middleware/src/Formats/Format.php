<?php

namespace Franzl\Middleware\Whoops\Formats;

use Whoops\Handler\HandlerInterface;

interface Format
{
    public function getHandler(): HandlerInterface;
    public function getPreferredContentType();
}
