<?php

namespace Franzl\Middleware\Whoops\Test;

use Exception;
use Franzl\Middleware\Whoops\WhoopsMiddleware;
use Laminas\Diactoros\Response\TextResponse;
use Laminas\Diactoros\ServerRequest;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;

class WhoopsMiddlewareTest extends TestCase
{
    public function test_successful_request_is_left_untouched()
    {
        $response = (new WhoopsMiddleware)->process(
            new ServerRequest,
            $this->handlerThatReturns(new TextResponse('Success!'))
        );

        $this->assertEquals(200, $response->getStatusCode());
        $this->assertEquals('Success!', $response->getBody());
    }

    public function test_exception_is_handled()
    {
        $response = (new WhoopsMiddleware)->process(
            new ServerRequest,
            $this->handlerThatThrowsException()
        );

        $this->assertEquals(500, $response->getStatusCode());
        $this->assertEquals('text/html', $response->getHeaderLine('content-type'));
    }

    /**
     * @dataProvider knownTypes
     */
    public function test_known_mime_types_will_return_preferred_content_type($mime, $expectedContentType)
    {
        $response = (new WhoopsMiddleware)->process(
            $this->requestWithAccept($mime),
            $this->handlerThatThrowsException()
        );

        $this->assertEquals(500, $response->getStatusCode());
        $this->assertEquals($expectedContentType, $response->getHeaderLine('content-type'));
    }

    public function knownTypes()
    {
        yield ['text/html', 'text/html'];
        yield ['application/xhtml+xml', 'text/html'];
        yield ['application/json', 'application/json'];
        yield ['text/json', 'application/json'];
        yield ['application/x-json', 'application/json'];
        yield ['text/xml', 'text/xml'];
        yield ['application/xml', 'text/xml'];
        yield ['application/x-xml', 'text/xml'];
        yield ['text/plain', 'text/plain'];
    }

    public function test_multiple_mime_types_will_prefer_the_first_match()
    {
        $response = (new WhoopsMiddleware)->process(
            $this->requestWithAccept('application/xml, application/json'),
            $this->handlerThatThrowsException()
        );

        $this->assertEquals(500, $response->getStatusCode());
        $this->assertEquals('text/xml', $response->getHeaderLine('content-type'));

        // Test vice versa to avoid false positives
        $response = (new WhoopsMiddleware)->process(
            $this->requestWithAccept('application/json, application/xml'),
            $this->handlerThatThrowsException()
        );

        $this->assertEquals(500, $response->getStatusCode());
        $this->assertEquals('application/json', $response->getHeaderLine('content-type'));
    }

    public function test_unknown_mime_types_will_fall_back_to_plain_text()
    {
        $response = (new WhoopsMiddleware)->process(
            $this->requestWithAccept('foo/bar, x/custom'),
            $this->handlerThatThrowsException()
        );

        $this->assertEquals(500, $response->getStatusCode());
        $this->assertEquals('text/plain', $response->getHeaderLine('content-type'));
    }

    private function handlerThatReturns(ResponseInterface $response)
    {
        return new class($response) implements RequestHandlerInterface {
            public function __construct($response) {
                $this->response = $response;
            }
            public function handle(ServerRequestInterface $request): ResponseInterface {
                return $this->response;
            }
        };
    }

    private function handlerThatThrowsException()
    {
        return new class implements RequestHandlerInterface {
            public function handle(ServerRequestInterface $request): ResponseInterface {
                throw new Exception;
            }
        };
    }

    private function requestWithAccept($acceptHeader)
    {
        return (new ServerRequest)->withHeader('accept', $acceptHeader);
    }
}
