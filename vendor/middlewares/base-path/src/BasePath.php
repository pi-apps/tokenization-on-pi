<?php
declare(strict_types = 1);

namespace Middlewares;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;

class BasePath implements MiddlewareInterface
{
    /**
     * @var string The path prefix to remove
     */
    private $basePath;

    /**
     * @var string The attribute name
     */
    private $attribute = null;

    /**
     * @var bool Whether or not add the base path to the Location header if exists
     */
    private $fixLocation = false;

    /**
     * Configure the base path of the request.
     */
    public function __construct(string $basePath)
    {
        $this->basePath = rtrim($basePath, '/');

        if (substr($this->basePath, 0, 1) !== '/') {
            $this->basePath = '/'.$this->basePath;
        }
    }

    /**
     * Whether fix the Location header in the response if exists.
     */
    public function fixLocation(bool $fixLocation = true): self
    {
        $this->fixLocation = $fixLocation;

        return $this;
    }

    /**
     * Set the attribute name to store the pre base path uri.
     */
    public function attribute(string $attribute): self
    {
        $this->attribute = $attribute;

        return $this;
    }

    /**
     * Process a server request and return a response.
     */
    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        $uri = $request->getUri();

        if ($this->attribute !== null) {
            $request = $request->withAttribute($this->attribute, $uri->getPath());
        }

        $request = $request->withUri($uri->withPath($this->removeBasePath($uri->getPath())));

        $response = $handler->handle($request);

        if ($this->fixLocation
         && $response->hasHeader('Location')
         && $location = parse_url($response->getHeaderLine('Location'))
         ) {
            if (empty($location['host']) || $location['host'] === $uri->getHost()) {
                $location['path'] = $this->addBasePath($location['path']);

                return $response->withHeader('Location', self::unParseUrl($location));
            }
        }

        return $response;
    }

    /**
     * Removes the basepath from a path.
     */
    private function removeBasePath(string $path): string
    {
        if (strpos($path, $this->basePath) === 0) {
            $path = substr($path, strlen($this->basePath)) ?: '';
        }

        if (substr($path, 0, 1) !== '/') {
            return '/'.$path;
        }

        return $path;
    }

    /**
     * Adds the basepath to a path.
     */
    private function addBasePath(string $path): string
    {
        if (strpos($path, $this->basePath) === 0) {
            return $path;
        }

        return str_replace('//', '/', $this->basePath.'/'.$path);
    }

    /**
     * Stringify a url parsed with parse_url()
     */
    private function unParseUrl(array $url): string
    {
        $scheme = isset($url['scheme']) ? sprintf('%s://', $url['scheme']) : '';
        $host = $url['host'] ?? '';
        $port = isset($url['port']) ? sprintf(':%s', $url['port']) : '';
        $user = $url['user'] ?? '';
        $pass = isset($url['pass']) ? sprintf(':%s', $url['pass']) : '';
        $pass = ($user || $pass) ? sprintf('%s@', $pass) : '';
        $path = $url['path'] ?? '';
        $query = isset($url['query']) ? sprintf('?%s', $url['query']) : '';
        $fragment = isset($url['fragment']) ? sprintf('#%s', $url['fragment']) : '';

        return "{$scheme}{$user}{$pass}{$host}{$port}{$path}{$query}{$fragment}";
    }
}
