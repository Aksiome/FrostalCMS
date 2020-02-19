<?php

declare(strict_types=1);

namespace Frostal\Template\Renderers;

use Latte\Engine;
use Latte\Loaders\FileLoader;
use Psr\Http\Message\ResponseFactoryInterface;
use Psr\Http\Message\ResponseInterface;

class LatteRenderer implements RendererInterface
{
    /**
     * @var ResponseFactoryInterface
     */
    private $responseFactory;

    /**
     * @var Engine
     */
    private $engine;
    
    /**
     * @param ResponseFactoryInterface $responseFactory
     * @param string $namespace
     * @param string $tempDir
     */
    public function __construct(ResponseFactoryInterface $responseFactory, string $namespace, string $tempDir)
    {
        $this->responseFactory = $responseFactory;
        $this->engine = new Engine();
        $this->engine->setTempDirectory($tempDir);
        $this->engine->setLoader(new FileLoader($namespace));
    }

    public function render(string $name, array $data): ResponseInterface
    {
        $response = $this->responseFactory->createResponse();
        $content = $this->engine->renderToString($name, $data);
        $response->getBody()->write($content);
        return $response;
    }
}
