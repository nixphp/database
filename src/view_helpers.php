<?php

use PHPico\Core\Asset;
use PHPico\Core\View;
use Psr\Http\Message\ResponseInterface;
use function PHPico\app;
use function PHPico\response;

function render(string $template, array $vars = []): ResponseInterface
{
    return response(\PHPico\view($template, $vars));
}

function view(string $template, array $vars = []): string
{
    return (new View())->setTemplate($template)->setVariables($vars)->render();
}

function asset(): Asset
{
    return app()->container()->get('asset');
}