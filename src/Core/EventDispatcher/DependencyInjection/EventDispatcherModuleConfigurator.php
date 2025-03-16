<?php

namespace App\Core\EventDispatcher\DependencyInjection;

use App\Core\DependencyInjection\Container\ContainerBuilder;
use App\Core\DependencyInjection\ServiceDefinition;
use App\Core\EventDispatcher\EventDispatcher;
use App\Core\EventDispatcher\Interfaces\IEventListener;
use App\Core\EventDispatcher\ListenerProvider;
use App\Core\Core\Interfaces\IModuleConfigurator;

class EventDispatcherModuleConfigurator implements IModuleConfigurator
{
    public function configure(ContainerBuilder $container): void
    {
        $container->addAutoTagging(IEventListener::class, ['event_dispatcher.event_listener' => []]);

        $container->setServiceDefinition(
            ListenerProvider::class,
            (new ServiceDefinition())
                ->setClass(ListenerProvider::class)
        );
        $container->setServiceDefinition(
            EventDispatcher::class,
            (new ServiceDefinition())
                ->setClass(EventDispatcher::class)
                ->setAutoInjecting(true)
        );
    }
}