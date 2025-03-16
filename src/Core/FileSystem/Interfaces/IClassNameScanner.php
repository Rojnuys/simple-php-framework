<?php

namespace App\Core\FileSystem\Interfaces;

interface IClassNameScanner
{
    /**
     * @throws \InvalidArgumentException
     */
    public function scan(string $namespace, string $resource, array $excludes = []): \Traversable;
}