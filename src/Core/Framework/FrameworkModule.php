<?php

namespace App\Core\Framework;

use App\Core\DependencyInjection\Container\Interfaces\IPreBuildModifier;
use App\Core\Framework\DependencyInjection\FrameworkModuleConfigurator;
use App\Core\Framework\DependencyInjection\FrameworkPreBuildModifier;
use App\Core\Core\Interfaces\IModuleConfigurator;
use App\Core\Core\Module;

class FrameworkModule extends Module
{
    public function getConfigurator(): ?IModuleConfigurator
    {
        return new FrameworkModuleConfigurator();
    }

    public function getPreBuildModifier(): ?IPreBuildModifier
    {
        return new FrameworkPreBuildModifier();
    }
}