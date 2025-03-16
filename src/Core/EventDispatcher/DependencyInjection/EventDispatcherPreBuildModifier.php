<?php

namespace App\Core\EventDispatcher\DependencyInjection;

use App\Core\DependencyInjection\Container\ContainerBuilder;
use App\Core\DependencyInjection\Container\Exceptions\ContainerException;
use App\Core\DependencyInjection\Container\Interfaces\IPreBuildModifier;
use App\Core\DependencyInjection\ServiceDefinition;
use App\Core\EventDispatcher\Attributes\EventListener;
use App\Core\EventDispatcher\Interfaces\IEventListener;
use App\Core\EventDispatcher\Interfaces\IListenerProvider;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;
use ReflectionException;

class EventDispatcherPreBuildModifier implements IPreBuildModifier
{
    /**
     * @throws \ReflectionException
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function modify(ContainerBuilder $container): void
    {
        $listenerProvider = $container->getServiceDefinition(IListenerProvider::class);

        foreach ($container->getServiceDefinitionsByTag('event_dispatcher.event_listener') as $id => $serviceDefinition) {
            $ref = new \ReflectionClass($serviceDefinition->getClass());

            if (!$ref->isSubclassOf(IEventListener::class)) {
                $tags = $serviceDefinition->getTag('event_dispatcher.event_listener');
                $this->addListenersByTag($listenerProvider, $tags, $id, $serviceDefinition->getClass());
            } else {
                foreach ($ref->getMethods() as $refMethod) {
                    foreach ($refMethod->getAttributes(EventListener::class) as $refAttribute) {
                        $attribute = $refAttribute->newInstance();

                        $listenerProvider->addMethodCall('setListener', [
                            'eventName' => $attribute->getEventName(),
                            'listener' => ['@' . $id, $refMethod->getName()],
                            'priority' => $attribute->getPriority(),
                        ]);
                    }
                }
            }
        }
    }

    /**
     * @throws ContainerException
     */
    protected function addListenersByTag(ServiceDefinition $listenerProvider, array $tags, string $serviceId, string $serviceClass): void
    {
        if (count($tags[array_key_first($tags)]) === 0) {
            return;
        }

        if (array_key_first($tags[array_key_first($tags)]) !== null && is_array($tags[array_key_first($tags)][array_key_first($tags[array_key_first($tags)])])) {
            $tags = [...$tags[array_key_first($tags)]];
        }

        foreach ($tags as $tag) {
            if (
                isset($tag['eventName']) && is_string($tag['eventName']) &&
                isset($tag['method']) && is_string($tag['method']) &&
                ((isset($tag['priority']) && is_int($tag['priority'])) || !isset($tag['priority']))
            ) {
                try {
                    $refMethod = new \ReflectionMethod($serviceClass, $tag['method']);
                } catch (ReflectionException) {
                    throw new ContainerException("Service '{$serviceId}' tag 'event_dispatcher.event_listener' does not have method '{$tag['method']}'");
                }

                if (!$refMethod->isPublic()) {
                    throw new ContainerException("Service '{$serviceId}' tag 'event_dispatcher.event_listener' method '{$tag['method']}' isn't public");
                }


                try {
                    new \ReflectionClass($tag['eventName']);

                    if (
                        count($refMethod->getParameters()) !== 0 &&
                        (
                            count($refMethod->getParameters()) > 1 ||
                            $refMethod->getParameters()[0]->getType()->getName() !== $tag['eventName']
                        )
                    ) {
                        throw new ContainerException("Service '{$serviceId}' tag 'event_dispatcher.event_listener' method '{$tag['method']}' must have only event '{$tag['eventName']}' as a parameter");
                    }
                } catch (ReflectionException) {
                    throw new ContainerException("Service '{$serviceId}' tag 'event_dispatcher.event_listener' eventName '{$tag['eventName']}' must be a real class");
                }

                $listenerProvider->addMethodCall('setListener', [
                    'eventName' => $tag['eventName'],
                    'listener' => ['@' . $serviceId, $tag['method']],
                    'priority' => $tag['priority'] ?? 0,
                ]);
            } else {
                throw new ContainerException("Service '{$serviceId}' tag 'event_dispatcher.event_listener' must have the following keys: eventName (string), method (string) and can have priority (int)");
            }
        }
    }
}