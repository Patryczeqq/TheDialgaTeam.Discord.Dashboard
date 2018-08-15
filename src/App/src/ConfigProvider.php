<?php

declare(strict_types=1);

namespace App;

use App\Form\Element\Csrf;
use App\Handler\BaseFormHandlerFactory;
use App\Handler\DiscordAppAuthenticationHandler;
use App\Handler\HomeHandler;
use App\Handler\LogoutHandler;
use App\TheDialgaTeam\Discord\NancyGateway;
use App\TheDialgaTeam\Discord\NancyGatewayFactory;
use Zend\ServiceManager\Factory\InvokableFactory;

/**
 * The configuration provider for the App module
 *
 * @see https://docs.zendframework.com/zend-component-installer/
 */
class ConfigProvider
{
    /**
     * Returns the configuration array
     *
     * To add a bit of a structure, each section is defined in a separate
     * method which returns an array with its configuration.
     *
     */
    public function __invoke(): array
    {
        return [
            'dependencies' => $this->getDependencies(),
            'templates' => $this->getTemplates(),
            'nancy_gateway' => $this->getNancyGateway(),
        ];
    }

    /**
     * Returns the container dependencies
     */
    public function getDependencies(): array
    {
        return [
            'factories' => [
                // Form
                Csrf::class => InvokableFactory::class,

                // Handler
                HomeHandler::class => BaseFormHandlerFactory::class,
                DiscordAppAuthenticationHandler::class => BaseFormHandlerFactory::class,
                LogoutHandler::class => BaseFormHandlerFactory::class,

                // Nancy Gateway
                NancyGateway::class => NancyGatewayFactory::class,
            ],
        ];
    }

    /**
     * Returns the templates configuration
     */
    public function getTemplates(): array
    {
        return [
            'paths' => [
                'app' => [__DIR__ . '/../templates/app'],
                'error' => [__DIR__ . '/../templates/error'],
                'layout' => [__DIR__ . '/../templates/layout'],
            ],
        ];
    }

    public function getNancyGateway(): array
    {
        return [
            'url' => 'http://127.0.0.1',
            'port' => '5000',
        ];
    }
}
