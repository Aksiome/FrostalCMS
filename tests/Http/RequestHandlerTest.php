<?php

declare(strict_types=1);

namespace Frostal\Tests\Http;

use Frostal\Http\RequestHandler;
use Nyholm\Psr7\Factory\Psr17Factory;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ResponseFactoryInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;

class RequestHandlerTest extends TestCase
{

    private $requestHandler;
    public function setUp(): void
    {
        $this->requestHandler = new RequestHandler();
    }

    public function testPipingMiddleware()
    {
        $middleware = $this->middlewareThatRespond(new Psr17Factory());
        $this->requestHandler->pipe($middleware);
        $middlewares = $this->requestHandler->getMiddlewares();
        $this->assertContains($middleware, $middlewares);
    }

    public function testPipingInvalidMiddleware()
    {
        $this->expectException(\TypeError::class);
        $this->requestHandler->pipe("foo");
    }

    public function testSortingMiddlewares()
    {
        $httpFactory = new Psr17Factory();
        $middleware1 = $this->middlewareThatRespond($httpFactory, "FIRST");
        $middleware2 = $this->middlewareThatRespond($httpFactory, "SECOND");
        $middleware3 = $this->middlewareThatRespond($httpFactory, "THIRD");
        $this->requestHandler->pipe($middleware3, 3);
        $this->requestHandler->pipe($middleware1, 1);
        $this->requestHandler->pipe($middleware2, 2);
        $this->assertEquals($this->requestHandler->getMiddlewares(), [$middleware3, $middleware1, $middleware2]);
        $this->requestHandler->sortMiddlewares();
        $this->assertEquals($this->requestHandler->getMiddlewares(), [$middleware1, $middleware2, $middleware3]);
    }

    public function testExhaustedQueue()
    {
        $httpFactory = new Psr17Factory();
        $this->expectException(\LogicException::class);
        $this->requestHandler->pipe($this->middlewareThatProcess());
        $this->requestHandler->run($httpFactory->createServerRequest("GET", "/"));
    }

    public function testTraversableQueue()
    {
        $httpFactory = new Psr17Factory();
        $this->requestHandler->pipe($this->middlewareThatProcess("FOO"));
        $this->requestHandler->pipe($this->middlewareThatRespond($httpFactory, "BAR"));
        $response = $this->requestHandler->run($httpFactory->createServerRequest("GET", "/"));
        $this->assertEquals(200, $response->getStatusCode());
        $this->assertEquals("FOOBAR", (string) $response->getBody());
    }

    private function middlewareThatRespond(
        ResponseFactoryInterface $responseFactory,
        ?string $message = null
    ): MiddlewareInterface {
        return new class ($responseFactory, $message) implements MiddlewareInterface {
            public function __construct(
                ResponseFactoryInterface $responseFactory,
                ?string $message
            ) {
                $this->message = $message ?? "";
                $this->responseFactory = $responseFactory;
            }
            public function process(
                ServerRequestInterface $request,
                RequestHandlerInterface $handler
            ): ResponseInterface {
                $response = $this->responseFactory->createResponse();
                $response->getBody()->write($request->getAttribute("message", "") . $this->message);
                return $response;
            }
        };
    }

    private function middlewareThatProcess(?string $message = null)
    {
        return new class ($message) implements MiddlewareInterface {
            public function __construct(?string $message)
            {
                $this->message = $message ?? "";
            }
            public function process(
                ServerRequestInterface $request,
                RequestHandlerInterface $handler
            ): ResponseInterface {
                return $handler->handle($request->withAttribute("message", $this->message));
            }
        };
    }
}
