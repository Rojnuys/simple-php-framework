<?php

namespace App\Core\DependencyInjection\Container;

use App\Core\DependencyInjection\Container\Exceptions\ContainerException;
use App\Core\DependencyInjection\Container\Exceptions\NotFoundException;
use App\Core\DependencyInjection\ParamContainer\Exceptions\NotFoundParamException;
use App\Core\DependencyInjection\ValueObjects\ServiceConfig;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\ContainerInterface;
use Psr\Container\NotFoundExceptionInterface;

class Container implements ContainerInterface
{
    protected array $services = [];
    protected array $tags = [];

    /**
     * @var array<string, ServiceConfig>
     */
    protected array $serviceConfigs = [];

    public function __construct(
        protected ContainerInterface $paramContainer,
        array                        $serviceConfigs = [],
    )
    {
        foreach ($serviceConfigs as $serviceName => $serviceConfig) {
            $this->addServiceConfig($serviceName, $serviceConfig);
        }

        $this->updateTags();
    }

    protected function addServiceConfig(string $serviceName, ServiceConfig $serviceConfig): void
    {
        $this->serviceConfigs[$serviceName] = $serviceConfig;
    }

    protected function updateTags(): void
    {
        foreach ($this->serviceConfigs as $serviceName => $serviceConfig) {
            foreach ($serviceConfig->getTags() as $tag) {
                $this->tags[$tag] = isset($this->tags[$tag])
                    ? [$serviceName, ...$this->tags[$tag]]
                    : [$serviceName];
            }
        }
    }

    public function get(string $id): mixed
    {
        try {
            return $this->paramContainer->get($id);
        } catch (NotFoundParamException) {
            try {
                return $this->services[$id] ?? $this->createService($id);
            } catch (\ReflectionException $e) {
                throw new ContainerException($e->getMessage());
            }
        }
    }

    public function has(string $id): bool
    {
        try {
            $this->get($id);
        } catch (ContainerExceptionInterface) {
            return false;
        }

        return true;
    }

    /**
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     * @throws \ReflectionException
     */
    protected function createService(string $serviceName): mixed
    {
        $serviceConfig = $this->serviceConfigs[$serviceName] ?? throw new NotFoundException("Service $serviceName not found");

        if (count($serviceConfig->getClassNames()) > 1) {
            $matches = '';
            foreach ($serviceConfig->getClassNames() as $className) {
                $matches .= "\t- {$className}\n";
            }
            throw new ContainerException("There are several possible matches for this service {$serviceName}:\n {$matches}");
        }

        $className = $serviceConfig->getClassNames()[0];

        if ($serviceName !== $className) {
            return $this->get($className);
        }

        if (!class_exists($className)) {
            throw new ContainerException("Class {$className} does not exist");
        }

        $ref = new \ReflectionClass($className);

        if (!$ref->isInstantiable()) {
            throw new ContainerException("Class {$className} is not instantiable");
        }

        $service = $ref->newInstanceArgs($this->getConstructorParams($ref, $serviceConfig));

        foreach ($serviceConfig->getCalls() as $call) {
            $call($service, $this);
        }

        if ($serviceConfig->isShared()) {
            $this->services[$serviceName] = $service;
        }

        return $service;
    }

    /**
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    protected function getConstructorParams(\ReflectionClass $ref, ServiceConfig $serviceConfig): array
    {
        $constructorParams = [];

        if ($serviceConfig->isLocked()) {
            throw new ContainerException("Service {$serviceConfig->getName()} has a cyclical dependence");
        }
        $serviceConfig->lock();

        if ($ref->getConstructor() !== null) {
            $refParams = $ref->getConstructor()->getParameters();

            foreach ($refParams as $param) {
                if (isset($serviceConfig->getArguments()[$param->getName()])) {
                    $constructorParams[$param->getName()] = $this->getServiceArgument(
                        $serviceConfig->getArguments()[$param->getName()]
                    );
                    continue;
                }

                if ($param->isDefaultValueAvailable()) {
                    continue;
                }

                throw new ContainerException("Parameter {$param->getName()} is required but not set");
            }
        }

        return $constructorParams;
    }

    /**
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    protected function getServiceArgument(mixed $argument): mixed
    {
        if (!is_string($argument) || $argument === '') {
            return $argument;
        }

        if ($argument[0] === '@') {
            return $this->get(substr($argument, 1));
        }

        if ($argument[0] === '$') {
            return array_map(
                fn($serviceName) => $this->get($serviceName),
                $this->tags[substr($argument, 1)] ?? []
            );
        }

        if ($this->isParameter($argument)) {
            return $this->paramContainer->get(substr($argument, 1, -1));
        }

        return $argument;
    }

    protected function isParameter(string $value): bool
    {
        if (strlen($value) < 3) {
            return false;
        }

        return $value[0] === '%' && $value[-1] === '%';
    }
}