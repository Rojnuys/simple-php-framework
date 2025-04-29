<?php

namespace App\Core\DependencyInjection\Container;

use App\Core\DependencyInjection\Container\Interfaces\IContainerDumper;

class ContainerDumper implements IContainerDumper
{

    public function dump(string $dumpFilePath, ContainerBuilder $container): void
    {
        ob_start();
        $cachedContainerClassName = pathinfo($dumpFilePath, PATHINFO_FILENAME);
        require __DIR__ . '/Templates/CachedContainer.php';
        file_put_contents($dumpFilePath, ob_get_clean());
    }
}