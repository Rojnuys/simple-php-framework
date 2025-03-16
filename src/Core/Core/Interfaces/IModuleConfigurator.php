<?php

namespace App\Core\Core\Interfaces;

use App\Core\DependencyInjection\Container\ContainerBuilder;

interface IModuleConfigurator
{
    public function configure(ContainerBuilder $container): void;
}