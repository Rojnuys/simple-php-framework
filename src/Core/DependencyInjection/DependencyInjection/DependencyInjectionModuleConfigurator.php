<?php

namespace App\Core\DependencyInjection\DependencyInjection;

use App\Core\Config\Enums\ConfigTypeKeys;
use App\Core\Config\Exceptions\ConfigException;
use App\Core\Config\Interfaces\IConfigLoader;
use App\Core\Config\Interfaces\IConfigParser;
use App\Core\Config\Interfaces\IConfigStructureValidator;
use App\Core\Core\Interfaces\IModuleConfigurator;
use App\Core\DependencyInjection\Container\ContainerBuilder;

class DependencyInjectionModuleConfigurator implements IModuleConfigurator
{
    public function __construct(
        protected string $configFilePath,
        protected IConfigParser $configParser,
        protected IConfigStructureValidator $configStructureValidator,
        protected IConfigLoader $configLoader,
    )
    {
    }

    public function configure(ContainerBuilder $container): void
    {
        try {
            $config = $this->configParser->parse($this->configFilePath);
            $this->configStructureValidator->validate($config);

            if (isset($config[ConfigTypeKeys::SERVICES])) {
                $this->configLoader->loadFromArray($config[ConfigTypeKeys::SERVICES], $container);
            }

            if (isset($config[ConfigTypeKeys::PARAMETERS])) {
                foreach ($config[ConfigTypeKeys::PARAMETERS] as $key => $value) {
                    $container->setParameter($key, $value);
                }
            }
        } catch (ConfigException $e) {
            throw new ConfigException("Config file '{$this->configFilePath}': " . $e->getMessage());
        }
    }
}