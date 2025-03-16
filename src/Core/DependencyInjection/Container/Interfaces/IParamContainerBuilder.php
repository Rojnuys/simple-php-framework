<?php

namespace App\Core\DependencyInjection\Container\Interfaces;

use Psr\Container\ContainerInterface;

interface IParamContainerBuilder extends ContainerInterface, IBuilder
{
    public function getParameters(): array;
    public function setParameter(string $id, mixed $value): static;
    public function setParameters(array $parameters): static;
    public function build(): ContainerInterface;
}