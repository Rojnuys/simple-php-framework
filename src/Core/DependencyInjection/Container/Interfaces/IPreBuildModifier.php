<?php

namespace App\Core\DependencyInjection\Container\Interfaces;

use App\Core\DependencyInjection\Container\ContainerBuilder;

interface IPreBuildModifier
{
    public function modify(ContainerBuilder $container): void;
}