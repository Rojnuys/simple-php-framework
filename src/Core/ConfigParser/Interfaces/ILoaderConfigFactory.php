<?php

namespace App\Core\ConfigParser\Interfaces;

use App\Core\DependencyInjection\ServiceConfigLoader\ValueObjects\LoaderConfig;

interface ILoaderConfigFactory
{
    public function create(string $namespace, mixed $config): LoaderConfig;
}