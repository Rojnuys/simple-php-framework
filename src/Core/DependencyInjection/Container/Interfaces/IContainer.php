<?php

namespace App\Core\DependencyInjection\Container\Interfaces;

use Psr\Container\ContainerInterface;

interface IContainer extends ContainerInterface
{
    public function get(string $id): object;
    public function has(string $id): bool;
    public function setService(string $id, object $service): static;
    public function getParameter(string $id): mixed;
    public function hasParameter(string $id): bool;
}