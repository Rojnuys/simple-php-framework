<?php

namespace App\Core\DependencyInjection\Interfaces;

use App\Core\DependencyInjection\ValueObjects\ServiceConfig;

interface IServiceConfigFactory
{
    public function create(string $serviceName, mixed $config): ServiceConfig;
}