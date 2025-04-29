<?php

namespace App\Core\DependencyInjection\Container;

use App\Core\DependencyInjection\Container\Exceptions\ContainerException;
use App\Core\DependencyInjection\Container\Interfaces\IParamContainerBuilder;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\ContainerInterface;
use Psr\Container\NotFoundExceptionInterface;

class ParamContainerBuilder extends ParamContainer implements IParamContainerBuilder
{
    public function __construct($idSeparator = self::DEFAULT_ID_SEPARATOR)
    {
        parent::__construct(idSeparator: $idSeparator);
    }

    public function getParameters(): array
    {
        return $this->parameters;
    }

    protected function expandKeys(mixed &$parameters): void
    {
        if (is_array($parameters)) {
            foreach ($parameters as $id => &$value) {
                $idParts = explode($this->idSeparator, $id);

                if (count($idParts) > 1) {
                    $newParameter[array_pop($idParts)] = $value;
                    while (count($idParts) > 1) {
                        $newParameter = [array_pop($idParts) => $newParameter];
                    }
                    $parameters[array_pop($idParts)] = $newParameter;
                    unset($parameters[$id]);
                }

                if (is_array($value)) {
                    $this->expandKeys($value);
                }
            }
        }
    }

    protected function setNested(array &$array, array $idParts, mixed $value): void
    {
        $idPart = array_shift($idParts);
        if (isset($array[$idPart]) && is_array($array[$idPart])) {
            $this->setNested($array[$idPart], $idParts, $value);
        } else {
            if ($idPart === null) {
                $array = $value;
            } else {
                $array[join($this->idSeparator, [$idPart, ...$idParts])] = $value;
                $this->expandKeys($array);
            }
        }
    }

    public function setParameter(string $id, mixed $value): static
    {
        $this->setNested($this->parameters, $this->getIdParts($id), $value);
        return $this;
    }

    public function setParameters(array $parameters): static
    {
        foreach ($parameters as $id => $value) {
            $this->setParameter($id, $value);
        }

        return $this;
    }

    /**
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    protected function buildEmbeddedParameters(string $value, array $trace = []): array|string|int|float|bool|null
    {
        if (preg_match('/^%([^%]+)%$/', $value, $matches)) {
            if (isset($trace[$matches[1]])) {
                throw new ContainerException("Parameter '{$matches[1]}' has a cyclical reference between: " . join(', ', array_keys($trace)));
            }

            $trace[$matches[1]] = true;
            $embeddedParameter = $this->get($matches[1]);

            if (is_string($embeddedParameter)) {
                return $this->buildEmbeddedParameters($embeddedParameter, $trace);
            }

            if (is_numeric($embeddedParameter) || is_bool($embeddedParameter) || is_null($embeddedParameter)) {
                return $embeddedParameter;
            }

            if (is_array($embeddedParameter)) {
                foreach ($embeddedParameter as $key => $value) {
                    if (is_string($value)) {
                        $embeddedParameter[$key] = $this->buildEmbeddedParameters($value, $trace);
                    }
                }
                return $embeddedParameter;
            }

            throw new ContainerException("Can't convert parameter type '" . gettype($embeddedParameter) . "' to string in '{$value}'");
        }

        return preg_replace_callback('/%%|%([^%]+)%/', function ($matches) use ($value, $trace) {
            if ($matches[0] === '%%') {
                return '%%';
            }

            if (isset($trace[$matches[1]])) {
                throw new ContainerException("Parameter '{$matches[1]}' has a cyclical reference between: " . join(', ', array_keys($trace)));
            }

            $trace[$matches[1]] = true;
            $embeddedParameter = $this->get($matches[1]);

            if (is_string($embeddedParameter)) {
                return $this->buildEmbeddedParameters($embeddedParameter, $trace);
            }

            if (is_numeric($embeddedParameter)) {
                return $embeddedParameter;
            }

            throw new ContainerException("Can't convert parameter type '" . gettype($embeddedParameter) . "' to string in '{$value}'");
        } , $value);
    }

    /**
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    protected function buildParameters(mixed &$parameters): void
    {
        if (is_array($parameters)) {
            foreach ($parameters as &$value) {
                $this->buildParameters($value);
            }
            return;
        }

        if (is_string($parameters)) {
            $parameters = $this->buildEmbeddedParameters($parameters);
        }
    }

    protected function unescapeStringValues(mixed &$value): mixed
    {
        if (is_array($value)) {
            foreach ($value as $k => $v) {
                $value[$k] = $this->unescapeStringValues($v);
            }
        }

        if (is_string($value)) {
            $value = str_replace('%%', '%', $value);
        }

        return $value;
    }

    public function reset(): void
    {
        $this->parameters = [];
    }

    /**
     * @throws \InvalidArgumentException
     */
    public function build(): ContainerInterface
    {
        try {
            $this->buildParameters($this->parameters);
            $this->unescapeStringValues($this->parameters);
            return new ParamContainer($this->parameters, $this->idSeparator);
        } catch (ContainerExceptionInterface $e) {
            throw new \InvalidArgumentException('Parameter container could not be built: ' . $e->getMessage());
        }
    }
}