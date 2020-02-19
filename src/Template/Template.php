<?php

declare(strict_types=1);

namespace Frostal\Template;

use Frostal\Template\Renderers\RendererInterface;
use Psr\Http\Message\ResponseInterface;

class Template
{
    /**
     * @var RendererInterface
     */
    private static $renderer;

    /**
     * @param RendererInterface $renderer
     * @return void
     */
    public static function setRenderer(RendererInterface $renderer): void
    {
        self::$renderer = $renderer;
    }

    /**
     * @return RendererInterface
     */
    public static function getRenderer(): RendererInterface
    {
        return self::$renderer;
    }

    /**
     * Uses the selected renderer to transform the HTML template into a Response object
     *
     * @param string $name
     * @param mixed[] $data
     * @return ResponseInterface
     */
    public static function render(string $name, array $data): ResponseInterface
    {
        return self::getRenderer()->render($name, $data);
    }
}
