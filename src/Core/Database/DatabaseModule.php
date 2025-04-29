<?php

namespace App\Core\Database;

use App\Core\Core\Interfaces\IModuleConfigurator;
use App\Core\Core\Module;
use App\Core\Database\DependencyInjection\DatabaseModuleConfigurator;
use App\Core\DependencyInjection\Container\Interfaces\IContainer;
use Illuminate\Database\Capsule\Manager;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;

class DatabaseModule extends Module
{
    /**
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function run(IContainer $container): void
    {
        $container->get(DatabaseAR::class);
    }

    public function stop(IContainer $container): void
    {
        Manager::connection()->disconnect();
    }

    public function getConfigurator(): ?IModuleConfigurator
    {
        return new DatabaseModuleConfigurator();
    }
}