<?php

namespace App\Core\ConfigParser\Interfaces;

use App\Core\DependencyInjection\ValueObjects\ServiceConfig;

interface IServiceConfigParser
{
    /**
     * @return ServiceConfig[]
     */
    public function getServiceConfigs(): array;
}