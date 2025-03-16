<?php

namespace App\Core\Framework;

use App\Core\Framework\Exceptions\RouteNotFoundException;

class Router
{
    protected array $routes;

    public function __construct(string $path)
    {
        $this->routes = require $path;
    }

    /**
     * @throws RouteNotFoundException
     */
    public function findRoute(string $uri, string $method): array
    {
        $possibleRoutes = [];

        foreach ($this->routes as $path => $endpoints) {
            $path = str_replace(['\\<', '\\>'], ['<', '>'], preg_quote($path, '#'));

            $lengthWithoutParameters = strlen(preg_replace_callback('#<([^\/<>]+)>#', fn($matches) => '', $path));

            $argumentNames = [];
            $path = preg_replace_callback('#<([^\/<>]+)>#', function ($matches) use (&$argumentNames) {
                $parameterConfigs = explode('\\:', $matches[1]);
                $parameterName = $parameterConfigs[1] ?? $parameterConfigs[0];
                $parameterType = isset($parameterConfigs[1]) ? $parameterConfigs[0] : 'string';

                $argumentNames[] = $parameterName;
                return match ($parameterType) {
                    'int' => '(\d+)',
                    default => '([^\/]+)',
                };
            }, $path);

            if (preg_match('#^' . $path. '$#', trim($uri, '/'), $matches) === 1) {
                foreach ($endpoints as $endpoint) {
                    if (in_array($method, $endpoint['methods'])) {
                        array_shift($matches);
                        $arguments = array_combine($argumentNames, $matches);
                        $possibleRoutes[] = [
                            'arguments' => $arguments,
                            'controller' => $endpoint['controller'],
                            'action' => $endpoint['action'],
                            'lengthWithoutParameters' => $lengthWithoutParameters,
                        ];
                    }
                }
            }
        }

        if (empty($possibleRoutes)) {
            throw new RouteNotFoundException("Route '{$uri}' does not exist");
        }

        $maxLengthWithoutParameters = $possibleRoutes[0]['lengthWithoutParameters'];
        $index = 0;

        foreach ($possibleRoutes as $key => $route) {
            if ($maxLengthWithoutParameters < $route['lengthWithoutParameters']) {
                $maxLengthWithoutParameters = $route['lengthWithoutParameters'];
                $index = $key;
            }
        }

        return $possibleRoutes[$index];
    }
}