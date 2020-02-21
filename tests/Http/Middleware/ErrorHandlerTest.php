<?php

declare(strict_types=1);

namespace Frostal\Tests\Http\Middleware;

use Frostal\Http\HttpException;
use Frostal\Http\Middleware\ErrorHandler;
use Nyholm\Psr7\Factory\Psr17Factory;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ResponseFactoryInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;

class ErrorHandlerTest extends TestCase
{
    /**
     * @var Psr17Factory
     */
    private $httpFactory;

    public function setUp(): void
    {
        $this->httpFactory = new Psr17Factory();
    }

    public function testSuccessfulRequest()
    {
        $response = (new ErrorHandler($this->httpFactory, false))->process(
            $this->httpFactory->createServerRequest("GET", "/"),
            $this->handlerThatReturns($this->httpFactory)
        );
        $this->assertEquals(200, $response->getStatusCode());
        $this->assertEquals("OK", $response->getReasonPhrase());
    }

    public function testHttpException()
    {
        $response = (new ErrorHandler($this->httpFactory, false))->process(
            $this->httpFactory->createServerRequest("GET", "/"),
            $this->handlerThatThrowsHttpException(404)
        );
        $this->assertEquals(404, $response->getStatusCode());
    }

    public function testError()
    {
        $response = (new ErrorHandler($this->httpFactory, true))->process(
            $this->httpFactory->createServerRequest("GET", "/"),
            $this->handlerThatThrowsError()
        );
        $this->assertEquals(500, $response->getStatusCode());
    }

    public function testXmlErrorFormatWithDebug()
    {
        $response = (new ErrorHandler($this->httpFactory, true))->process(
            $this->httpFactory->createServerRequest("GET", "/")->withHeader("accept", "application/xml"),
            $this->handlerThatThrowsError()
        );
        $this->assertEquals("application/xml", $response->getHeaderLine("Content-Type"));
        $this->assertStringStartsWith("<?xml", (string) $response->getBody());
        $this->assertStringContainsString("trace", (string) $response->getBody());
    }

    public function testJsonErrorFormatWithDebug()
    {
        $response = (new ErrorHandler($this->httpFactory, true))->process(
            $this->httpFactory->createServerRequest("GET", "/")->withHeader("accept", "application/json"),
            $this->handlerThatThrowsError()
        );
        $this->assertEquals("application/json", $response->getHeaderLine("Content-Type"));
        $this->assertStringStartsWith("{", (string) $response->getBody());
        $this->assertStringEndsWith("}", (string) $response->getBody());
        $this->assertStringContainsString("trace", (string) $response->getBody());
    }

    public function testHtmlErrorFormatWithDebug()
    {
        $response = (new ErrorHandler($this->httpFactory, true))->process(
            $this->httpFactory->createServerRequest("GET", "/")->withHeader("accept", "text/html"),
            $this->handlerThatThrowsError()
        );
        $this->assertEquals("text/html", $response->getHeaderLine("Content-Type"));
    }

    public function testXmlErrorFormatWithoutDebug()
    {
        $response = (new ErrorHandler($this->httpFactory, false))->process(
            $this->httpFactory->createServerRequest("GET", "/")->withHeader("accept", "text/xml"),
            $this->handlerThatThrowsError()
        );
        $this->assertEquals("application/xml", $response->getHeaderLine("Content-Type"));
        $this->assertStringStartsWith("<?xml", (string) $response->getBody());
        $this->assertStringNotContainsString("trace", (string) $response->getBody());
    }

    public function testJsonErrorFormatWithoutDebug()
    {
        $response = (new ErrorHandler($this->httpFactory, false))->process(
            $this->httpFactory->createServerRequest("GET", "/")->withHeader("accept", "text/json"),
            $this->handlerThatThrowsError()
        );
        $this->assertEquals("application/json", $response->getHeaderLine("Content-Type"));
        $this->assertStringStartsWith("{", (string) $response->getBody());
        $this->assertStringEndsWith("}", (string) $response->getBody());
        $this->assertStringNotContainsString("trace", (string) $response->getBody());
    }

    public function testHtmlErrorFormatWithoutDebug()
    {
        $response = (new ErrorHandler($this->httpFactory, false))->process(
            $this->httpFactory->createServerRequest("GET", "/")->withHeader("accept", "text/html"),
            $this->handlerThatThrowsError()
        );
        $this->assertEquals("text/html", $response->getHeaderLine("Content-Type"));
        $this->assertStringStartsWith("<!DOCTYPE html>", (string) $response->getBody());
        $this->assertStringNotContainsString("trace", (string) $response->getBody());
    }

    public function testPreferredContentTypeParsing()
    {
        $response = (new ErrorHandler($this->httpFactory, false))->process(
            $this->httpFactory->createServerRequest("GET", "/")
                ->withHeader("accept", ["foo", "foo/bar", "bar", "text/json", "text/html"]),
            $this->handlerThatThrowsError()
        );
        $this->assertEquals("application/json", $response->getHeaderLine("Content-Type"));
    }

    private function handlerThatReturns(ResponseFactoryInterface $responseFactory)
    {
        return new class ($responseFactory) implements RequestHandlerInterface {
            public function __construct(ResponseFactoryInterface $responseFactory)
            {
                $this->responseFactory = $responseFactory;
            }
            public function handle(ServerRequestInterface $request): ResponseInterface
            {
                $response = $this->responseFactory->createResponse(200, "OK");
                return $response;
            }
        };
    }

    private function handlerThatThrowsHttpException(int $code)
    {
        return new class ($code) implements RequestHandlerInterface {
            public function __construct(int $code)
            {
                $this->code = $code;
            }
            public function handle(ServerRequestInterface $request): ResponseInterface
            {
                throw new HttpException($this->code);
            }
        };
    }

    private function handlerThatThrowsError()
    {
        return new class implements RequestHandlerInterface {
            public function handle(ServerRequestInterface $request): ResponseInterface
            {
                throw new \Error();
            }
        };
    }
}
