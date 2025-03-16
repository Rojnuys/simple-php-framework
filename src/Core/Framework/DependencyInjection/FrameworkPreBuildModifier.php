<?php

namespace App\Core\Framework\DependencyInjection;

use App\Core\DependencyInjection\Container\ContainerBuilder;
use App\Core\DependencyInjection\Container\Exceptions\ContainerException;
use App\Core\DependencyInjection\Container\Interfaces\IPreBuildModifier;
use App\Core\Framework\Attributes\Route;
use App\Core\Framework\Interfaces\IController;
use App\Core\Http\HttpRequest;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;

class FrameworkPreBuildModifier implements IPreBuildModifier
{
    protected array $endpoints = [];

    /**
     * @throws \ReflectionException
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function modify(ContainerBuilder $container): void
    {
        foreach ($container->getServiceDefinitionsByTag('framework.controller') as $serviceDefinition) {
            $ref = new \ReflectionClass($serviceDefinition->getClass());

            if (!$ref->isSubclassOf(IController::class)) {
                throw new ContainerException("Class '{$serviceDefinition->getClass()}' must implement '" . IController::class . "' to be a controller");
            }

            try {
                $this->getEndpointsFromController($ref);
                foreach ($ref->getMethods() as $refMethod) {
                    $this->resolveMethodArguments($refMethod, $container);
                }
            } catch (\InvalidArgumentException $e) {
                throw new ContainerException('Controller \'' . $serviceDefinition->getClass() . '\'. ' . $e->getMessage());
            }
        }

        $this->dump($container->getParameter('core.cached_routes_file'));
    }

    protected function dump(string $path): void
    {
        file_put_contents($path, '<?php return ' . var_export($this->endpoints, true) . ';');
    }

    protected function resolveMethodArguments(\ReflectionMethod $refMethod, ContainerBuilder $container): void
    {
        $methodRouteRefAttributes = $refMethod->getAttributes(Route::class);

        foreach ($methodRouteRefAttributes as $methodRouteRefAttribute) {
            $methodRouteAttribute = $methodRouteRefAttribute->newInstance();
            preg_match_all('#<(.*?)>#', $methodRouteAttribute->getPath(), $parameters);

            foreach ($refMethod->getParameters() as $refParam) {
                if (in_array($refParam->getName(), $parameters[1])) {
                    continue;
                }

                if (!$refParam->getType()->isBuiltin()) {
                    if ($container->hasServiceDefinition($refParam->getType()->getName())) {
                        continue;
                    }
                    if ($refParam->getType()->getName() === HttpRequest::class) {
                        continue;
                    }
                }

                try {
                    $reason = '';
                    $container->getServiceDefinitionByAbstraction($refParam->getType()->getName());
                } catch (NotFoundExceptionInterface) {
                    $reason = "Service '{$refParam->getType()->getName()}' not found";
                } catch (ContainerExceptionInterface $e) {
                    $reason = $e->getMessage();
                }

                throw new \InvalidArgumentException("Method '{$refMethod->getName()}' parameter '{$refParam->getType()->getName()}' can't be resolved. " . $reason);
            }
        }
    }

    protected function getEndpointsFromController(\ReflectionClass $ref): array
    {
        $endpoints = [];

        $classRouteAttributes = $ref->getAttributes(Route::class);

        foreach ($ref->getMethods() as $refMethod) {
            $methodRouteAttributes = $refMethod->getAttributes(Route::class);

            foreach ($methodRouteAttributes as $methodRoute) {
                $methodRouteHasMethods = isset($methodRoute->getArguments()[1]) || isset($methodRoute->getArguments()['methods']);
                $methodRoute = $methodRoute->newInstance();

                if (count($classRouteAttributes) !== 0) {
                    foreach ($classRouteAttributes as $classRoute) {
                        $classRoute = $classRoute->newInstance();

                        $pattern = $classRoute->getPath() . $methodRoute->getPath();
                        $methods = $methodRouteHasMethods ? $methodRoute->getMethods() : $classRoute->getMethods();
                        $this->addEndpoint($pattern, $methods, $ref->getName(), $refMethod->getName());
                    }
                } else {
                    $this->addEndpoint($methodRoute->getPath(), $methodRoute->getMethods(), $ref->getName(), $refMethod->getName());
                }
            }
        }

        return $endpoints;
    }

    protected function addEndpoint(string $path, array $methods, string $controller, string $action): void
    {
        $path = trim($path, '/');

        if (substr_count($path, '<') !== substr_count($path, '>')) {
            throw new \InvalidArgumentException("Path '{$path}' incorrect. Symbols '<' and '>' can be used only in pair");
        }

        preg_match_all('#<(.*?)>#', $path, $matches);

        foreach ($matches[1] as $match) {
            if (preg_match('#^(?:(?:int|string):)?[a-zA-Z_]+[a-zA-Z0-9_]*$#', $match) !== 1) {
                throw new \InvalidArgumentException("Parameter '{$match}' invalid");
            }
        }

        if (isset($this->endpoints[$path])) {
            $conflicts = [];

            foreach ($this->endpoints[$path] as $endpoint) {
                $conflict = [];
                foreach ($endpoint['methods'] as $method) {
                    if (in_array($method, $methods)) {
                        $conflict['methods'][] = $method;
                    }
                    if (isset($conflict['methods'])) {
                        $conflict['controller'] = $endpoint['controller'];
                        $conflict['action'] = $endpoint['action'];
                        $conflicts[] = $conflict;
                    }
                }
            }

            if (count($conflicts) !== 0) {
                foreach ($conflicts as &$conflict) {
                    $conflict['methods'] = '(' . join(', ', $conflict['methods']) . ')';
                    $conflict = "\t- " . $conflict['methods'] . ' ' . $conflict['controller'] . ' ' . $conflict['action'];
                }
                throw new \InvalidArgumentException("Route '{$path}' conflicted with: \n" . join("\n", $conflicts));
            }
        }

        $this->endpoints[$path][] = ['methods' => $methods, 'controller' => $controller, 'action' => $action];
    }
}