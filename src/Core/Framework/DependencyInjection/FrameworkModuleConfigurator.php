<?php

namespace App\Core\Framework\DependencyInjection;

use App\Core\DependencyInjection\Container\ContainerBuilder;
use App\Core\DependencyInjection\ServiceDefinition;
use App\Core\Framework\Interfaces\IController;
use App\Core\Framework\Router;
use App\Core\Core\Interfaces\IModuleConfigurator;

class FrameworkModuleConfigurator implements IModuleConfigurator
{
    public function configure(ContainerBuilder $container): void
    {
        $container->addAutoTagging(IController::class, ['framework.controller' => []]);

        $container->setServiceDefinition(
            Router::class,
            (new ServiceDefinition())
                ->setClass(Router::class)
                ->setArgument('path', '%core.cached_routes_file%')
        );
    }
}