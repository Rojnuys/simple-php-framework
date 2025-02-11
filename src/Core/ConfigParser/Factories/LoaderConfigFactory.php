<?php

namespace App\Core\ConfigParser\Factories;

use App\Core\ConfigParser\Enums\LoaderConfigType;
use App\Core\ConfigParser\Interfaces\ILoaderConfigFactory;
use App\Core\DependencyInjection\ServiceConfigLoader\ValueObjects\LoaderConfig;

class LoaderConfigFactory implements ILoaderConfigFactory
{

    public function create(string $namespace, mixed $config): LoaderConfig
    {
        if (!isset($config[LoaderConfigType::RESOURCE])) {
            throw new \InvalidArgumentException("Parameter '" . LoaderConfigType::RESOURCE . "' is required");
        }

        return new LoaderConfig(
            $namespace,
            $config[LoaderConfigType::RESOURCE],
            $config[LoaderConfigType::AUTOWIRE] ?? false,
            $config[LoaderConfigType::AUTOCONFIGURE] ?? false,
        $config[LoaderConfigType::EXCLUDE] ?? [],
        );
    }
}