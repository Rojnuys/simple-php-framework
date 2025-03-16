<?php

namespace App\Core\Core\Interfaces;

use App\Core\DependencyInjection\Container\Interfaces\IContainer;
use App\Core\DependencyInjection\Container\Interfaces\IPreBuildModifier;

interface IModule
{
    public function run(IContainer $container): void;
    public function stop(IContainer $container): void;
    public function getConfigurator(): ?IModuleConfigurator;
    public function getPreBuildModifier(): ?IPreBuildModifier;
}