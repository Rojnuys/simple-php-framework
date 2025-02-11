<?php

namespace App\Core\DependencyInjection\ServiceConfigLoader\Interfaces;

use App\Core\DependencyInjection\ValueObjects\ServiceConfig;

interface IServiceConfigLoader
{
    /**
     * @throws IServiceConfigLoaderException
     * @return array<string, ServiceConfig>
     */
    public function load(): array;
}