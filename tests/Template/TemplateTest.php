<?php

declare(strict_types=1);

namespace Frostal\Tests\Template;

use Frostal\Template\Renderers\RendererInterface;
use Frostal\Template\Template;
use Nyholm\Psr7\Factory\Psr17Factory;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ResponseInterface;

class TemplateTest extends TestCase
{
    public function testRenderingStrategies()
    {
        $renderer = $this->renderer();
        Template::setRenderer($renderer);
        $this->assertEquals($renderer, Template::getRenderer());
        $response = Template::render("template", ["foo" => "bar"]);
        $this->assertEquals("template", (string) $response->getBody());
        $this->assertEquals(["foo" => "bar"], json_decode($response->getHeaderLine("data"), true));
    }

    private function renderer(): RendererInterface
    {
        return new class implements RendererInterface {
            public function render(string $name, array $data): ResponseInterface
            {
                $responseFactory = new Psr17Factory();
                $response = $responseFactory->createResponse();
                $response->getBody()->write($name);
                return $response->withHeader("data", json_encode($data));
            }
        };
    }
}
