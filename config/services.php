<?php

use App\Core\ConfigParser\Enums\LoaderConfigType;
use App\Core\DependencyInjection\Enums\ServiceConfigType;
use App\Core\DependencyInjection\Factories\ServiceConfigFactory;
use App\Core\DependencyInjection\ParamContainer\ParamContainer;
use App\Tmp\Tmp34\C;
use App\Tmp\Tmp34\Interfaces\ICanFly;
use App\Tmp\Tmp34\TmpTmp\A;
use App\Tmp\Tmp34\TmpTmp\B;
use Psr\Container\ContainerInterface;

return [
    'App\\' => [
        LoaderConfigType::RESOURCE => '',
        LoaderConfigType::EXCLUDE => ['Core/**'],
        LoaderConfigType::AUTOWIRE => true,
        LoaderConfigType::AUTOCONFIGURE => true,
    ],

    ParamContainer::class => [
        ServiceConfigType::ARGS => [
            'params' => ['hello.com' => 'world'],
            'keySeparator' => '%parameters.separator%',
        ],
    ],

    ServiceConfigFactory::class => [
        ServiceConfigType::TAGS => [
            'factory', 'athkk', 'ghjk', 'top',
        ],
    ],

    'top' => [
        ServiceConfigType::CLASSNAME => ServiceConfigFactory::class,
    ],

    C::class => [
        ServiceConfigType::ARGS => [
            'a' => '@' . B::class,
            'b' => '@' . B::class,
            'c' => '$factory',
        ],
    ],

    A::class => [
        ServiceConfigType::ARGS => [
            'b' => '$top',
        ],
        ServiceConfigType::TAGS => [
            'factory', 'athkk', 'ghjk'
        ],
    ],

    B::class => [
        ServiceConfigType::TAGS => [
            'super', 'puper', 'ultra'
        ],
    ],

    ICanFly::class => [
        ServiceConfigType::CLASSNAME => A::class,
    ],

    ContainerInterface::class => [
        ServiceConfigType::CLASSNAME => ParamContainer::class,
    ],

    'rty' => [],
];