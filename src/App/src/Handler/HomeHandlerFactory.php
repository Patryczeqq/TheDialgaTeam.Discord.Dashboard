<?php

namespace App\Handler;

use App\TheDialgaTeam\Discord\NancyGateway;
use Psr\Container\ContainerInterface;
use Zend\Expressive\Template\TemplateRendererInterface;

class HomeHandlerFactory
{
    public function __invoke(ContainerInterface $container)
    {
        $renderer = $container->get(TemplateRendererInterface::class);
        $nancyGateway = $container->get(NancyGateway::class);

        return new HomeHandler($renderer, $nancyGateway);
    }
}