<?php

namespace App\Handler\BaseForm;

use App\TheDialgaTeam\Discord\NancyGateway;
use Interop\Container\ContainerInterface;
use Zend\Expressive\Helper\ServerUrlHelper;
use Zend\Expressive\Helper\UrlHelper;
use Zend\Expressive\Template\TemplateRendererInterface;
use Zend\ServiceManager\Factory\FactoryInterface;

class BaseFormHandlerFactory implements FactoryInterface
{
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null)
    {
        if (class_exists($requestedName)) {
            return new $requestedName([
                'templateRenderer' => $container->get(TemplateRendererInterface::class),
                'nancyGateway' => $container->get(NancyGateway::class),
                'serverUrlHelper' => $container->get(ServerUrlHelper::class),
                'urlHelper' => $container->get(UrlHelper::class)
            ]);
        }
    }
}