<?php

namespace App\Core\DependencyInjection;

use App\Core\Config\Interfaces\IConfigLoader;
use App\Core\Config\Interfaces\IConfigParser;
use App\Core\Config\Interfaces\IConfigStructureValidator;
use App\Core\Core\Interfaces\IModuleConfigurator;
use App\Core\Core\Module;
use App\Core\DependencyInjection\DependencyInjection\DependencyInjectionModuleConfigurator;

class DependencyInjectionModule extends Module
{
    public function __construct(
        protected string $configFilePath,
        protected IConfigParser $configParser,
        protected IConfigStructureValidator $configStructureValidator,
        protected IConfigLoader $configLoader,
    )
    {
    }

    public function getConfigurator(): ?IModuleConfigurator
    {
        return new DependencyInjectionModuleConfigurator(
            $this->configFilePath, $this->configParser, $this->configStructureValidator, $this->configLoader
        );
    }
}