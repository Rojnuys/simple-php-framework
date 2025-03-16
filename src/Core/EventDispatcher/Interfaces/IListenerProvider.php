<?php

namespace App\Core\EventDispatcher\Interfaces;

use Psr\EventDispatcher\ListenerProviderInterface;

interface IListenerProvider extends ListenerProviderInterface
{
    public function getListenersForEvent(object $event) : iterable;
    public function setListener(string $eventName, callable $listener, int $priority = 0): void;
}