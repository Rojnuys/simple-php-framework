<?php

namespace App\Core\DependencyInjection\Container;

use App\Core\DependencyInjection\Container\Exceptions\ContainerException;
use App\Core\DependencyInjection\Container\Exceptions\NotFoundException;
use App\Core\DependencyInjection\Container\Interfaces\IBuilder;
use App\Core\DependencyInjection\Container\Interfaces\IContainer;
use App\Core\DependencyInjection\Container\Interfaces\IContainerDumper;
use App\Core\DependencyInjection\Container\Interfaces\IParamContainerBuilder;
use App\Core\DependencyInjection\Container\Interfaces\IPreBuildModifier;
use App\Core\DependencyInjection\ServiceDefinition;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;

class ContainerBuilder extends Container implements IBuilder
{
    /**
     * @var array<string, ServiceDefinition>
     */
    protected array $serviceDefinitions = [];
    /**
     * @var array<string, ServiceDefinition[]>
     */
    protected array $serviceDefinitionsByAbstraction = [];
    /**
     * @var IPreBuildModifier[]
     */
    protected array $preBuildModifiers = [];
    /**
     * @var array<string, array>
     */
    protected array $autoTagging = [];

    public function __construct(
        protected IParamContainerBuilder $parameterContainerBuilder,
        protected IContainerDumper       $containerDumper,
        protected bool                   $addSinglyRealizedAbstraction = false
    )
    {
        parent::__construct($parameterContainerBuilder);
    }

    public function setAlias(string $alias, string $serviceId): static
    {
        $this->aliases[$alias] = $serviceId;
        return $this;
    }

    public function getAliases(): array
    {
        return $this->aliases;
    }

    public function addPreBuildModifier(IPreBuildModifier $modifier): static
    {
        $this->preBuildModifiers[] = $modifier;
        return $this;
    }

    public function addAutoTagging(string $class, array $tags): static
    {
        $this->autoTagging[$class] = array_merge($this->autoTagging[$class] ?? [], $tags);
        return $this;
    }

    /**
     * @throws \ReflectionException
     */
    protected function autoTagging(ServiceDefinition $serviceDefinition): void
    {
        $ref = new \ReflectionClass($serviceDefinition->getClass());

        foreach ($this->autoTagging as $class => $tags) {
            if ($ref->isSubclassOf($class)) {
                $serviceDefinition->addTags($tags);
            }
        }
    }

    /**
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function getServiceDefinitionByAbstraction(string $id): ServiceDefinition
    {
        if (!isset($this->serviceDefinitionsByAbstraction[$id])) {
            throw new NotFoundException("Abstraction '{$id}' realizations not found");
        }

        $serviceDefinitions = $this->serviceDefinitionsByAbstraction[$id];

        if (count($serviceDefinitions) > 1) {
            $classes = array_map(fn($serviceDefinition) => $serviceDefinition->getClass(), $serviceDefinitions);
            throw new ContainerException(
                "Abstraction '{$id}' has more than one implementation: \n " . implode("\n ", $classes)
            );
        }

        return $serviceDefinitions[array_key_first($serviceDefinitions)];
    }

    protected function setServiceDefinitionByAbstraction(string $abstraction, ServiceDefinition $serviceDefinition): static
    {
        $this->serviceDefinitionsByAbstraction[$abstraction][$serviceDefinition->getClass()] = $serviceDefinition;
        return $this;
    }

    protected function addImplementation(\ReflectionClass $ref, ServiceDefinition $serviceDefinition): void
    {
        foreach ($ref->getInterfaces() as $interface) {
            $this->setServiceDefinitionByAbstraction($interface->getName(), $serviceDefinition);
        }

        $parentClass = $ref->getParentClass();
        while ($parentClass !== false) {
            if ($parentClass->isAbstract()) {
                $this->setServiceDefinitionByAbstraction($parentClass->getName(), $serviceDefinition);
            }
            $parentClass = $parentClass->getParentClass();
        }
    }

    /**
     * @throws NotFoundExceptionInterface
     */
    public function getServiceDefinition(string $id): ServiceDefinition
    {
        return $this->serviceDefinitions[$id] ?? throw new NotFoundException("Service definition '{$id}' not found");
    }

    public function getServiceDefinitions(): array
    {
        return $this->serviceDefinitions;
    }

    public function hasServiceDefinition(string $id): bool
    {
        return isset($this->serviceDefinitions[$id]);
    }

    /**
     * @throws \ReflectionException
     * @throws ContainerException
     */
    public function setServiceDefinition(string $id, ServiceDefinition $serviceDefinition): static
    {
        if (!class_exists($serviceDefinition->getClass())) {
            throw new ContainerException("Service '{$id}' class '{$serviceDefinition->getClass()}' does not exist");
        }

        $ref = new \ReflectionClass($serviceDefinition->getClass());

        if (!$ref->isInstantiable()) {
            throw new ContainerException("Class {$serviceDefinition->getClass()} is not instantiable");
        }

        $this->addImplementation($ref, $serviceDefinition);

        if ($serviceDefinition->hasAutoTagging()) {
            $this->autoTagging($serviceDefinition);
        }

        $this->serviceDefinitions[$id] = $serviceDefinition;

        return $this;
    }

