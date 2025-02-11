<?php

namespace App\Core\DependencyInjection\Factories;

use App\Core\DependencyInjection\Enums\ServiceConfigType;
use App\Core\DependencyInjection\Interfaces\IServiceConfigFactory;
use App\Core\DependencyInjection\ValueObjects\ServiceConfig;

class ServiceConfigFactory implements IServiceConfigFactory
{
    public function create(string $serviceName, mixed $config): ServiceConfig
    {
        if (!isset($config[ServiceConfigType::CLASSNAME])) {
            $config[ServiceConfigType::CLASSNAME] = $serviceName;
        }

        return new ServiceConfig(
            $serviceName,
            (array) $config[ServiceConfigType::CLASSNAME],
            $config[ServiceConfigType::ARGS] ?? [],
            $config[ServiceConfigType::TAGS] ?? [],
            $config[ServiceConfigType::CALLS] ?? [],
            $config[ServiceConfigType::SHARED] ?? true
        );
    }
}