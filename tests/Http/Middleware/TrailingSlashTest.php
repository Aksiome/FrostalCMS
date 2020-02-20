<?php

declare(strict_types=1);

namespace Frostal\Tests\Http\Middleware;

use Frostal\Http\Middleware\TrailingSlash;
use Nyholm\Psr7\Factory\Psr17Factory;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ResponseFactoryInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;

class TrailingSlashTest extends TestCase
{
    /**
     * @var Psr17Factory
     */
    private $httpFactory;

    public function setUp(): void
    {
        $this->httpFactory = new Psr17Factory();
    }

    public function testWithTrailingSlash()
    {
        $paths = [
            "/foo/bar"  => "/foo/bar/",
            "/foo/bar/" => "/foo/bar/",
            "/"         => "/",
            ""          => "/"
        ];
        $this->checkPaths(new TrailingSlash($this->httpFactory, true), $paths);
    }

    public function testWithoutTrailingSlash()
    {
        $paths = [
            "/foo/bar"  => "/foo/bar",
            "/foo/bar/" => "/foo/bar",
            "/"         => "/",
            ""          => "/"
        ];
        $this->checkPaths(new TrailingSlash($this->httpFactory, false), $paths);
    }

    private function checkPaths(TrailingSlash $middleware, array $paths)
    {
        foreach ($paths as $providedPath => $expectedPath) {
            $request = $this->httpFactory->createServerRequest("GET", $providedPath);
            $response = $middleware->process($request, $this->middlewareThatRespondRequestedPath($this->httpFactory));
            $response->getStatusCode() === 301
                ? $this->assertEquals($expectedPath, $response->getHeaderLine("Location"))
                : $this->assertEquals($expectedPath, (string) $response->getBody());
        }
    }

    private function middlewareThatRespondRequestedPath(ResponseFactoryInterface $responseFactory)
    {
        return new class ($responseFactory) implements RequestHandlerInterface {
            public function __construct(ResponseFactoryInterface $responseFactory)
            {
                $this->responseFactory = $responseFactory;
            }
            public function handle(ServerRequestInterface $request): ResponseInterface
            {
                $response = $this->responseFactory->createResponse();
                $response->getBody()->write($request->getUri()->getPath());
                return $response;
            }
        };
    }
}
