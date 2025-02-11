<?php

namespace App\Core\ConfigParser;

use App\Core\ConfigParser\Enums\LoaderConfigType;
use App\Core\ConfigParser\Interfaces\ILoaderConfigParser;
use App\Core\ConfigParser\Interfaces\IServiceConfigParser;
use App\Core\ConfigParser\Interfaces\ILoaderConfigFactory;
use App\Core\DependencyInjection\Enums\ServiceConfigType;
use App\Core\DependencyInjection\Interfaces\IServiceConfigFactory;
use App\Core\DependencyInjection\ServiceConfigLoader\ValueObjects\LoaderConfig;
use App\Core\DependencyInjection\ValueObjects\ServiceConfig;

class ConfigParser implements IServiceConfigParser, ILoaderConfigParser
{
    protected array $serviceConfigs = [];
    protected array $loaderConfigs = [];

    public function __construct(
        protected IServiceConfigFactory $serviceConfigFactory,
        protected ILoaderConfigFactory  $loaderConfigFactory,
        array                           $configs = [],
    )
    {
        $this->handle($configs);
    }

    protected function handle(array $configs): void
    {
        foreach ($configs as $serviceName => $serviceConfig) {
            if (is_string($serviceConfig)) {
                $this->serviceConfigs[$serviceName] = $this->serviceConfigFactory->create($serviceName, [
                    ServiceConfigType::CLASSNAME => $serviceConfig
                ]);
                continue;
            }

            if (is_array($serviceConfig)) {
                foreach (LoaderConfigType::getAllTypes() as $type) {
                    if (in_array($type, array_keys($serviceConfig))) {
                        $this->loaderConfigs[$serviceName] = $this->loaderConfigFactory->create($serviceName, $serviceConfig);
                        continue 2;
                    }
                }

                $this->serviceConfigs[$serviceName] = $this->serviceConfigFactory->create($serviceName, $serviceConfig);;
                continue;
            }

            throw new \InvalidArgumentException("Service {$serviceName} invalid. Must be a string or array");
        }
    }

    /**
     * @return ServiceConfig[]
     */
    public function getServiceConfigs(): array
    {
        return $this->serviceConfigs;
    }

    /**
     * @return LoaderConfig[]
     */
    public function getLoaderConfigs(): array
    {
        return $this->loaderConfigs;
    }
}