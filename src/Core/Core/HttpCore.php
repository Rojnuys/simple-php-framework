<?php

namespace App\Core\Core;

use App\Core\Core\Enums\CoreMode;
use App\Core\Core\Events\ControllerEvent;
use App\Core\Core\Events\ExceptionEvent;
use App\Core\Core\Events\RequestEvent;
use App\Core\Core\Events\ResponseEvent;
use App\Core\DependencyInjection\Container\ContainerBuilder;
use App\Core\Framework\Exceptions\RouteNotFoundException;
use App\Core\Framework\Router;
use App\Core\Http\HttpRequest;
use App\Core\Http\TextResponse;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;
use Psr\EventDispatcher\EventDispatcherInterface;
use Psr\Http\Message\ResponseInterface;

class HttpCore extends BaseCore
{
    protected string $cachedContainerClassName = 'HttpCachedContainer';

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

    public function run(): void
    {
        try {
            $this->loadContainer();
        } catch (\Throwable $e) {
            if ($this->mode === CoreMode::DEVELOPMENT) {
                throw $e;
            } else {
                $this->sendServerErrorResponse();
            }
            exit();
        }

        try {
            $this->runModules();
        } catch (\RuntimeException $e) {
            if ($this->mode === CoreMode::DEVELOPMENT) {
                throw $e;
            } else {
                $this->sendServerErrorResponse();
            }
            exit();
        }

        try {
            $eventDispatcher = $this->container->get(EventDispatcherInterface::class);
            $router = $this->container->get(Router::class);
        } catch (\Throwable $e) {
            if ($this->mode === CoreMode::DEVELOPMENT) {
                throw $e;
            } else {
                $this->sendServerErrorResponse();
            }
            exit();
        }

        try {
            try {
                $requestEvent = new RequestEvent(new HttpRequest($_GET, $_POST, $_SERVER));
                $eventDispatcher->dispatch($requestEvent);
                $request = $requestEvent->getRequest();
                $this->container->setService(HttpRequest::class, $request);

                $requestMethod = $request->getMethod();
                if ($requestMethod === 'POST') {
                    if ( in_array($request->post('_method'), ['PUT', 'PATCH', 'DELETE']) ) {
                        $requestMethod = $request->post('_method');
                    }
                }

                $route = $router->findRoute($request->getUri()->getPath(), $requestMethod);
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
                if ($this->mode === CoreMode::DEVELOPMENT) {
                    throw $e;
                } else {
                    $exceptionEvent = new ExceptionEvent($e);
                    $eventDispatcher->dispatch($exceptionEvent);
                }
                throw $e;
            }
        } catch (RouteNotFoundException $e) {
            if ($this->mode === CoreMode::DEVELOPMENT) {
                throw $e;
            } else {
                $this->sendNotFoundResponse();
            }
        } catch (\Throwable $e) {
            if ($this->mode === CoreMode::DEVELOPMENT) {
                throw $e;
            } else {
                $this->sendServerErrorResponse();
            }
        }

        try {
            $this->stopModules();
        } catch (\RuntimeException $e) {
            if ($this->mode === CoreMode::DEVELOPMENT) {
                throw $e;
            }
        }
    }

    protected function loadCoreConfiguration(ContainerBuilder $container): void
    {
        parent::loadCoreConfiguration($container);
        $container->setParameter('core.cached_routes_file', $this->getCachedRoutesFilePath());
    }
}