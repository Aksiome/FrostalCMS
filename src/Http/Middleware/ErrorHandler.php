<?php

declare(strict_types=1);

namespace Frostal\Http\Middleware;

use Frostal\Http\HttpException;
use Psr\Http\Message\ResponseFactoryInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Whoops\Handler\PrettyPageHandler;
use Whoops\Run;

class ErrorHandler implements MiddlewareInterface
{
    /**
     * @var ResponseFactoryInterface
     */
    private $responseFactory;

    /**
     * @var boolean
     */
    private $debug;

    /**
     * @param ResponseFactoryInterface $responseFactory
     */
    public function __construct(ResponseFactoryInterface $responseFactory, bool $debug)
    {
        $this->responseFactory = $responseFactory;
        $this->debug = $debug;
    }

    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        ob_start();
        $level = ob_get_level();

        $whoops = $this->getWhoopsRunner();
        $whoopsHandler = $this->getWhoopsHandler($request->getHeaderLine('Accept'));
        if (method_exists($whoopsHandler, "debug")) {
            $whoopsHandler->debug($this->debug);
        }
        $whoops->appendHandler($whoopsHandler);
        $whoops->register();

        register_shutdown_function(function () use ($whoops) {
            $whoops->allowQuit(true);
            $whoops->writeToOutput(true);
            $whoops->sendHttpCode(true);
            $whoops->{Run::SHUTDOWN_HANDLER}();
        });

        try {
            $response = $handler->handle($request);
        } catch (\Throwable $exception) {
            $response = ($exception instanceof HttpException
                ? $this->responseFactory->createResponse($exception->getCode())
                : $response = $this->responseFactory->createResponse(500)
            )->withHeader("Content-Type", $whoopsHandler->contentType());
            $response->getBody()->write($whoops->{Run::EXCEPTION_HANDLER}($exception));
        } finally {
            while (ob_get_level() >= $level) {
                ob_end_clean();
            }
        }

        $whoops->unregister();
        return $response;
    }

    /**
     * Returns the Whoops Runner
     *
     * @param string $contentType
     * @return Run
     */
    private function getWhoopsRunner(): Run
    {
        $whoops = new Run();
        $whoops->allowQuit(false);
        $whoops->writeToOutput(false);
        $whoops->sendHttpCode(false);
        return $whoops;
    }

    /**
     * Returns Whoops preferred handler
     *
     * @param string $acceptHeaderLine
     * @return mixed
     */
    private function getWhoopsHandler(string $acceptHeaderLine)
    {
        $handlers = [
            "text/html"             => $this->debug ? PrettyPageHandler::class : ErrorHandlers\HtmlHandler::class,
            "application/xhtml+xml" => $this->debug ? PrettyPageHandler::class : ErrorHandlers\HtmlHandler::class,
            "application/json"      => ErrorHandlers\JsonHandler::class,
            "text/json"             => ErrorHandlers\JsonHandler::class,
            "application/x-json"    => ErrorHandlers\JsonHandler::class,
            "application/xml"       => ErrorHandlers\XmlHandler::class,
            "text/xml"              => ErrorHandlers\XmlHandler::class,
        ];

        $formats = explode(",", $acceptHeaderLine);
        foreach ($formats as $format) {
            $format = trim($format);
            if (!array_key_exists($format, $handlers)) {
                continue;
            }
            return new $handlers[$format]();
        }
        $defaultHandler = $this->debug ? PrettyPageHandler::class : ErrorHandlers\HtmlHandler::class;
        return new $defaultHandler();
    }
}
