<?php

namespace Franzl\Middleware\Whoops;

use Middlewares\Utils\Factory;
use Psr\Http\Message\ServerRequestInterface;
use Whoops\Handler\PlainTextHandler;
use Whoops\Run;

class WhoopsRunner
{
    public static function handle($error, ServerRequestInterface $request)
    {
        $method = Run::EXCEPTION_HANDLER;

        $format = FormatNegotiator::negotiate($request);
        $whoops = self::getWhoopsInstance($format);

        // Output is managed by the middleware pipeline
        $whoops->allowQuit(false);

        ob_start();
        $whoops->$method($error);
        $content = ob_get_clean();

        return Factory::createResponse(500)
            ->withBody(Factory::createStream($content))
            ->withHeader('Content-Type', $format->getPreferredContentType());
    }

    private static function getWhoopsInstance(Formats\Format $format)
    {
        $whoops = new Run();
        if (php_sapi_name() === 'cli') {
            $whoops->pushHandler(new PlainTextHandler);
            return $whoops;
        }

        $whoops->pushHandler($format->getHandler());
        return $whoops;
    }
}
