<?php

namespace App\Core\Config;

use App\Core\Config\Enums\ServiceConfigTypeKeys;
use App\Core\Config\Exceptions\NotInstantiableClassException;
use App\Core\Config\Interfaces\IConfigLoader;
use App\Core\DependencyInjection\Container\ContainerBuilder;
use App\Core\DependencyInjection\ServiceDefinition;
use App\Core\FileSystem\Interfaces\IClassNameScanner;

class ConfigLoader implements IConfigLoader
{
    public function __construct(
        protected IClassNameScanner $classNameFinder,
    )
    {
    }

    public function loadFromArray(array $configs, ContainerBuilder $container): void
    {
        $default = $configs['_default'] ?? [];
        $instanceOf = $configs['_instanceof'] ?? [];

        foreach ($configs as $id => $config) {
            if ($id === '_instanceof' || $id === '_default') {
                continue;
            }

            if (isset($config[ServiceConfigTypeKeys::RESOURCE])) {
                $config[ServiceConfigTypeKeys::NAMESPACE] ??= $id;
                $this->loadFromResource($config, $container, $default, $instanceOf);
                continue;
            }

            if (isset($config[ServiceConfigTypeKeys::ALIAS]) || (is_string($config) && str_starts_with($config, '@'))) {
                $container->setAlias($id, $config[ServiceConfigTypeKeys::ALIAS] ?? substr($config, 1));
                continue;
            }

            if (is_string($config)) {
                $container->setServiceDefinition($id, $this->createServiceDefinition(
                    [ServiceConfigTypeKeys::CLASSNAME => $config], $default, $instanceOf)
                );
                continue;
            }

            $config[ServiceConfigTypeKeys::CLASSNAME] ??= $id;
            $container->setServiceDefinition($id, $this->createServiceDefinition($config, $default, $instanceOf));
        }
    }

    protected function loadFromResource(array $config, ContainerBuilder $container, array $default = [], array $instanceOf = []): void
    {
        $classes = $this->classNameFinder->scan(
            $config[ServiceConfigTypeKeys::NAMESPACE],
            $config[ServiceConfigTypeKeys::RESOURCE],
            $config[ServiceConfigTypeKeys::EXCLUDE] ?? []
        );

        foreach ($classes as $class) {
            try {
                $config[ServiceConfigTypeKeys::CLASSNAME] = $class;
                $container->setServiceDefinition(
                    $class, $this->createServiceDefinition($config, $default, $instanceOf)
                );
            } catch (NotInstantiableClassException) {
            }
        }
    }

    /**
     * @throws \ReflectionException
     * @throws NotInstantiableClassException
     */
    protected function createServiceDefinition(array $config, array $default = [], array $instanceOf = []): ServiceDefinition
    {
        if (!class_exists($config[ServiceConfigTypeKeys::CLASSNAME])) {
            throw new \InvalidArgumentException('Class "' . $config[ServiceConfigTypeKeys::CLASSNAME] . '" does not exist');
        }

        $ref = new \ReflectionClass($config[ServiceConfigTypeKeys::CLASSNAME]);

        if (!$ref->isInstantiable()) {
            throw new NotInstantiableClassException('Class "' . $config[ServiceConfigTypeKeys::CLASSNAME] . '" is not instantiable.');
        }

        $instances = array_filter($instanceOf, function ($class) use ($ref) {
            return $ref->isSubclassOf($class);
        }, ARRAY_FILTER_USE_KEY);

        $class = $config[ServiceConfigTypeKeys::CLASSNAME];
        $parameters = $this->mergeParameters($config, $default, $instances);

        return (new ServiceDefinition())
            ->setClass($class)
            ->setArguments($parameters[ServiceConfigTypeKeys::ARGS])
            ->setTags($parameters[ServiceConfigTypeKeys::TAGS])
            ->addMethodCalls($parameters[ServiceConfigTypeKeys::CALLS])
            ->setFactory($parameters[ServiceConfigTypeKeys::FACTORY])
            ->setSingleton($parameters[ServiceConfigTypeKeys::SINGLETON])
            ->setAutoInjecting($parameters[ServiceConfigTypeKeys::AUTO_INJECTING])
            ->setAutoTagging($parameters[ServiceConfigTypeKeys::AUTO_TAGGING])
        ;
    }

    protected function mergeParameters(array $config, array $default = [], array $instanceOf = []): array
    {
        $serviceDefinitionDefault = new ServiceDefinition();

        $parameters = [
            ServiceConfigTypeKeys::ARGS => $serviceDefinitionDefault->getArguments(),
            ServiceConfigTypeKeys::TAGS => $serviceDefinitionDefault->getTags(),
            ServiceConfigTypeKeys::CALLS => $serviceDefinitionDefault->getMethodCalls(),
            ServiceConfigTypeKeys::FACTORY => $serviceDefinitionDefault->getFactory(),
            ServiceConfigTypeKeys::SINGLETON => $serviceDefinitionDefault->isSingleton(),
            ServiceConfigTypeKeys::AUTO_INJECTING => $serviceDefinitionDefault->hasAutoInjecting(),
            ServiceConfigTypeKeys::AUTO_TAGGING => $serviceDefinitionDefault->hasAutoTagging(),
        ];

        foreach ($parameters as $key => $value) {
            foreach ([$default, ...$instanceOf, $config] as $source) {
                if (isset($source[$key])) {
                    if (is_array($value)) {
                        $parameters[$key] = array_merge($parameters[$key], $source[$key]);
                    } else {
                        $parameters[$key] = $source[$key];
                    }
                }
            }
        }

        return $parameters;
    }
}