<?php

namespace App\TheDialgaTeam\Discord;

use Psr\Container\ContainerInterface;

class NancyGatewayFactory
{
    public function __invoke(ContainerInterface $container)
    {
        $config = $container->get('config');
        $config = $config['nancy_gateway'];

        return new NancyGateway($config);
    }
}