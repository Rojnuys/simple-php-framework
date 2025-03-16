<?php

namespace App\Core\DependencyInjection\Container\Interfaces;

use App\Core\DependencyInjection\Container\ContainerBuilder;

interface IContainerDumper
{
    public function dump(string $dumpFilePath, ContainerBuilder $container): void;
}