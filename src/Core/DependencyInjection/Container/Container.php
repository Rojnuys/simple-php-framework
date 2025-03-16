<?php

namespace App\Core\DependencyInjection\Container;

use App\Core\DependencyInjection\Container\Exceptions\NotFoundException;
use App\Core\DependencyInjection\Container\Interfaces\IContainer;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\ContainerInterface;
use Psr\Container\NotFoundExceptionInterface;

abstract class Container implements IContainer
{
    /**
     * @var array<string, object>
     */
    protected array $services = [];
    /**
     * @var array<string, string>
     */
    protected array $aliases = [];
    /**
     * @var array<string, string>
     */
    protected array $methodMap = [];

    public function __construct(
        protected ContainerInterface $parameterContainer,
    )
    {
    }

    protected function createService(string $id): object
    {
        if (!isset($this->methodMap[$id])) {
            throw new NotFoundException("Service '{$id}' not found");
        }

        return $this->{$this->methodMap[$id]}($this);
    }

    /**
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function get(string $id): object
    {
        if (isset($this->services[$id])) {
            return $this->services[$id];
        }

        if (isset($this->aliases[$id]))
        {
            $id = $this->aliases[$id];
            if (isset($this->services[$id])) {
                return $this->services[$id];
            }
        }

        return $this->createService($id);
    }

    public function has(string $id): bool
    {
        return isset($this->services[$id]) || isset($this->aliases[$id]) || isset($this->methodMap[$id]);
    }

    public function setService(string $id, object $service): static
    {
        $this->services[$id] = $service;
        return $this;
    }

    /**
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function getParameter(string $id): mixed
    {
        return $this->parameterContainer->get($id);
    }

    public function hasParameter(string $id): bool
    {
        return $this->parameterContainer->has($id);
    }
}