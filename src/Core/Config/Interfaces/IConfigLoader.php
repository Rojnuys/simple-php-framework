<?php

namespace App\Core\Config\Interfaces;

use App\Core\DependencyInjection\Container\ContainerBuilder;

interface IConfigLoader
{
    public function loadFromArray(array $configs, ContainerBuilder $container): void;
}