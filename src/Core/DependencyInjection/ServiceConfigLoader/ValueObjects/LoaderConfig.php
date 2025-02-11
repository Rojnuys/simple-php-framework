<?php

namespace App\Core\DependencyInjection\ServiceConfigLoader\ValueObjects;

class LoaderConfig
{
    public function __construct(
        protected string $namespace,
        protected string $resource,
        protected bool $autowired = false,
        protected bool $autoconfigured = false,
        /**
         * @var string[]
         */
        protected array $excludes = [],
    )
    {
    }

    public function getNamespace(): string
    {
        return $this->namespace;
    }

    public function getResource(): string
    {
        return $this->resource;
    }

    public function isAutowired(): bool
    {
        return $this->autowired;
    }

    public function isAutoconfigured(): bool
    {
        return $this->autoconfigured;
    }

    /**
     * @return string[]
     */
    public function getExcludes(): array
    {
        return $this->excludes;
    }
}