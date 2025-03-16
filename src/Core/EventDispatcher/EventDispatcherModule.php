<?php

namespace App\Core\EventDispatcher;

use App\Core\DependencyInjection\Container\Interfaces\IPreBuildModifier;
use App\Core\EventDispatcher\DependencyInjection\EventDispatcherModuleConfigurator;
use App\Core\EventDispatcher\DependencyInjection\EventDispatcherPreBuildModifier;
use App\Core\Core\Interfaces\IModuleConfigurator;
use App\Core\Core\Module;

class EventDispatcherModule extends Module
{
    public function getConfigurator(): ?IModuleConfigurator
    {
        return new EventDispatcherModuleConfigurator();
    }

    public function getPreBuildModifier(): ?IPreBuildModifier
    {
        return new EventDispatcherPreBuildModifier();
    }
}