    /**
     * @throws \ReflectionException
     * @throws ContainerException
     */
    public function setServiceDefinitions(array $serviceDefinitions): static
    {
        foreach ($serviceDefinitions as $id => $serviceDefinition) {
            $this->setServiceDefinition($id, $serviceDefinition);
        }

        return $this;
    }

    public function deleteServiceDefinition(string $id): static
    {
        if (isset($this->serviceDefinitions[$id])) {
            unset($this->serviceDefinitions[$id]);
        }

        return $this;
    }

    public function setParameter(string $id, mixed $value): static
    {
        $this->parameterContainerBuilder->setParameter($id, $value);
        return $this;
    }

    public function setParameters(array $parameters): static
    {
        foreach ($parameters as $id => $value) {
            $this->setParameter($id, $value);
        }

        return $this;
    }

    public function getParameters(): array
    {
        return $this->parameterContainerBuilder->getParameters();
    }

    public function getServiceDefinitionsByTag(string $tag): array
    {
        return array_filter(
            $this->serviceDefinitions, fn($serviceDefinition) => $serviceDefinition->hasTag($tag)
        );
    }

    public function getServiceIdsByTag(string $tag): array
    {
        return array_keys($this->getServiceDefinitionsByTag($tag));
    }

    /**
     * @throws ContainerException
     */
    protected function buildAlias(string $alias, array $trace = []): string
    {
        if (isset($trace[$alias])) {
            throw new ContainerException(
                'Alias \'' . array_key_first($trace) . '\' has a circular reference with trace: '
                . join(', ', array_keys($trace))
            );
        }
        $trace[$alias] = true;

        if (isset($this->serviceDefinitions[$alias])) {
            return $alias;
        }

        if (isset($this->aliases[$alias])) {
            return $this->buildAlias($this->aliases[$alias], $trace);
        }

        throw new ContainerException(
            "Service '{$alias}' does not exist with trace: " . join(', ', array_keys($trace))
        );
    }

    protected function reset(): void
    {
        $this->services = [];
        $this->aliases = [];
        $this->serviceDefinitions = [];
        $this->serviceDefinitionsByAbstraction = [];
        $this->preBuildModifiers = [];
        $this->autoTagging = [];
    }

    /**
     * @throws \ReflectionException
     * @throws NotFoundExceptionInterface
     * @throws ContainerExceptionInterface
     */
    public function build(?string $dumpFilePath = null): IContainer
    {
        if ($this->addSinglyRealizedAbstraction) {
            foreach ($this->serviceDefinitionsByAbstraction as $abstraction => $serviceDefinitions) {
                if (
                    count($serviceDefinitions) === 1 &&
                    !isset($this->serviceDefinitions[$abstraction]) &&
                    !isset($this->aliases[$abstraction])
                ) {
                    $this->serviceDefinitions[$abstraction] = (new ServiceDefinition())
                        ->setClass($serviceDefinitions[array_key_first($serviceDefinitions)]->getClass())
                        ->setAutoInjecting(true)
                        ->setAutoTagging(true)
                    ;
                }
            }
        }

        foreach ($this->preBuildModifiers as $modifier) {
            $modifier->modify($this);
        }

        foreach ($this->aliases as $alias => $id) {
            $this->aliases[$alias] = $this->buildAlias($id);
        }

        $this->parameterContainer = $this->parameterContainerBuilder->build();

        foreach ($this->serviceDefinitions as $id => $serviceDefinition) {
            $this->get($id);
        }

        $dumpFilePath = $dumpFilePath ?? __DIR__ . '/Cache/CachedContainer.php';
        $this->containerDumper->dump($dumpFilePath, $this);

        require_once $dumpFilePath;

        $builtContainer = (new \ReflectionClass('CachedContainer'))->newInstance();

        $this->parameterContainer = $this->parameterContainerBuilder;
        $this->reset();

        return $builtContainer;
    }

