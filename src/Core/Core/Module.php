<?php

namespace App\Core\Core;

use App\Core\DependencyInjection\Container\Interfaces\IContainer;
use App\Core\DependencyInjection\Container\Interfaces\IPreBuildModifier;
use App\Core\Core\Interfaces\IModule;
use App\Core\Core\Interfaces\IModuleConfigurator;

abstract class Module implements IModule
{
    public function run(IContainer $container): void
    {
    }

    public function stop(IContainer $container): void
    {
    }

    public function getConfigurator(): ?IModuleConfigurator
    {
        return null;
    }

    public function getPreBuildModifier(): ?IPreBuildModifier
    {
        return null;
    }
}