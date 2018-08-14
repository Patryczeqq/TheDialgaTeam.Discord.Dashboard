<?php

namespace App\Handler;

use App\TheDialgaTeam\Discord\NancyGateway;
use Psr\Container\ContainerInterface;
use Zend\Expressive\Helper\UrlHelper;
use Zend\Expressive\Template\TemplateRendererInterface;

class DiscordAppAuthenticationHandlerFactory
{
    public function __invoke(ContainerInterface $container)
    {
        $templateRenderer = $container->get(TemplateRendererInterface::class);
        $nancyGateway = $container->get(NancyGateway::class);
        $urlHelper = $container->get(UrlHelper::class);

        return new DiscordAppAuthenticationHandler($templateRenderer, $nancyGateway, $urlHelper);
    }
}