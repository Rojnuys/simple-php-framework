<?php

namespace App\Core\DependencyInjection\ServiceConfigLoader;

use App\Core\DependencyInjection\ServiceConfigLoader\Interfaces\IPhpFileFinder;
use App\Core\FileSystem\Scanner\Scanner;

class PhpFileFinder implements IPhpFileFinder
{

    public function find(string $dir, array $notPaths): \Traversable
    {
        $scanner = (new Scanner())
            ->files()
            ->in($dir)
            ->name('*.php')
        ;

        foreach ($notPaths as $notPath) {
            $scanner->notPath($notPath);
        }

        return $scanner;
    }
}