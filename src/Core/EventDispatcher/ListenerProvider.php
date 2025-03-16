<?php

namespace App\Core\EventDispatcher;

use App\Core\EventDispatcher\Interfaces\IListenerProvider;

class ListenerProvider implements IListenerProvider
{
    /**
     * @var array<string, array<int, callable>>
     */
    protected array $listeners = [];

    public function getListenersForEvent(object $event): iterable
    {
        return $this->listeners[$event::class] ?? [];
    }

    public function setListener(string $eventName, callable $listener, int $priority = 0): void
    {
        $this->listeners[$eventName][$priority] = $listener;
        ksort($this->listeners[$eventName]);
    }
}