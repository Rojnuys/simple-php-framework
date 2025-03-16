<?php

namespace App\Core\EventDispatcher\Attributes;

use Attribute;

#[Attribute(Attribute::TARGET_METHOD)]
class EventListener
{
    public function __construct(protected string $eventName, protected int $priority = 0)
    {
    }

    public function getEventName(): string
    {
        return $this->eventName;
    }

    public function getPriority(): int
    {
        return $this->priority;
    }
}