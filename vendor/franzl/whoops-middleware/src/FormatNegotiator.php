<?php

namespace Franzl\Middleware\Whoops;

use Psr\Http\Message\ServerRequestInterface;

/**
 * Detect any of the supported preferred formats from a HTTP request
 */
class FormatNegotiator
{
    /**
     * @var array Available format handlers
     */
    private static $formats = [
        Formats\Html::class,
        Formats\Json::class,
        Formats\PlainText::class,
        Formats\Xml::class,
    ];

    /**
     * Returns the preferred format based on the Accept header
     *
     * @param ServerRequestInterface $request
     * @return Formats\Format
     */
    public static function negotiate(ServerRequestInterface $request): Formats\Format
    {
        $acceptTypes = $request->getHeader('accept');

        if (count($acceptTypes) > 0) {
            $acceptType = $acceptTypes[0];

            // As many formats may match for a given Accept header, let's try to find the one that fits the best
            // We do this by storing the best (i.e. earliest) match for each type.
            $memo = [];
            foreach (self::$formats as $format) {
                foreach ($format::MIMES as $value) {
                    if (! isset($memo[$format])) {
                        $memo[$format] = PHP_INT_MAX;
                    }

                    $match = strpos($acceptType, $value);
                    if ($match !== false) {
                        $memo[$format] = min($match, $memo[$format]);
                    }
                }
            }

            // Sort the array to retrieve the format that best matches the Accept header
            asort($memo);
            reset($memo);

            if (current($memo) == PHP_INT_MAX) {
                return new Formats\PlainText;
            } else {
                $class = key($memo);
                return new $class;
            }
        }

        return new Formats\Html;
    }
}
