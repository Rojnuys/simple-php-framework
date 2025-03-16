<?php

namespace App\Core\Core;

use App\Core\Core\Events\ControllerEvent;
use App\Core\Core\Events\ExceptionEvent;
use App\Core\Core\Events\RequestEvent;
use App\Core\Core\Events\ResponseEvent;
use App\Core\DependencyInjection\Container\ContainerBuilder;
use App\Core\DependencyInjection\Container\ContainerDumper;
use App\Core\DependencyInjection\Container\Interfaces\IContainer;
use App\Core\DependencyInjection\Container\ParamContainerBuilder;
use App\Core\Framework\Exceptions\RouteNotFoundException;
use App\Core\Framework\Router;
use App\Core\Http\HttpRequest;
use App\Core\Http\TextResponse;
use App\Core\Core\Interfaces\IModule;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;
use Psr\EventDispatcher\EventDispatcherInterface;
use Psr\Http\Message\ResponseInterface;

class Core
{
    protected ?IContainer $container = null;
    protected ?string $projectDirectoryPath = null;
    /**
     * @var IModule[]
     */
    protected array $modules = [];

    public function __construct()
    {
    }

    public function addModule(IModule $module): static
    {
        $this->modules[] = $module;
        return $this;
    }

    public function setProjectDirectoryPath(string $projectDirectoryPath): static
    {
        if (!file_exists($projectDirectoryPath)) {
            throw new \InvalidArgumentException("Project directory '$projectDirectoryPath' does not exist");
        }

        $this->projectDirectoryPath = $projectDirectoryPath;
        return $this;
    }

    public function getProjectDirectoryPath(): string
    {
        if ($this->projectDirectoryPath === null) {
            return dirname(__DIR__);
        }

        return $this->projectDirectoryPath;
    }

    public function getCacheDirectoryPath(): string
    {
        return $this->getProjectDirectoryPath() . DIRECTORY_SEPARATOR . 'var' . DIRECTORY_SEPARATOR . 'cache';
    }

    public function getCachedContainerFilePath(): string
    {
        return $this->getCacheDirectoryPath() . DIRECTORY_SEPARATOR . 'CachedContainer.php';
    }

    public function getCachedRoutesFilePath(): string
    {
        return $this->getCacheDirectoryPath() . DIRECTORY_SEPARATOR . 'routes.php';
    }

