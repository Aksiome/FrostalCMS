<?php

declare(strict_types=1);

namespace Frostal\Http;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;

class RequestHandler implements RequestHandlerInterface
{
    /**
     * @var array[]
     */
    protected $middlewares = [];

    /**
     * @var integer
     */
    protected $currentMiddleware = -1;

    /**
     * @return MiddlewareInterface[]
     */
    public function getMiddlewares(): array
    {
        return array_map(function ($x) {
            return $x[0];
        }, $this->middlewares);
    }

    /**
     * Sort each middleware by priority
     *
     * @return void
     */
    public function sortMiddlewares(): void
    {
        usort($this->middlewares, function ($x, $y) {
            return $x[1] <=> $y[1];
        });
    }

    /**
     * Add a new middleware to the queue
     *
     * @param MiddlewareInterface $middleware
     * @param integer $priority
     * @return self
     */
    public function pipe(MiddlewareInterface $middleware, int $priority = 0): self
    {
        $this->middlewares[] = [$middleware, $priority];
        return $this;
    }

    /**
     * Handle a request
     *
     * @param ServerRequestInterface $request
     * @return ResponseInterface
     */
    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        $this->currentMiddleware++;
        if (isset($this->middlewares[$this->currentMiddleware]) === false) {
            throw new \LogicException('Middleware queue exhausted');
        }

        $middleware = $this->middlewares[$this->currentMiddleware][0];
        $response = $middleware->process($request, $this);
        return $response;
    }

    /**
     * Run the RequestHandler
     *
     * This method traverses the middleware queue and
     * returns the appropriate Response object.
     *
     * @param ServerRequestInterface $request
     * @return ResponseInterface
     */
    public function run(ServerRequestInterface $request): ResponseInterface
    {
        $this->sortMiddlewares();
        return $this->handle($request);
    }
}
