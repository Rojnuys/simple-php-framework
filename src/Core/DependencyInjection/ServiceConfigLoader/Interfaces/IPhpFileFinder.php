<?php

namespace App\Core\DependencyInjection\ServiceConfigLoader\Interfaces;

interface IPhpFileFinder
{
    public function find(string $dir, array $notPaths): \Traversable;
}