    /**
     * @throws \ReflectionException
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    protected function getActionArguments(string $controllerName, string $actionName, array $predefinedArguments): array
    {
        $arguments = [];

        $ref = new \ReflectionMethod($controllerName, $actionName);
        foreach ($ref->getParameters() as $refParam) {
            if (isset($predefinedArguments[$refParam->getName()])) {
                $arguments[$refParam->getName()] = $predefinedArguments[$refParam->getName()];
                continue;
            }

            if (!$refParam->getType()->isBuiltin()) {
                if ($this->container->has($refParam->getType()->getName())) {
                    $arguments[$refParam->getName()] = $this->container->get($refParam->getType()->getName());
                    continue;
                }
            }

            if ($refParam->isDefaultValueAvailable()) {
                continue;
            }

            throw new \InvalidArgumentException($controllerName . ' ' . $actionName . ' argument \'' . $refParam->getName() . '\' not found');
        }

        return $arguments;
    }

    protected function sendResponse(ResponseInterface $response): void
    {
        http_response_code($response->getStatusCode());

        foreach ($response->getHeaders() as $name => $values) {
            foreach ($values as $value) {
                header("$name: $value", false);
            }
        }

        echo $response->getBody();
    }

    protected function sendServerErrorResponse(): void
    {
        $this->sendResponse(new TextResponse('Server Error', 500));
    }

    protected function sendNotFoundResponse(): void
    {
        $this->sendResponse(new TextResponse('Not found', 404));
    }

    protected function runModules(): void
    {
        $lastModuleRunIndex = 0;

        try {
            for (; $lastModuleRunIndex < count($this->modules); $lastModuleRunIndex++) {
                $this->modules[$lastModuleRunIndex]->run($this->container);
            }
        } catch (\Throwable) {
            try {
                $this->stopModules($lastModuleRunIndex);
            } catch (\RuntimeException $e) {
            }
            throw new \RuntimeException("Error while running module with index '{$lastModuleRunIndex}'. " . $e->getMessage());
        }
    }

    protected function stopModules(?int $lastModuleRunIndex = null): void
    {
        $lastModuleStopIndex = 0;

        try {
            for (; $lastModuleStopIndex < $lastModuleRunIndex ?? count($this->modules); $lastModuleStopIndex++) {
                $this->modules[$lastModuleStopIndex]->stop($this->container);
            }
        } catch (\Throwable) {
            throw new \RuntimeException("Error while stopping module with index '{$lastModuleStopIndex}'");
        }
    }

    public function run(): void
    {
        try {
            $this->loadContainer();
        } catch (\Throwable) {
            $this->sendServerErrorResponse();
            exit();
        }

        try {
            $this->runModules();
        } catch (\RuntimeException) {
            $this->sendServerErrorResponse();
            exit();
        }

        try {
            $eventDispatcher = $this->container->get(EventDispatcherInterface::class);
            $router = $this->container->get(Router::class);
        } catch (\Throwable) {
            $this->sendServerErrorResponse();
            exit();
        }

        try {
            try {
                $requestEvent = new RequestEvent(new HttpRequest($_GET, $_POST, $_SERVER));
                $eventDispatcher->dispatch($requestEvent);
                $request = $requestEvent->getRequest();
                $this->container->setService(HttpRequest::class, $request);

                $route = $router->findRoute($request->getUri()->getPath(), $request->getMethod());
                $arguments = $this->getActionArguments($route['controller'], $route['action'], $route['arguments']);
                $controllerEvent = new ControllerEvent($route['controller'], $route['action'], $arguments);
                $eventDispatcher->dispatch($controllerEvent);

                ob_start();
                $response = $this->container
                    ->get($controllerEvent->getControllerName())
                    ->{$controllerEvent->getActionName()}(...$controllerEvent->getArguments());
                ob_end_clean();

                $responseEvent = new ResponseEvent($response);
                $eventDispatcher->dispatch($responseEvent);
                $this->sendResponse($responseEvent->getResponse());
            } catch (\Throwable $e) {
                $exceptionEvent = new ExceptionEvent($e);
                $eventDispatcher->dispatch($exceptionEvent);
                throw $e;
            }
        } catch (RouteNotFoundException) {
            $this->sendNotFoundResponse();
        } catch (\Throwable) {
            $this->sendServerErrorResponse();
        }

        try {
            $this->stopModules();
        } catch (\RuntimeException) {
        }
    }

    protected function loadCoreConfiguration(ContainerBuilder $container): void
    {
        $container->setParameter('core.cache_dir', $this->getCacheDirectoryPath());
        $container->setParameter('core.cached_routes_file', $this->getCachedRoutesFilePath());
    }

    protected function loadModulesConfiguration(ContainerBuilder $container): void
    {
        foreach ($this->modules as $module) {
            $module->getConfigurator()?->configure($container);
        }
    }

    protected function preBuildCore(ContainerBuilder $container): void
    {
    }

    protected function preBuildModules(ContainerBuilder $container): void
    {
        foreach ($this->modules as $module) {
            if ($module->getPreBuildModifier() !== null) {
                $container->addPreBuildModifier($module->getPreBuildModifier());
            }
        }
    }

    /**
     * @throws \ReflectionException
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    protected function buildContainer(): void
    {
        $container = new ContainerBuilder(
            new ParamContainerBuilder(),
            new ContainerDumper(),
            true
        );

        $this->loadCoreConfiguration($container);
        $this->loadModulesConfiguration($container);

        $this->preBuildCore($container);
        $this->preBuildModules($container);

        $this->container = $container->build($this->getCachedContainerFilePath());
    }

    /**
     * @throws \ReflectionException
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    protected function loadContainer(): void
    {
        if (file_exists($this->getCachedContainerFilePath())) {
            require_once $this->getCachedContainerFilePath();
            $this->container = (new \ReflectionClass('CachedContainer'))->newInstance();
            return;
        }

        $this->buildContainer();
    }
}