<?php

namespace App\Core\Core\Events;

class ControllerEvent
{
    public function __construct(
        protected string $controllerName,
        protected string $actionName,
        protected array $arguments = []
    )
    {
    }

    public function getControllerName(): string
    {
        return $this->controllerName;
    }

    public function getActionName(): string
    {
        return $this->actionName;
    }

    public function getArguments(): array
    {
        return $this->arguments;
    }

    public function setArguments(array $arguments): void
    {
        $this->arguments = $arguments;
    }
}