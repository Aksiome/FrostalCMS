<?php

declare(strict_types=1);

namespace Frostal;

use Nyholm\Psr7\Factory\Psr17Factory;
use Nyholm\Psr7Server\ServerRequestCreator;
use Psr\Http\Message\ServerRequestInterface;
use Frostal\Http\RequestHandler;
use Frostal\Http\Middleware;

class Application
{
    /**
     * @var RequestHandler
     */
    private $requestHandler;

    /**
     * @var Psr17Factory
     */
    private $httpFactory;

    /**
     * @var mixed[]
     */
    private static $config;

    /**
     * Create the application
     *
     * This method uses a configuration array
     * to create the application instance.
     *
     * @param mixed[] $config
     */
    public function __construct(array $config = [])
    {
        self::$config = array_merge([
            "debug" => false,
            "trailingSlash" => false,
            "panel" => "admin",
            "auth.trials" => 6,
            "auth.timeout" => 3600,
            "templates.path" => null,
            "schemes.path" => null,
            "users.path" => null,
            "media.path" => null,
            "pages.path" => null,
            "pages.index" => "home",
            "session.durationNormal" => 7200,
            "session.durationLong" => 1209600,
            "session.timeout" => 1800,
            "session.cookieName" => "fs",
        ], $config);
        $this->httpFactory = new Psr17Factory();
        $this->requestHandler = new RequestHandler();
        $this->requestHandler
            ->pipe(new Middleware\ExceptionHandler($this->httpFactory), -100)
            ->pipe(new Middleware\TrailingSlash($this->httpFactory), -50);
    }

    /**
     * @return RequestHandler
     */
    public function getRequestHandler(): RequestHandler
    {
        return $this->requestHandler;
    }

    /**
     * Get the configuration
     *
     * This method returns the current configuration.
     *
     * @param string|null $key
     * @return string|mixed[]|null
     */
    public static function config(?string $key = null)
    {
        if (is_null($key)) {
            return self::$config;
        }
        return self::$config[$key];
    }

    /**
     * Run the application
     *
     * This method traverses the middleware queue and
     * returns the HTTP response to the client.
     *
     * @param ServerRequestInterface|null $request
     * @return void
     */
    public function run(?ServerRequestInterface $request = null): void
    {
        $request = $request ?? (new ServerRequestCreator(
            $this->httpFactory,
            $this->httpFactory,
            $this->httpFactory,
            $this->httpFactory
        ))->fromGlobals();
        self::config("panel") !== explode("/", trim($request->getUri()->getPath(), "/"), 2)[0]
            ? $this->requestHandler
                ->pipe(new Middleware\PageHandler($this->httpFactory), 100)
            : $this->requestHandler
                ->pipe(new Middleware\Authentication(), 50)
                ->pipe(new Middleware\PanelHandler(), 100);
        $response = $this->requestHandler->run($request);
        \Http\Response\send($response);
    }
}
