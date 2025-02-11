<?php

namespace App\Core\ConfigParser\Interfaces;

use App\Core\DependencyInjection\ServiceConfigLoader\ValueObjects\LoaderConfig;

interface ILoaderConfigParser
{
    /**
     * @return LoaderConfig[]
     */
    public function getLoaderConfigs(): array;
}