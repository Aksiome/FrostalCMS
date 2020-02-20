<?php

declare(strict_types=1);

namespace Frostal\Http\Middleware;

use Psr\Http\Message\ResponseFactoryInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;

class TrailingSlash implements MiddlewareInterface
{
    /**
     * @var ResponseFactoryInterface
     */
    private $responseFactory;

    /**
     * @var boolean
     */
    private $trailingSlash;

    /**
     * @param ResponseFactoryInterface $responseFactory
     * @param boolean $trailingSlash
     */
    public function __construct(ResponseFactoryInterface $responseFactory, bool $trailingSlash = false)
    {
        $this->responseFactory = $responseFactory;
        $this->trailingSlash = $trailingSlash;
    }

    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        $path = $request->getUri()->getPath();
        $redirectPath = $this->normalize($path);
        if ($path !== $redirectPath) {
            return $this->responseFactory->createResponse(301)
                ->withHeader("Location", $redirectPath);
        }
        return $handler->handle($request);
    }

    /**
     * Normalize the given path
     *
     * @param string $path
     * @return string
     */
    private function normalize(string $path): string
    {
        $path = rtrim($path, "/");
        if ($path === "") {
            return "/";
        }
        return $this->trailingSlash ? $path . "/" : $path;
    }
}
