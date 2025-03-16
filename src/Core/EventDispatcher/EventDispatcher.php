<?php

namespace App\Core\EventDispatcher;

use App\Core\EventDispatcher\Interfaces\IListenerProvider;
use Psr\EventDispatcher\EventDispatcherInterface;
use Psr\EventDispatcher\StoppableEventInterface;

class EventDispatcher implements EventDispatcherInterface
{
    public function __construct(protected IListenerProvider $listenerProvider)
    {
    }

    public function dispatch(object $event): object
    {
        if ($event instanceof StoppableEventInterface && $event->isPropagationStopped()) {
            return $event;
        }

        foreach ($this->listenerProvider->getListenersForEvent($event) as $listener) {
            call_user_func($listener, $event);
        }

        return $event;
    }
}