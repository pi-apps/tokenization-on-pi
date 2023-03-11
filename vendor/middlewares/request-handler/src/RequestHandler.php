<?php
declare(strict_types = 1);

namespace Middlewares;

use Middlewares\Utils\CallableHandler;
use Middlewares\Utils\RequestHandlerContainer;
use Psr\Container\ContainerInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use RuntimeException;

class RequestHandler implements MiddlewareInterface
{
    /**
     * @var ContainerInterface Used to resolve the handlers
     */
    private $container;

    /**
     * @var bool
     */
    private $continueOnEmpty = false;

    /**
     * @var string Attribute name for handler reference
     */
    private $handlerAttribute = 'request-handler';

    /**
     * Set the resolver instance.
     */
    public function __construct(ContainerInterface $container = null)
    {
        $this->container = $container ?: new RequestHandlerContainer();
    }

    /**
     * Set the attribute name to store handler reference.
     */
    public function handlerAttribute(string $handlerAttribute): self
    {
        $this->handlerAttribute = $handlerAttribute;

        return $this;
    }

    /**
     * Configure whether continue with the next handler if custom requestHandler is empty.
     */
    public function continueOnEmpty(bool $continueOnEmpty = true): self
    {
        $this->continueOnEmpty = $continueOnEmpty;

        return $this;
    }

    /**
     * Process a server request and return a response.
     */
    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        $requestHandler = $request->getAttribute($this->handlerAttribute);

        if (empty($requestHandler)) {
            if ($this->continueOnEmpty) {
                return $handler->handle($request);
            }

            throw new RuntimeException('Empty request handler');
        }

        if (is_string($requestHandler)) {
            $requestHandler = $this->container->get($requestHandler);
        }

        if (is_array($requestHandler) && count($requestHandler) === 2 && is_string($requestHandler[0])) {
            $requestHandler[0] = $this->container->get($requestHandler[0]);
        }

        if ($requestHandler instanceof MiddlewareInterface) {
            return $requestHandler->process($request, $handler);
        }

        if ($requestHandler instanceof RequestHandlerInterface) {
            return $requestHandler->handle($request);
        }

        if (is_callable($requestHandler)) {
            return (new CallableHandler($requestHandler))->process($request, $handler);
        }

        throw new RuntimeException(sprintf('Invalid request handler: %s', gettype($requestHandler)));
    }
}