    /**
     * @throws \ReflectionException
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    protected function createService(string $id): object
    {
        if (isset($this->serviceDefinitions[$id])) {
            $serviceDefinition = $this->serviceDefinitions[$id];
        } else {
            try {
                $this->getServiceDefinitionByAbstraction($id);
            } catch (NotFoundException) {
            }
            throw new NotFoundException("Service '{$id}' not found");
        }

        if ($serviceDefinition->isLocked()) {
            throw new ContainerException("Service '{$id}' has circular reference");
        }
        $serviceDefinition->lock();

        try {
            $service = $serviceDefinition->hasFactory()
                ? $this->createServiceByFactory($serviceDefinition)
                : $this->createServiceByReflection($serviceDefinition);

            foreach ($serviceDefinition->getMethodCalls() as $methodCall) {
                $refMethod = new \ReflectionMethod($service, $methodCall[0]);
                $service->{$methodCall[0]}(...$this->getMethodArguments($refMethod, $methodCall[1]));
            }
        } catch (ContainerExceptionInterface $e) {
            throw new ContainerException("Service '{$id}'. " . $e->getMessage());
        }

        if ($serviceDefinition->isSingleton()) {
            $this->services[$id] = $service;
        }

        $serviceDefinition->unlock();

        return $service;
    }

    /**
     * @throws \ReflectionException
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    protected function createServiceByFactory(ServiceDefinition $serviceDefinition): object
    {
        $factory = $serviceDefinition->getFactory();

        if ($factory[0] === null) {
            $factory[0] = $serviceDefinition->getClass();
        }

        if (str_starts_with($factory[0], '@')) {
            $factoryService = $this->get(substr($factory[0], 1));
        }

        $refMethod = new \ReflectionMethod($factoryService ?? $factory[0], $factory[1]);

        try {
            $arguments = $this->getMethodArguments($refMethod, $serviceDefinition->getArguments());
        } catch (ContainerExceptionInterface $e) {
            throw new ContainerException("Factory '{$factory[0]}'. " . $e->getMessage());
        }

        return call_user_func_array([$factoryService ?? $factory[0], $factory[1]], $arguments);
    }

    /**
     * @throws \ReflectionException
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    protected function createServiceByReflection(ServiceDefinition $serviceDefinition): object
    {
        $ref = new \ReflectionClass($serviceDefinition->getClass());
        return $ref->newInstanceArgs($this->getConstructorArguments($ref, $serviceDefinition));
    }

    /**
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    protected function getMethodArguments(\ReflectionMethod $refMethod, array $arguments): array
    {
        $methodArguments = [];

        foreach ($refMethod->getParameters() as $refParam) {
            if (array_key_exists($refParam->getName(), $arguments)) {
                $methodArguments[$refParam->getName()] = $this->prepareArgument(
                    $arguments[$refParam->getName()]
                );
                continue;
            }

            if ($refParam->isDefaultValueAvailable()) {
                continue;
            }

            throw new ContainerException(
                "Parameter '{$refParam->getName()}' in method '{$refMethod->getName()}' is required but not provided"
            );
        }

        return $methodArguments;
    }

    /**
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    protected function getConstructorArguments(\ReflectionClass $ref, ServiceDefinition $serviceDefinition): array
    {
        $constructorArguments = [];

        foreach ($ref->getConstructor()?->getParameters() ?? [] as $refParam) {
            if (array_key_exists($refParam->getName(), $serviceDefinition->getArguments())) {
                $constructorArguments[$refParam->getName()] = $this->prepareArgument(
                    $serviceDefinition->getArguments()[$refParam->getName()]
                );
                continue;
            }

            if ($serviceDefinition->hasAutoInjecting()) {
                if (!$refParam->getType()->isBuiltin()) {
                    $constructorArguments[$refParam->getName()] = $this->prepareArgument(
                        '@' . $refParam->getType()->getName()
                    );
                    $serviceDefinition->setArgument($refParam->getName(), '@' . $refParam->getType()->getName());
                    continue;
                }
            }

            if ($refParam->isDefaultValueAvailable()) {
                continue;
            }

            throw new ContainerException(
                "Parameter '{$refParam->getName()}' in class '{$serviceDefinition->getClass()}' is required but not provided"
            );
        }

        return $constructorArguments;
    }

    /**
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    protected function prepareArgument(mixed $argument): mixed
    {
        if (is_array($argument)) {
            foreach ($argument as $key => $value) {
                $argument[$key] = $this->prepareArgument($value);
            }
            return $argument;
        }

        if (!is_string($argument) || $argument === '') {
            return $argument;
        }

        return $this->prepareArgumentString($argument);
    }

    /**
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    protected function prepareArgumentString(string $argument): mixed
    {
        if (preg_match('/^%([^%]+)%$/', $argument, $matches)) {
            return $this->parameterContainer->get($matches[1]);
        }

        $argument = preg_replace_callback('/%%|%([^%]+)%/', function ($matches) use ($argument) {
            if ($matches[0] === '%%') {
                return '%';
            }

            $parameter = $this->parameterContainer->get($matches[1]);

            if (is_string($parameter) || is_numeric($parameter)) {
                return $parameter;
            }

            throw new ContainerException(
                "Service '{$argument}' argument's embedded parameter '{$matches[1]}' can't be converted to string"
            );
        }, $argument);

        if (str_starts_with($argument, '@@') || str_starts_with($argument, '$$')) {
            return substr($argument, 1);
        }

        if (str_starts_with($argument, '@')) {
            return $this->get(substr($argument, 1));
        }

        if (str_starts_with($argument, '$')) {
            return array_map(fn($id) => $this->get($id), $this->getServiceIdsByTag(substr($argument, 1)));
        }

        return $argument;
    }
}