<?php

declare(strict_types=1);

namespace Frostal\Tests\Template\Renderers;

use Frostal\Template\Renderers\RendererInterface;
use Frostal\Template\Renderers\LatteRenderer;
use Nyholm\Psr7\Factory\Psr17Factory;
use PHPUnit\Framework\TestCase;

class LatteRendererTest extends TestCase
{
    /**
     * @var RendererInterface
     */
    private $renderer;

    public function setUp(): void
    {
        $this->renderer = new LatteRenderer(new Psr17Factory(), __DIR__ . "/fixtures/", __DIR__ . "/fixtures/tmp");
    }

    public function testRenderer()
    {
        $response = $this->renderer->render("latte.html", ["foo" => "BAR"]);
        $this->assertEquals("FOOBAR", (string) $response->getBody());
        $this->clearCache();
    }

    private function clearCache()
    {
        $di = new \RecursiveDirectoryIterator(__DIR__ . "/fixtures/tmp/", \FilesystemIterator::SKIP_DOTS);
        $ri = new \RecursiveIteratorIterator($di, \RecursiveIteratorIterator::CHILD_FIRST);
        foreach ($ri as $file) {
            $file->isDir() ? rmdir($file->getRealPath()) : unlink($file->getRealPath());
        }
        return true;
    }
}
