<?php

namespace App\Handler;

use App\TheDialgaTeam\Discord\NancyGateway;
use Psr\Container\ContainerInterface;
use Zend\Expressive\Template\TemplateRendererInterface;

class HomeHandlerFactory
{
    public function __invoke(ContainerInterface $container)
    {
        $templateRenderer = $container->get(TemplateRendererInterface::class);
        $nancyGateway = $container->get(NancyGateway::class);

        return new HomeHandler($templateRenderer, $nancyGateway);
    }
}