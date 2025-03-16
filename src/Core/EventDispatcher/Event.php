<?php

namespace App\Core\EventDispatcher;

use Psr\EventDispatcher\StoppableEventInterface;

abstract class Event implements StoppableEventInterface
{
    protected bool $propagationStopped = false;

    public function stopPropagation(bool $propagationStopped): void
    {
        $this->propagationStopped = $propagationStopped;
    }

    public function isPropagationStopped(): bool
    {
        return $this->propagationStopped;
    }
}