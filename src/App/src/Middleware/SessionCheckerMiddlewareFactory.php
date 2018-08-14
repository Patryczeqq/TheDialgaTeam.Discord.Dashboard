<?php

namespace App\Middleware;

use App\TheDialgaTeam\Discord\NancyGateway;
use Psr\Container\ContainerInterface;
use Zend\Expressive\Helper\ServerUrlHelper;
use Zend\Expressive\Helper\UrlHelper;
use Zend\Expressive\Template\TemplateRendererInterface;

class SessionCheckerMiddlewareFactory
{
    public function __invoke(ContainerInterface $container)
    {
        $templateRenderer = $container->get(TemplateRendererInterface::class);
        $nancyGateway = $container->get(NancyGateway::class);
        $serverUrlHelper = $container->get(ServerUrlHelper::class);
        $urlHelper = $container->get(UrlHelper::class);

        return new SessionCheckerMiddleware($templateRenderer, $nancyGateway, $serverUrlHelper, $urlHelper);
    }
}