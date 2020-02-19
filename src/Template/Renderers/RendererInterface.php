<?php

declare(strict_types=1);

namespace Frostal\Template\Renderers;

use Psr\Http\Message\ResponseInterface;

interface RendererInterface
{
    /**
     * Renders the HTML template and returns a response
     *
     * @param string $name
     * @param mixed[] $data
     * @return ResponseInterface
     */
    public function render(string $name, array $data): ResponseInterface;
}
