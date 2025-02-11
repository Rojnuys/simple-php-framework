<?php

namespace App\Core\DependencyInjection\ServiceConfigLoader;

use App\Core\DependencyInjection\Enums\ServiceConfigType;
use App\Core\DependencyInjection\Interfaces\IServiceConfigFactory;
use App\Core\DependencyInjection\ServiceConfigLoader\Exceptions\NotFoundPredefinedConfigException;
use App\Core\DependencyInjection\ServiceConfigLoader\Exceptions\NotInstantiableClassException;
use App\Core\DependencyInjection\ServiceConfigLoader\Exceptions\ServiceConfigLoaderException;
use App\Core\DependencyInjection\ServiceConfigLoader\Interfaces\IPhpFileFinder;
use App\Core\DependencyInjection\ServiceConfigLoader\Interfaces\IServiceConfigLoader;
use App\Core\DependencyInjection\ServiceConfigLoader\ValueObjects\LoaderConfig;
use App\Core\DependencyInjection\ValueObjects\ServiceConfig;

class ServiceConfigLoader implements IServiceConfigLoader
{
    /**
     * @var array<string, LoaderConfig>
     */
    protected array $configs = [];

    /**
     * @var array<string, ServiceConfig>
     */
    protected array $predefinedServiceConfigs = [];
    protected array $interfaceImplementations = [];

    public function __construct(
        protected IPhpFileFinder $phpFileFinder,
        protected IServiceConfigFactory $serviceConfigFactory,
        array $configs = [],
        array $predefinedServiceConfigs = [],
    )
    {
        foreach ($configs as $namespace => $loaderConfig) {
            $this->addConfig($namespace, $loaderConfig);
        }

        foreach ($predefinedServiceConfigs as $serviceName => $predefinedServiceConfig) {
            $this->addPredefinedServiceConfig($serviceName, $predefinedServiceConfig);
        }
    }

    protected function addConfig(string $namespace, LoaderConfig $loaderConfig): void
    {
        $this->configs[$namespace] = $loaderConfig;
    }

    protected function addPredefinedServiceConfig(string $serviceName, ServiceConfig $serviceConfig): void
    {
        $this->predefinedServiceConfigs[$serviceName] = $serviceConfig;
    }

    protected function addInterfaceImplementation(string $interfaceName, string $className): void
    {
        $this->interfaceImplementations[$interfaceName] = isset($this->interfaceImplementations[$interfaceName])
            ? [...$this->interfaceImplementations[$interfaceName], $className]
            : [$className]
        ;
    }

    protected function getNamespacePath(string $namespace): string
    {
        $loader = spl_autoload_functions()[0][0];
        $namespaces = $loader->getPrefixesPsr4();

        if (!isset($namespaces[$namespace])) {
            throw new ServiceConfigLoaderException("Namespace {$namespace} does not exist");
        }

        return $namespaces[$namespace][0] . DIRECTORY_SEPARATOR;
    }

    protected function getClassNames(LoaderConfig $config): array
    {
        $namespacePath = $this->getNamespacePath($config->getNamespace());
        $files = $this->phpFileFinder->find(
            $namespacePath . $config->getResource(),
            array_map(fn($exclude) => $namespacePath . $exclude, $config->getExcludes())
        );

        $classNames = [];

        foreach ($files as $fileInfo) {
            $classesBefore = get_declared_classes();
            include_once $fileInfo->getRealPath();
            $classesAfter = get_declared_classes();

            $classNames = array_merge($classNames, array_diff($classesAfter, $classesBefore));
        }

        return $classNames;
    }

    /**
     * @throws \ReflectionException
     */
    public function load(): array
    {
        $serviceConfigs = [];

        foreach ($this->configs as $loaderConfig) {
            $classNames = $this->getClassNames($loaderConfig);

            foreach ($classNames as $className) {
                try {
                    $serviceConfigs[$className] = $this->createClassServiceConfig($className, $loaderConfig->isAutowired());
                } catch (NotInstantiableClassException) {
                }
            }
        }

        $serviceConfigs = array_merge($serviceConfigs, $this->getInterfaceConfigs());

        return array_merge($this->predefinedServiceConfigs, $serviceConfigs);
    }

