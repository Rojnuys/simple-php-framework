<?php

namespace App\Core\DependencyInjection\ValueObjects;

class ServiceConfig
{
    public function __construct(
        protected string $name,
        protected array $classNames = [],
        protected array $arguments = [],
        protected array $tags = [],
        /**
         * @var callable[]
         */
        protected array $calls = [],
        protected bool $shared = true,
        protected bool $locked = false,
    )
    {
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getClassNames(): array
    {
        return $this->classNames;
    }

    public function getArguments(): array
    {
        return $this->arguments;
    }

    public function getTags(): array
    {
        return $this->tags;
    }

    /**
     * @return callable[]
     */
    public function getCalls(): array
    {
        return $this->calls;
    }

    public function isShared(): bool
    {
        return $this->shared;
    }

    public function isLocked(): bool
    {
        return $this->locked;
    }

    public function lock(): void
    {
        $this->locked = true;
    }
}