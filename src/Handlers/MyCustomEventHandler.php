<?php

namespace App\Handlers;

use App\Core\EventDispatcher\Attributes\EventListener;
use App\Core\EventDispatcher\Interfaces\IEventListener;
use App\Events\MyCustomEvent;

class MyCustomEventHandler implements IEventListener
{
    #[EventListener(MyCustomEvent::class)]
    public function onMyCustomEvent(MyCustomEvent $event): void
    {
        $event->setResult($event->getA() + $event->getB());
    }

    #[EventListener(MyCustomEvent::class, -1)]
    public function onMyCustomEvent2(MyCustomEvent $event): void
    {
        $event->setResult(10);
    }
}