    /**
     * @throws \ReflectionException
     * @throws NotInstantiableClassException
     */
    protected function createClassServiceConfig(string $className, bool $isAutowired = false): ServiceConfig
    {
        $ref = new \ReflectionClass($className);

        if (!$ref->isInstantiable()) {
            throw new NotInstantiableClassException('Class "' . $className . '" is not instantiable.');
        }

        $serviceConfig = $this->createServiceConfig(
            $className,
            $this->getConstructorParams($ref, $className, $isAutowired)
        );

        if ($isAutowired) {
            foreach ($ref->getInterfaceNames() as $interfaceName) {
                $this->addInterfaceImplementation($interfaceName, $className);
            }
        }

        return $serviceConfig;
    }

    protected function getConstructorParams(\ReflectionClass $ref, string $className, bool $isAutowired = false): array
    {
        $constructorParams = [];

        foreach ($ref->getConstructor()->getParameters() as $refParam) {
            try {
                $constructorParams[$refParam->getName()] = $this->getPredefinedServiceConfigArgument($className, $refParam->getName());
                continue;
            } catch (NotFoundPredefinedConfigException) {
            }

            if ($refParam->isDefaultValueAvailable()) {
                continue;
            }

            if ($refParam->getType()->isBuiltin()) {
                throw new ServiceConfigLoaderException("$className::{$refParam->getName()} must be set or have default value");
            }

            if ($isAutowired) {
                $constructorParams[$refParam->getName()] = '@' . $refParam->getType()->getName();
                continue;
            }

            throw new ServiceConfigLoaderException("$className::{$refParam->getName()} must be set or autowired");
        }

        return $constructorParams;
    }

    protected function createServiceConfig(string $serviceName, array $constructorParams): ServiceConfig
    {
        try {
            $predefinedServiceConfig = $this->getPredefinedServiceConfig($serviceName);
            $serviceConfig = $this->serviceConfigFactory->create($serviceName, [
                ServiceConfigType::CLASSNAME => $predefinedServiceConfig->getClassNames(),
                ServiceConfigType::TAGS => $predefinedServiceConfig->getTags(),
                ServiceConfigType::CALLS => $predefinedServiceConfig->getCalls(),
                ServiceConfigType::SHARED => $predefinedServiceConfig->isShared(),
                ServiceConfigType::ARGS => $constructorParams
            ]);
        } catch (NotFoundPredefinedConfigException) {
            $serviceConfig = $this->serviceConfigFactory->create($serviceName, [
                ServiceConfigType::ARGS => $constructorParams
            ]);
        }

        return $serviceConfig;
    }

    /**
     * @throws NotFoundPredefinedConfigException
     */
    protected function getPredefinedServiceConfigArgument(string $serviceName, string $argumentName): mixed
    {
        $predefinedServiceConfig = $this->getPredefinedServiceConfig($serviceName);

        if (!isset($predefinedServiceConfig->getArguments()[$argumentName])) {
            throw new NotFoundPredefinedConfigException('Predefined service "' . $serviceName . '" does not have argument "' . $argumentName . '"');
        }

        return $predefinedServiceConfig->getArguments()[$argumentName];
    }

    /**
     * @throws NotFoundPredefinedConfigException
     */
    protected function getPredefinedServiceConfig(string $serviceName): ServiceConfig
    {
        if (!isset($this->predefinedServiceConfigs[$serviceName])) {
            throw new NotFoundPredefinedConfigException('Predefined service "' . $serviceName . '" does not exist.');
        }

        return $this->predefinedServiceConfigs[$serviceName];
    }

    /**
     * @return array<string, ServiceConfig>
     */
    protected function getInterfaceConfigs(): array
    {
        $interfaceConfigs = [];

        foreach ($this->interfaceImplementations as $interfaceName => $interfaceImplementations) {
            try {
                $predefinedServiceConfig = $this->getPredefinedServiceConfig($interfaceName);
                $interfaceConfigs[$interfaceName] = $this->serviceConfigFactory->create($interfaceName, [
                    ServiceConfigType::CLASSNAME => $predefinedServiceConfig->getClassNames()[0],
                ]);
                continue;
            } catch (NotFoundPredefinedConfigException) {
            }

            $interfaceConfigs[$interfaceName] = $this->serviceConfigFactory->create($interfaceName, [
                ServiceConfigType::CLASSNAME => $interfaceImplementations,
            ]);
        }

        return $interfaceConfigs;
    }
}