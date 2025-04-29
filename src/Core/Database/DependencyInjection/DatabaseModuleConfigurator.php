<?php

namespace App\Core\Database\DependencyInjection;

use App\Core\Core\Interfaces\IModuleConfigurator;
use App\Core\Database\DatabaseAR;
use App\Core\DependencyInjection\Container\ContainerBuilder;
use App\Core\DependencyInjection\Container\Exceptions\ContainerException;
use App\Core\DependencyInjection\ServiceDefinition;

class DatabaseModuleConfigurator implements IModuleConfigurator
{
    /**
     * @throws \ReflectionException
     * @throws ContainerException
     */
    public function configure(ContainerBuilder $container): void
    {
        $container->setServiceDefinition(
            DatabaseAR::class,
            (new ServiceDefinition())
                ->setClass(DatabaseAR::class)
                ->setArguments([
                    'database' => '%db.database%',
                    'username' => '%db.username%',
                    'password' => '%db.password%',
                    'host' => '%db.host%',
                    'dbDriver' => '%db.driver%',
                    'prefix' => '%db.prefix%',
                    'charset' => '%db.charset%',
                    'collation' => '%db.collation%',
                ])
        );
    }
}