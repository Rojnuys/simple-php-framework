<?php

use App\Core\Config\ConfigLoader;
use App\Core\Config\ConfigStructureValidator;
use App\Core\Config\Parsers\ConfigParser;
use App\Core\Config\Parsers\JsonConfigParser;
use App\Core\Config\Parsers\PhpConfigParser;
use App\Core\DependencyInjection\DependencyInjectionModule;
use App\Core\EventDispatcher\EventDispatcherModule;
use App\Core\FileSystem\ClassNameScanner;
use App\Core\Framework\FrameworkModule;
use App\Core\Core\Core;

require_once dirname(__DIR__) . '/vendor/autoload.php';

$core = (new Core())
    ->setProjectDirectoryPath(dirname(__DIR__))
    ->addModule(new EventDispatcherModule())
    ->addModule(new FrameworkModule())
    ->addModule(new DependencyInjectionModule(
        dirname(__DIR__) . '/config/services.php',
        new ConfigParser([
            new PhpConfigParser(),
            new JsonConfigParser(),
        ]),
        new ConfigStructureValidator(),
        new ConfigLoader(new ClassNameScanner()),
    ))
;

$core->run();