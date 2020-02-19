<?php

declare(strict_types=1);

namespace Frostal\Template\Renderers;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ResponseFactoryInterface;
use Twig\Environment;
use Twig\Loader\FilesystemLoader;

class TwigRenderer implements RendererInterface
{
    /**
     * @var ResponseFactoryInterface
     */
    private $responseFactory;
    
    /**
     * @var Environment
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
        $loader = new FilesystemLoader($namespace);
        $this->engine = new Environment($loader, ["cache" => $tempDir]);
    }

    public function render(string $name, array $data): ResponseInterface
    {
        $response = $this->responseFactory->createResponse();
        $content = $this->engine->render($name, $data);
        $response->getBody()->write($content);
        return $response;
    }
}
