<?php

declare(strict_types=1);

namespace Frostal\Tests;

use Frostal\Application;
use Frostal\Http\Middleware\PanelHandler;
use Nyholm\Psr7\Factory\Psr17Factory;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\RequestFactoryInterface;
use Psr\Http\Message\ResponseFactoryInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestFactoryInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\StreamFactoryInterface;
use Psr\Http\Message\UploadedFileFactoryInterface;
use Psr\Http\Message\UriFactoryInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;

class ApplicationTest extends TestCase
{
    public function testConfiguration()
    {
        new Application([
            "foo" => "bar"
        ]);
        $this->assertEquals("bar", Application::config("foo"));
        $this->assertEquals("bar", Application::config()["foo"]);
    }

    public function testHttpFactory()
    {
        $app = new Application();
        $this->assertInstanceOf(ResponseFactoryInterface::class, $app->getHttpFactory());
        $this->assertInstanceOf(ServerRequestFactoryInterface::class, $app->getHttpFactory());
        $this->assertInstanceOf(RequestFactoryInterface::class, $app->getHttpFactory());
        $this->assertInstanceOf(StreamFactoryInterface::class, $app->getHttpFactory());
        $this->assertInstanceOf(UploadedFileFactoryInterface::class, $app->getHttpFactory());
        $this->assertInstanceOf(UriFactoryInterface::class, $app->getHttpFactory());
    }

    /**
     * @test
     * @runInSeparateProcess
     */
    public function testRunning()
    {
        $httpFactory = new Psr17Factory();
        $app = new Application();
        $app->getRequestHandler()->pipe($this->middlewareThatReturns($httpFactory, "Hello world"), -200);
        $app->run();
        $this->expectOutputString("Hello world");
    }

    /**
     * @test
     * @runInSeparateProcess
     */
    public function testPanelRouting()
    {
        $httpFactory = new Psr17Factory();
        $app = new Application();
        $app->getRequestHandler()->pipe($this->middlewareThatReturns($httpFactory), -200);
        $app->run($httpFactory->createServerRequest("GET", "/" . Application::config("panel")));
        $middlewares = $app->getRequestHandler()->getMiddlewares();
        $this->assertTrue((function () use ($middlewares) {
            $handler = new PanelHandler();
            foreach ($middlewares as $middleware) {
                if ($middleware == $handler) {
                    return true;
                }
            }
            return false;
        })());
    }

    private function middlewareThatReturns(
        ResponseFactoryInterface $responseFactory,
        ?string $message = null
    ): MiddlewareInterface {
        return new class ($responseFactory, $message) implements MiddlewareInterface {
            public function __construct(ResponseFactoryInterface $responseFactory, ?string $message)
            {
                $this->message = $message ?? "";
                $this->responseFactory = $responseFactory;
            }
            public function process(
                ServerRequestInterface $request,
                RequestHandlerInterface $handler
            ): ResponseInterface {
                $response = $this->responseFactory->createResponse();
                $response->getBody()->write($this->message);
                return $response;
            }
        };
    }
}
