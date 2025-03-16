<?php

namespace App\Core\DependencyInjection;

class ServiceDefinition
{
    protected ?string $class = null;
    protected array $arguments = [];
    protected array $tags = [];
    protected array $methodCalls = [];
    protected array|null $factory = null;
    protected bool $singleton = true;
    protected bool $autoInjecting = false;
    protected bool $autoTagging = false;
    protected bool $locked = false;

    public function __construct()
    {
    }

    public function setClass(?string $class): static
    {
        if ($class === '') {
            throw new \InvalidArgumentException('Class name must be a non-empty string');
        }

        $this->class = $class;
        return $this;
    }

    public function getClass(): ?string
    {
        return $this->class;
    }

    public function setArgument(string $key, mixed $argument): static
    {
        if ($key === '') {
            throw new \InvalidArgumentException('Argument name must be a non-empty string');
        }

        $this->arguments[$key] = $argument;
        return $this;
    }

    public function setArguments(array $arguments): static
    {
        foreach ($arguments as $key => $argument) {
            $this->setArgument($key, $argument);
        }
        return $this;
    }

    public function getArguments(): array
    {
        return $this->arguments;
    }

    public function addTag(string $tag, array $arguments = []): static
    {
        if ($tag === '') {
            throw new \InvalidArgumentException('Tag name must be a non-empty string');
        }

        $this->tags[$tag][] = $arguments;
        return $this;
    }

    public function addTags(array $tags): static
    {
        foreach ($tags as $tag => $arguments) {
            $this->addTag($tag, $arguments);
        }
        return $this;
    }

    public function setTags(array $tags): static
    {
        foreach ($tags as $tag => $arguments) {
            $this->addTag($tag, $arguments);
        }
        return $this;
    }

    public function hasTag(string $tag): bool
    {
        return isset($this->tags[$tag]);
    }

    public function getTag(string $tag): mixed
    {
        return $this->tags[$tag] ?? [];
    }

    public function getTags(): array
    {
        return $this->tags;
    }

    public function addMethodCall(string $method, array $arguments = []): static
    {
        if ($method === '') {
            throw new \InvalidArgumentException('Method name must be a non-empty string');
        }

        $this->methodCalls[] = [$method, $arguments];
        return $this;
    }

    public function addMethodCalls(array $methodCalls): static
    {
        foreach ($methodCalls as $method => $arguments) {
            $this->addMethodCall($method, $arguments);
        }
        return $this;
    }

    public function getMethodCalls(): array
    {
        return $this->methodCalls;
    }

    protected function isFactory(array $factory): bool
    {
        if (count($factory) !== 2) {
            return false;
        }

        if ((!is_null($factory[0]) && !is_string($factory[0]) || $factory[0] === '')) {
            return false;
        }

        if ($factory[1] === '' || !is_string($factory[1])) {
            return false;
        }

        return true;
    }

    public function setFactory(?array $factory): static
    {
        if (!is_null($factory) && !$this->isFactory($factory)) {
            throw new \InvalidArgumentException(
                'Factory must be an array containing null and a method name or a class name and a method name'
            );
        }

        $this->factory = $factory;
        return $this;
    }

    public function hasFactory(): bool
    {
        return $this->factory !== null;
    }

    public function getFactory(): ?array
    {
        return $this->factory;
    }

    public function setSingleton(bool $singleton): static
    {
        $this->singleton = $singleton;
        return $this;
    }

    public function isSingleton(): bool
    {
        return $this->singleton;
    }

    public function setAutoInjecting(bool $autoInjecting): static
    {
        $this->autoInjecting = $autoInjecting;
        return $this;
    }

    public function hasAutoInjecting(): bool
    {
        return $this->autoInjecting;
    }

    public function setAutoTagging(bool $autoTagging): static
    {
        $this->autoTagging = $autoTagging;
        return $this;
    }

    public function hasAutoTagging(): bool
    {
        return $this->autoTagging;
    }

    public function isLocked(): bool
    {
        return $this->locked;
    }

    public function lock(): static
    {
        $this->locked = true;
        return $this;
    }

    public function unlock(): static
    {
        $this->locked = false;
        return $this;
    }
}